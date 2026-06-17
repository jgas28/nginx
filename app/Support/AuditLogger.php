<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    private const HARD_EXCLUDED_KEYS = ['password', 'remember_token'];

    /**
     * Captures a plain Eloquent create/update/delete. Called from the wildcard
     * eloquent.* event listeners registered in AppServiceProvider.
     */
    public static function record(string $verb, ?Model $model): void
    {
        if (!$model || !Auth::check()) {
            return;
        }

        // Mandatory recursion guard: without this, AuditLog::create() below would
        // fire its own eloquent.created event and log itself forever.
        if ($model instanceof AuditLog) {
            return;
        }

        [$oldValues, $newValues] = match ($verb) {
            'created' => [null, self::stripSensitive($model, $model->getAttributes())],
            'deleted' => [self::stripSensitive($model, $model->getAttributes()), null],
            default => self::diffForUpdate($model),
        };

        if ($verb === 'updated' && $newValues === null) {
            // No real change after stripping timestamps -- a no-op save, don't log it.
            return;
        }

        $tag = app(AuditContext::class)->consume();

        AuditLog::create([
            'user_id' => Auth::id(),
            'actor_name' => self::actorName(Auth::user()),
            'event' => $tag->action ?? $verb,
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'description' => $tag->description ?? (class_basename($model) . ' #' . $model->getKey() . ' ' . $verb),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Captures login / logout / failed-login -- not Eloquent writes, so they need
     * their own entry point distinct from record() above.
     */
    public static function recordAuth(string $event, ?User $user, string $description): void
    {
        AuditLog::create([
            'user_id' => $user?->id,
            'actor_name' => self::actorName($user),
            'event' => $event,
            'description' => $description,
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    private static function diffForUpdate(Model $model): array
    {
        $changes = $model->getChanges();
        unset($changes['updated_at'], $changes['created_at']);

        if (empty($changes)) {
            return [null, null];
        }

        $original = array_intersect_key($model->getOriginal(), $changes);

        return [self::stripSensitive($model, $original), self::stripSensitive($model, $changes)];
    }

    private static function stripSensitive(Model $model, array $values): array
    {
        foreach (array_merge($model->getHidden(), self::HARD_EXCLUDED_KEYS) as $key) {
            unset($values[$key]);
        }

        return $values;
    }

    private static function actorName(?User $user): ?string
    {
        if (!$user) {
            return null;
        }

        return trim("{$user->fname} {$user->lname}") . " ({$user->employee_code})";
    }
}
