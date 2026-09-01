<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Services\ContactService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class ContactController extends Controller
{
    public function index(): View
    {
        return view('contact.index');
    }

    public function store(StoreContactMessageRequest $request, ContactService $messages): RedirectResponse
    {
        $messages->store($request->validated());

        return back()->with('contact_success', 'Thank you. Your message has been recorded for the Rhythm Exports team.');
    }
}
