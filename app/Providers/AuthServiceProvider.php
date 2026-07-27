<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Author;
use App\Models\Brand;
use App\Models\CatalogCategory;
use App\Models\NewsletterSubscriber;
use App\Models\Permission;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\Product;
use App\Models\ProductInquiry;
use App\Models\Role;
use App\Models\Testimonial;
use App\Models\User;
use App\Policies\AuthorPolicy;
use App\Policies\BrandPolicy;
use App\Policies\CatalogCategoryPolicy;
use App\Policies\NewsletterSubscriberPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\PostCommentPolicy;
use App\Policies\PostPolicy;
use App\Policies\ProductInquiryPolicy;
use App\Policies\ProductPolicy;
use App\Policies\RolePolicy;
use App\Policies\TestimonialPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Author::class => AuthorPolicy::class,
        Brand::class => BrandPolicy::class,
        CatalogCategory::class => CatalogCategoryPolicy::class,
        NewsletterSubscriber::class => NewsletterSubscriberPolicy::class,
        Permission::class => PermissionPolicy::class,
        Post::class => PostPolicy::class,
        PostComment::class => PostCommentPolicy::class,
        Product::class => ProductPolicy::class,
        ProductInquiry::class => ProductInquiryPolicy::class,
        Role::class => RolePolicy::class,
        Testimonial::class => TestimonialPolicy::class,
        User::class => UserPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(static function (User $user, string $ability): ?bool {
            return $user->hasRole('Super Admin') ? true : null;
        });
    }
}
