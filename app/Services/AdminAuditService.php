<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class AdminAuditService
{
    private const REDACTED = '[REDACTED]';

    /** @var list<string> */
    private const SENSITIVE_FRAGMENTS = ['password', 'token', 'secret', 'signature', 'authorization', 'cookie', 'otp', 'recovery', 'card', 'cvv'];

    /** @param array<string, mixed> $before
     * @param  array<string, mixed>  $after
     */
    public function record(User $actor, string $action, ?Model $subject = null, array $before = [], array $after = [], ?string $reason = null): AdminAuditLog
    {
        $request = app()->runningInConsole() ? null : request();
        $ip = $request?->ip();

        return AdminAuditLog::create([
            'actor_id' => $actor->id,
            'action' => Str::limit($action, 100, ''),
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'reason' => $reason === null ? null : Str::limit(trim($reason), 500, ''),
            'before_values' => $this->redact($before),
            'after_values' => $this->redact($after),
            'ip_hash' => $ip === null ? null : hash_hmac('sha256', $ip, (string) config('app.key')),
            'user_agent' => $request === null ? null : Str::limit((string) $request->userAgent(), 500, ''),
            'request_id' => $request?->headers->get('X-Request-ID') ?: (string) Str::uuid(),
            'created_at' => now(),
        ]);
    }

    /** @param array<string, mixed> $values
     * @return array<string, mixed>
     */
    public function redact(array $values): array
    {
        $redacted = [];
        foreach ($values as $key => $value) {
            $normalizedKey = strtolower((string) $key);
            if ($this->isSensitive($normalizedKey)) {
                $redacted[$key] = self::REDACTED;

                continue;
            }
            $redacted[$key] = is_array($value) ? $this->redact($value) : $value;
        }

        return $redacted;
    }

    private function isSensitive(string $key): bool
    {
        foreach (self::SENSITIVE_FRAGMENTS as $fragment) {
            if (str_contains($key, $fragment)) {
                return true;
            }
        }

        return false;
    }
}
