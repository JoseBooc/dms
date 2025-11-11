<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Log a create action
     *
     * @param Model $model
     * @param string|null $description
     * @return AuditLog
     */
    public function logCreate(Model $model, ?string $description = null): AuditLog
    {
        return $this->log($model, 'create', null, $model->getAttributes(), $description);
    }

    /**
     * Log an update action
     *
     * @param Model $model
     * @param array $oldValues
     * @param string|null $description
     * @return AuditLog
     */
    public function logUpdate(Model $model, array $oldValues, ?string $description = null): AuditLog
    {
        // Only log changed fields
        $changes = $model->getChanges();
        $oldValuesFiltered = array_intersect_key($oldValues, $changes);

        return $this->log($model, 'update', $oldValuesFiltered, $changes, $description);
    }

    /**
     * Log a delete action
     *
     * @param Model $model
     * @param string|null $description
     * @return AuditLog
     */
    public function logDelete(Model $model, ?string $description = null): AuditLog
    {
        return $this->log($model, 'delete', $model->getAttributes(), null, $description);
    }

    /**
     * Log a restore action
     *
     * @param Model $model
     * @param string|null $description
     * @return AuditLog
     */
    public function logRestore(Model $model, ?string $description = null): AuditLog
    {
        return $this->log($model, 'restore', null, $model->getAttributes(), $description);
    }

    /**
     * Log a custom action
     *
     * @param Model $model
     * @param string $action
     * @param array|null $oldValues
     * @param array|null $newValues
     * @param string|null $description
     * @return AuditLog
     */
    public function log(
        Model $model,
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => auth()->id(),
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'action' => $action,
            'old_values' => $this->sanitizeValues($oldValues),
            'new_values' => $this->sanitizeValues($newValues),
            'description' => $description ?? $this->generateDescription($model, $action),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Get audit logs for a model
     *
     * @param Model $model
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLogsForModel(Model $model)
    {
        return AuditLog::where('model_type', get_class($model))
            ->where('model_id', $model->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get recent audit logs
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentLogs(int $limit = 50)
    {
        return AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get audit logs for a specific user
     *
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLogsForUser(int $userId, int $limit = 100)
    {
        return AuditLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Generate a human-readable description of the action
     *
     * @param Model $model
     * @param string $action
     * @return string
     */
    private function generateDescription(Model $model, string $action): string
    {
        $modelName = class_basename($model);
        $modelId = $model->id ?? 'new';

        switch ($action) {
            case 'create':
                return "{$modelName} #{$modelId} was created";
            case 'update':
                return "{$modelName} #{$modelId} was updated";
            case 'delete':
                return "{$modelName} #{$modelId} was deleted";
            case 'restore':
                return "{$modelName} #{$modelId} was restored";
            default:
                return "{$modelName} #{$modelId}: {$action}";
        }
    }

    /**
     * Sanitize values before storing (remove sensitive data)
     *
     * @param array|null $values
     * @return array|null
     */
    private function sanitizeValues(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        $sensitiveFields = ['password', 'password_confirmation', 'remember_token'];

        foreach ($sensitiveFields as $field) {
            if (isset($values[$field])) {
                $values[$field] = '***REDACTED***';
            }
        }

        return $values;
    }
}
