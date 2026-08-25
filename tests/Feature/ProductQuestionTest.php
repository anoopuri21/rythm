<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\ProductQuestionSection;
use App\Models\Product;
use App\Models\ProductQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class ProductQuestionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->user = User::where('email', 'test@example.com')->firstOrFail();
        $this->product = Product::firstOrFail();
        $this->actingAs($this->user);
    }

    public function test_phase_five_moderation_schema_exists(): void
    {
        $this->assertTrue(Schema::hasColumns('reviews', [
            'status', 'merchant_reply', 'moderated_by', 'moderated_at', 'replied_by', 'replied_at',
        ]));
        $this->assertTrue(Schema::hasColumns('product_questions', [
            'product_id', 'user_id', 'question', 'status', 'answer', 'moderated_by', 'moderated_at', 'answered_by', 'answered_at',
        ]));
    }

    public function test_product_question_section_renders(): void
    {
        $this->get(route('product.show', $this->product))
            ->assertOk()
            ->assertSee('Questions &amp; answers', escape: false)
            ->assertSee('Ask about this product');
    }

    public function test_authenticated_customer_can_submit_pending_question(): void
    {
        Livewire::test(ProductQuestionSection::class, ['product' => $this->product])
            ->set('question', 'Does this instrument include a suitable carrying case?')
            ->call('submit')
            ->assertSet('submitted', true)
            ->assertSet('question', '');

        $this->assertDatabaseHas('product_questions', [
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
            'status' => ProductQuestion::STATUS_PENDING,
        ]);
    }

    public function test_guest_question_redirects_to_login(): void
    {
        auth()->logout();

        Livewire::test(ProductQuestionSection::class, ['product' => $this->product])
            ->set('question', 'Does this instrument include a suitable carrying case?')
            ->call('submit')
            ->assertRedirect('/login');

        $this->assertDatabaseCount('product_questions', 0);
    }

    public function test_question_length_is_validated(): void
    {
        Livewire::test(ProductQuestionSection::class, ['product' => $this->product])
            ->set('question', 'Short')
            ->call('submit')
            ->assertHasErrors(['question' => 'min']);

        $this->assertDatabaseCount('product_questions', 0);
    }

    public function test_only_approved_answered_questions_are_public_and_content_is_escaped(): void
    {
        ProductQuestion::create([
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
            'question' => '<script>alert(1)</script> Is a case included?',
            'answer' => '<b>No case is listed as included.</b>',
            'status' => ProductQuestion::STATUS_APPROVED,
        ]);
        ProductQuestion::create([
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
            'question' => 'Approved but not answered',
            'status' => ProductQuestion::STATUS_APPROVED,
        ]);
        ProductQuestion::create([
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
            'question' => 'Pending with an answer',
            'answer' => 'This must remain private.',
            'status' => ProductQuestion::STATUS_PENDING,
        ]);

        Livewire::test(ProductQuestionSection::class, ['product' => $this->product])
            ->assertSee('<script>alert(1)</script> Is a case included?')
            ->assertSee('<b>No case is listed as included.</b>')
            ->assertDontSeeHtml('<script>alert(1)</script>')
            ->assertDontSeeHtml('<b>No case is listed as included.</b>')
            ->assertDontSee('Approved but not answered')
            ->assertDontSee('Pending with an answer');
    }

    public function test_question_submission_is_rate_limited_per_customer_and_product(): void
    {
        $component = Livewire::test(ProductQuestionSection::class, ['product' => $this->product]);

        foreach (range(1, 3) as $attempt) {
            $component
                ->set('submitted', false)
                ->set('question', "Question number {$attempt} about this instrument?")
                ->call('submit')
                ->assertSet('submitted', true);
        }

        $component
            ->set('submitted', false)
            ->set('question', 'Question number four about this instrument?')
            ->call('submit')
            ->assertSet('error', 'Too many questions were submitted. Please try again later.');

        $this->assertDatabaseCount('product_questions', 3);
    }

    public function test_admin_can_moderate_and_answer_question_with_audit_fields(): void
    {
        $question = ProductQuestion::create([
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
            'question' => 'Is this compatible with a standard stand?',
            'status' => ProductQuestion::STATUS_PENDING,
        ]);
        $admin = User::where('email', 'admin@rythme.test')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/product-questions')
            ->assertOk()
            ->assertSee('Is this compatible');

        $question->update([
            'status' => ProductQuestion::STATUS_APPROVED,
            'answer' => 'Please compare the stand dimensions with the listed product dimensions.',
        ]);

        $question->refresh();
        $this->assertSame($admin->id, $question->moderated_by);
        $this->assertSame($admin->id, $question->answered_by);
        $this->assertNotNull($question->moderated_at);
        $this->assertNotNull($question->answered_at);
    }

    public function test_customer_cannot_access_question_moderation(): void
    {
        $this->get('/admin/product-questions')->assertForbidden();
    }
}
