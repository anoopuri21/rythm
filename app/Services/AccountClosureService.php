<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Address;
use App\Models\BackInStockSubscription;
use App\Models\Cart;
use App\Models\User;
use App\Models\Wishlist;
use App\Notifications\AccountClosureLinkNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

final class AccountClosureService
{
    public const LINK_MINUTES = 60;

    public function sendLink(User $user): void
    {
        $this->guardCustomer($user);

        $plain = Str::random(64);
        $user->forceFill([
            'account_closure_token' => hash('sha256', $plain),
            'account_closure_expires_at' => now()->addMinutes(self::LINK_MINUTES),
        ])->save();

        try {
            $user->notify(new AccountClosureLinkNotification($this->signedUrl(
                'account.closure.show',
                $user,
                $plain,
            )));
        } catch (\Throwable $exception) {
            $this->clearToken($user);
            throw $exception;
        }
    }

    public function linkIsValid(User $user, string $hash, ?string $token): bool
    {
        if (! $this->isOpenCustomer($user) || ! is_string($token) || $token === '') {
            return false;
        }

        if (! hash_equals(sha1($user->email), $hash)) {
            return false;
        }

        $stored = (string) $user->account_closure_token;
        $expiresAt = $user->account_closure_expires_at;

        return $stored !== ''
            && hash_equals($stored, hash('sha256', $token))
            && $expiresAt !== null
            && $expiresAt->isFuture();
    }

    public function signedActionUrl(string $route, User $user, string $token): string
    {
        return $this->signedUrl($route, $user, $token);
    }

    public function deactivate(User $user): void
    {
        $this->guardCustomer($user);

        DB::transaction(function () use ($user): void {
            $user->forceFill([
                'deactivated_at' => now(),
                'remember_token' => null,
                'account_closure_token' => null,
                'account_closure_expires_at' => null,
            ])->save();

            $this->forgetSessions($user);
        });
    }

    public function remove(User $user): void
    {
        $this->guardCustomer($user);

        DB::transaction(function () use ($user): void {
            Address::query()->where('user_id', $user->id)->delete();
            Cart::query()->where('user_id', $user->id)->delete();
            Wishlist::query()->where('user_id', $user->id)->delete();
            BackInStockSubscription::query()->where('user_id', $user->id)->delete();

            $user->forceFill([
                'name' => 'Deleted customer',
                'email' => 'deleted+'.$user->id.'@users.invalid',
                'password' => Str::random(64),
                'email_verified_at' => null,
                'removed_at' => now(),
                'remember_token' => null,
                'account_closure_token' => null,
                'account_closure_expires_at' => null,
            ])->save();

            $this->forgetSessions($user);
        });
    }

    public function isOpenCustomer(User $user): bool
    {
        return $user->role === User::ROLE_CUSTOMER && $user->removed_at === null;
    }

    private function guardCustomer(User $user): void
    {
        if (! $this->isOpenCustomer($user)) {
            abort(403);
        }
    }

    private function signedUrl(string $route, User $user, string $token): string
    {
        return URL::temporarySignedRoute($route, now()->addMinutes(self::LINK_MINUTES), [
            'user' => $user->id,
            'hash' => sha1($user->email),
            'token' => $token,
        ]);
    }

    private function clearToken(User $user): void
    {
        $user->forceFill([
            'account_closure_token' => null,
            'account_closure_expires_at' => null,
        ])->save();
    }

    private function forgetSessions(User $user): void
    {
        DB::table('sessions')->where('user_id', $user->id)->delete();
    }
}
