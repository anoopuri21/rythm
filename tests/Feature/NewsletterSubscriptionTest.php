<?php

namespace Tests\Feature;

use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_subscribe_to_the_newsletter(): void
    {
        $response = $this->postJson(route('newsletter.store'), [
            'email' => 'MUSICIAN@example.com ',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('subscribed', true);

        $this->assertDatabaseHas(NewsletterSubscriber::class, [
            'email' => 'musician@example.com',
        ]);
    }

    public function test_subscribing_twice_is_idempotent(): void
    {
        NewsletterSubscriber::create([
            'email' => 'musician@example.com',
            'subscribed_at' => now(),
        ]);

        $this->postJson(route('newsletter.store'), [
            'email' => 'musician@example.com',
        ])->assertOk();

        $this->assertDatabaseCount(NewsletterSubscriber::class, 1);
    }

    public function test_email_must_be_valid(): void
    {
        $this->postJson(route('newsletter.store'), [
            'email' => 'not-an-email',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_honeypot_field_is_rejected(): void
    {
        $this->postJson(route('newsletter.store'), [
            'email' => 'bot@example.com',
            'company' => 'Spam Incorporated',
        ])->assertUnprocessable()->assertJsonValidationErrors('company');
    }
}
