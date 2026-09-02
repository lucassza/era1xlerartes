<?php

declare(strict_types=1);

namespace Tests\Feature\Mail;

use App\Mail\NewCommentNotification;
use App\Mail\NewsletterConfirmationMail;
use App\Mail\NewTestimonialNotification;
use App\Mail\ProductInquiryAdminNotification;
use App\Mail\ProductInquiryVisitorConfirmation;
use App\Models\NewsletterSubscriber;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\Product;
use App\Models\ProductInquiry;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailableRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_comment_notification_renders_html(): void
    {
        $recipient = User::factory()->admin()->create();
        $post = Post::factory()->create();
        $comment = PostComment::factory()->create(['post_id' => $post->getKey()]);

        $html = (new NewCommentNotification($comment, $recipient))->render();

        $this->assertStringContainsString((string) $recipient->name, $html);
        $this->assertStringContainsString((string) $comment->getAttribute('name'), $html);
    }

    public function test_new_testimonial_notification_renders_html(): void
    {
        $recipient = User::factory()->admin()->create();
        $user = User::factory()->siteUser()->create();
        $testimonial = Testimonial::factory()->forUser($user)->pending()->create();

        $html = (new NewTestimonialNotification($testimonial, $recipient))->render();

        $this->assertStringContainsString((string) $recipient->name, $html);
        $this->assertStringContainsString((string) $testimonial->getAttribute('name'), $html);
    }

    public function test_newsletter_confirmation_renders_html(): void
    {
        $subscriber = NewsletterSubscriber::factory()->create();

        $html = (new NewsletterConfirmationMail(
            $subscriber,
            'https://era1x.test/newsletter/confirmar/token',
            'https://era1x.test/newsletter/cancelar/token',
        ))->render();

        $this->assertStringContainsString('https://era1x.test/newsletter/confirmar/token', $html);
        $this->assertStringContainsString('https://era1x.test/newsletter/cancelar/token', $html);
    }

    public function test_product_inquiry_admin_notification_renders_html(): void
    {
        $inquiry = ProductInquiry::factory()->create([
            'product_id' => Product::factory()->create()->getKey(),
        ]);

        $html = (new ProductInquiryAdminNotification($inquiry))->render();

        $this->assertStringContainsString((string) $inquiry->getAttribute('product_name'), $html);
    }

    public function test_product_inquiry_visitor_confirmation_renders_html(): void
    {
        $inquiry = ProductInquiry::factory()->create([
            'product_id' => Product::factory()->create()->getKey(),
        ]);

        $html = (new ProductInquiryVisitorConfirmation($inquiry))->render();

        $this->assertStringContainsString((string) $inquiry->getAttribute('name'), $html);
    }

    public function test_shared_email_layout_is_resolvable_and_uses_brand_logo(): void
    {
        $this->assertFileExists(resource_path('views/components/emails/layout.blade.php'));

        $subscriber = NewsletterSubscriber::factory()->create();
        $html = (new NewsletterConfirmationMail($subscriber, 'https://era1x.test/c', 'https://era1x.test/u'))->render();

        $this->assertStringContainsString('images/brand/logo-fundo-branco.png', $html);
        $this->assertStringNotContainsString('x-emails.layout', $html);
    }
}
