<?php

namespace App\Services;

use App\Models\UtilityReading;
use App\Models\UtilityType;
use App\Models\Room;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UtilityService
{
    // Maximum consumption limits (per month)
    const MAX_ELECTRICITY_KWH = 500;
    const MAX_WATER_M3 = 40;

    /**
     * Calculate consumption from readings
     *
     * @param float $currentReading
     * @param float $previousReading
     * @return float
     */
    public function calculateConsumption(float $currentReading, float $previousReading): float
    {
        return max(0, $currentReading - $previousReading);
    }

    /**
     * Calculate utility amount
     *
     * @param float $consumption
     * @param float $rate
     * @return float
     */
    public function calculateAmount(float $consumption, float $rate): float
    {
        return round($consumption * $rate, 2);
    }

    /**
     * Validate consumption against limits
     *
     * @param UtilityType $utilityType
     * @param float $consumption
     * @return array ['valid' => bool, 'limit' => float, 'exceeded_by' => float]
     */
    public function validateConsumption(UtilityType $utilityType, float $consumption): array
    {
        $limit = $this->getConsumptionLimit($utilityType);
        
        if ($limit === null) {
            return ['valid' => true, 'limit' => null, 'exceeded_by' => 0];
        }

        $exceeded = $consumption > $limit;
        $exceededBy = $exceeded ? ($consumption - $limit) : 0;

        return [
            'valid' => !$exceeded,
            'limit' => $limit,
            'exceeded_by' => $exceededBy,
            'percentage_over' => $limit > 0 ? round(($exceededBy / $limit) * 100, 1) : 0,
        ];
    }

    /**
     * Get consumption limit for a utility type
     *
     * @param UtilityType $utilityType
     * @return float|null
     */
    private function getConsumptionLimit(UtilityType $utilityType): ?float
    {
        $utilityName = strtolower($utilityType->name);

        if ($utilityName === 'electricity') {
            return self::MAX_ELECTRICITY_KWH;
        }

        if ($utilityName === 'water') {
            return self::MAX_WATER_M3;
        }

        return null; // No limit for other utilities
    }

    /**
     * Create a utility reading with validation and auto-calculation
     *
     * @param array $readingData
     * @return UtilityReading
     * @throws \Exception
     */
    public function createReading(array $readingData): UtilityReading
    {
        return DB::transaction(function () use ($readingData) {
            $utilityType = UtilityType::findOrFail($readingData['utility_type_id']);

            // Get previous reading if not provided
            if (!isset($readingData['previous_reading'])) {
                $previousReading = $this->getLastReading(
                    $readingData['room_id'],
                    $readingData['utility_type_id']
                );
                $readingData['previous_reading'] = $previousReading ? $previousReading->current_reading : 0;
            }

            // Calculate consumption
            $consumption = $this->calculateConsumption(
                $readingData['current_reading'],
                $readingData['previous_reading']
            );

            // Validate consumption
            $validation = $this->validateConsumption($utilityType, $consumption);
            
            if (!$validation['valid'] && empty($readingData['override_reason'])) {
                throw new \Exception(
                    "Consumption ({$consumption} {$utilityType->unit}) exceeds maximum limit ({$validation['limit']} {$utilityType->unit}). " .
                    "Please provide an override reason or verify the reading."
                );
            }

            // Calculate amount
            $rate = $readingData['rate'] ?? $this->getCurrentRate($utilityType);
            $amount = $this->calculateAmount($consumption, $rate);

            // Create reading
            $reading = UtilityReading::create([
                'room_id' => $readingData['room_id'],
                'utility_type_id' => $readingData['utility_type_id'],
                'reading_date' => $readingData['reading_date'] ?? now(),
                'previous_reading' => $readingData['previous_reading'],
                'current_reading' => $readingData['current_reading'],
                'consumption' => $consumption,
                'rate' => $rate,
                'amount' => $amount,
                'status' => $readingData['status'] ?? 'pending',
                'override_reason' => $readingData['override_reason'] ?? null,
                'override_by' => !empty($readingData['override_reason']) ? auth()->id() : null,
            ]);

            Log::info('Utility reading created', [
                'reading_id' => $reading->id,
                'room_id' => $reading->room_id,
                'utility_type' => $utilityType->name,
                'consumption' => $consumption,
                'amount' => $amount,
                'exceeded_limit' => !$validation['valid'],
            ]);

            return $reading;
        });
    }

    /**
     * Get the last utility reading for a room and utility type
     *
     * @param int $roomId
     * @param int $utilityTypeId
     * @return UtilityReading|null
     */
    public function getLastReading(int $roomId, int $utilityTypeId): ?UtilityReading
    {
        return UtilityReading::where('room_id', $roomId)
            ->where('utility_type_id', $utilityTypeId)
            ->orderBy('reading_date', 'desc')
            ->first();
    }

    /**
     * Get current rate for a utility type
     *
     * @param UtilityType $utilityType
     * @return float
     */
    private function getCurrentRate(UtilityType $utilityType): float
    {
        $currentRate = $utilityType->rates()
            ->where('effective_date', '<=', now())
            ->orderBy('effective_date', 'desc')
            ->first();

        return $currentRate ? $currentRate->rate : 0;
    }

    /**
     * Verify a utility reading
     *
     * @param UtilityReading $reading
     * @return UtilityReading
     */
    public function verifyReading(UtilityReading $reading): UtilityReading
    {
        return DB::transaction(function () use ($reading) {
            $reading->status = 'verified';
            $reading->save();

            Log::info('Utility reading verified', [
                'reading_id' => $reading->id,
                'room_id' => $reading->room_id,
            ]);

            return $reading;
        });
    }

    /**
     * Mark reading as billed (prevents further edits)
     *
     * @param UtilityReading $reading
     * @param int $billId
     * @return UtilityReading
     */
    public function markAsBilled(UtilityReading $reading, int $billId): UtilityReading
    {
        return DB::transaction(function () use ($reading, $billId) {
            $reading->status = 'billed';
            $reading->bill_id = $billId;
            $reading->save();

            Log::info('Utility reading marked as billed', [
                'reading_id' => $reading->id,
                'bill_id' => $billId,
            ]);

            return $reading;
        });
    }

    /**
     * Get utility consumption summary for a room
     *
     * @param int $roomId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return array
     */
    public function getConsumptionSummary(int $roomId, $startDate, $endDate): array
    {
        $readings = UtilityReading::where('room_id', $roomId)
            ->whereBetween('reading_date', [$startDate, $endDate])
            ->with('utilityType')
            ->get();

        $summary = [];

        foreach ($readings as $reading) {
            $utilityName = $reading->utilityType->name;
            
            if (!isset($summary[$utilityName])) {
                $summary[$utilityName] = [
                    'total_consumption' => 0,
                    'total_amount' => 0,
                    'readings_count' => 0,
                    'unit' => $reading->utilityType->unit,
                ];
            }

            $summary[$utilityName]['total_consumption'] += $reading->consumption;
            $summary[$utilityName]['total_amount'] += $reading->amount;
            $summary[$utilityName]['readings_count']++;
        }

        return $summary;
    }
}
