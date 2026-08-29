<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\NotificationPreference;
use App\Services\NotificationPreferenceService;
use App\Services\SeoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

final class NotificationController extends Controller
{
    public function __construct(private readonly SeoService $seo) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $this->seo->apply([
            'meta_title' => 'Notifications — Rhythm Exports',
            'meta_description' => 'View transactional account and order updates.',
            'robots' => 'noindex, follow',
        ]);

        return view('account.notifications', [
            'notifications' => $user->notifications()->latest()->paginate(12),
            'unreadCount' => $user->unreadNotifications()->count(),
            'preferences' => $user->notificationPreferences()->get()->keyBy('category'),
            'categories' => NotificationPreference::OPTIONAL_CATEGORIES,
        ]);
    }

    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $this->owned($request, $notification)->markAsRead();

        return back()->with('notification_status', 'Notification marked as read.');
    }

    public function markUnread(Request $request, string $notification): RedirectResponse
    {
        $this->owned($request, $notification)->markAsUnread();

        return back()->with('notification_status', 'Notification marked as unread.');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('notification_status', 'All notifications marked as read.');
    }

    public function updatePreferences(
        Request $request,
        NotificationPreferenceService $preferences,
    ): RedirectResponse {
        $validated = $request->validate([
            'preferences' => ['required', 'array'],
            'preferences.*.email_enabled' => ['nullable', 'boolean'],
            'preferences.*.database_enabled' => ['nullable', 'boolean'],
        ]);

        foreach (NotificationPreference::OPTIONAL_CATEGORIES as $category) {
            $selected = $validated['preferences'][$category] ?? [];
            $preferences->set(
                $request->user(),
                $category,
                (bool) ($selected['email_enabled'] ?? false),
                (bool) ($selected['database_enabled'] ?? false),
            );
        }

        return back()->with('preference_status', 'Notification preferences updated.');
    }

    private function owned(Request $request, string $id): DatabaseNotification
    {
        return $request->user()->notifications()->whereKey($id)->firstOrFail();
    }
}
