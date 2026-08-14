<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Address;
use App\Models\Order;
use App\Services\AddressService;
use App\Services\SeoService;
use App\Services\WishlistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

final class AccountController extends Controller
{
    public function __construct(private readonly SeoService $seo) {}

    public function index(WishlistService $wishlists, AddressService $addresses): View
    {
        $user = auth()->user();

        $this->seo->apply([
            'meta_title' => 'My Account — Rhythm Exports',
            'meta_description' => 'Manage your profile, addresses, orders and wishlist at Rhythm Exports.',
            'robots' => 'noindex, follow',
        ]);

        return view('account.index', [
            'orders' => Order::query()
                ->where('user_id', $user->id)
                ->withCount('items')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get(),
            'wishlistCount' => $wishlists->countFor($user->id),
            'addresses' => $addresses->forUser($user->id),
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $user = auth()->user();

        $user->update($request->validated());

        return back()->with('profile_success', 'Profile updated.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = auth()->user();

        // Invalidate other sessions FIRST — this verifies the current
        // password against the still-current hash in the database.
        Auth::logoutOtherDevices($request->validated('current_password'));

        $user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return back()->with('password_success', 'Password updated.');
    }

    public function storeAddress(StoreAddressRequest $request, AddressService $addresses): RedirectResponse
    {
        $addresses->store((int) auth()->id(), $request->validated());

        return back()->with('address_success', 'Address added.');
    }

    public function destroyAddress(Address $address, AddressService $addresses): RedirectResponse
    {
        $addresses->delete($address, (int) auth()->id());

        return back()->with('address_success', 'Address removed.');
    }
}
