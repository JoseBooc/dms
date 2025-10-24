<?php

namespace App\Console\Commands;

use App\Services\PenaltyService;
use Illuminate\Console\Command;

class ProcessPenalties extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'penalties:process 
                            {--dry-run : Preview penalties without applying them}
                            {--tenant= : Process penalties for specific tenant ID}
                            {--show-details : Show detailed output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process penalties for overdue bills';

    /**
     * The penalty service instance.
     */
    protected PenaltyService $penaltyService;

    /**
     * Create a new command instance.
     */
    public function __construct(PenaltyService $penaltyService)
    {
        parent::__construct();
        $this->penaltyService = $penaltyService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting penalty processing...');

        if ($this->option('dry-run')) {
            return $this->handleDryRun();
        }

        $results = $this->penaltyService->processOverdueBills();

        $this->displayResults($results);

        if (count($results['errors']) > 0) {
            $this->error('Some errors occurred during processing:');
            foreach ($results['errors'] as $error) {
                $this->error("  - {$error}");
            }
            return 1;
        }

        $this->info('Penalty processing completed successfully.');
        return 0;
    }

    /**
     * Handle dry run mode
     */
    protected function handleDryRun(): int
    {
        $this->info('DRY RUN MODE - No penalties will be applied');
        $this->line('');

        $eligibleBills = $this->penaltyService->getEligibleBills();

        if ($eligibleBills->isEmpty()) {
            $this->info('No bills are eligible for penalty calculation.');
            return 0;
        }

        $this->info("Found {$eligibleBills->count()} bills eligible for penalty calculation:");
        $this->line('');

        $headers = ['Bill ID', 'Tenant', 'Room', 'Amount', 'Due Date', 'Days Overdue', 'Current Penalty', 'New Penalty', 'Increase'];
        $rows = [];

        $totalIncrease = 0;

        foreach ($eligibleBills as $bill) {
            $preview = $this->penaltyService->previewPenalty($bill);
            
            if ($preview['eligible']) {
                $rows[] = [
                    $bill->id,
                    $bill->tenant->name ?? 'N/A',
                    $bill->room->room_number ?? 'N/A',
                    '₱' . number_format($bill->total_amount, 2),
                    $bill->due_date->format('Y-m-d'),
                    $preview['overdue_days'],
                    '₱' . number_format($preview['current_penalty'], 2),
                    '₱' . number_format($preview['new_penalty'], 2),
                    '₱' . number_format($preview['increase'], 2)
                ];

                $totalIncrease += $preview['increase'];
            }
        }

        $this->table($headers, $rows);
        $this->line('');
        $this->info("Total penalty increase would be: ₱" . number_format($totalIncrease, 2));

        return 0;
    }

    /**
     * Display processing results
     */
    protected function displayResults(array $results): void
    {
        $this->line('');
        $this->info('Processing Results:');
        $this->line("  Bills processed: {$results['processed']}");
        $this->line("  Penalties applied: {$results['penalties_applied']}");
        $this->line("  Total penalty amount: ₱" . number_format($results['total_penalty_amount'], 2));

        if ($this->option('show-details')) {
            $stats = $this->penaltyService->getPenaltyStatistics();
            $this->line('');
            $this->info('Current Statistics:');
            $this->line("  Total bills: {$stats['total_bills']}");
            $this->line("  Overdue bills: {$stats['overdue_bills']} ({$stats['overdue_percentage']}%)");
            $this->line("  Bills with penalties: {$stats['bills_with_penalties']} ({$stats['penalty_rate']}% of overdue)");
            $this->line("  Waived penalties: {$stats['waived_penalties']}");
            $this->line("  Active penalty amount: ₱" . number_format($stats['active_penalty_amount'], 2));
        }
    }
}
