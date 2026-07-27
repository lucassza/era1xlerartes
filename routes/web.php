<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\SiteAuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PublicCatalogCategoryController;
use App\Http\Controllers\PublicCommentController;
use App\Http\Controllers\PublicPostController;
use App\Http\Controllers\PublicProductController;
use App\Http\Controllers\PublicSearchController;
use App\Http\Controllers\PublicTaxonomyController;
use App\Http\Controllers\PublicTestimonialController;
use App\Http\Controllers\SeoController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', CatalogController::class)->name('home');
Route::get('/produtos', [PublicProductController::class, 'index'])->name('products.index');
Route::get('/produtos/{slug}', [PublicProductController::class, 'show'])
    ->name('products.show');
Route::get('/categorias/{slug}', [PublicCatalogCategoryController::class, 'show'])
    ->name('catalog-categories.show');
Route::get('/blog', HomeController::class)->name('blog.index');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
Route::get('/rss.xml', [SeoController::class, 'rss'])->name('seo.rss');
Route::get('/search', [PublicSearchController::class, 'index'])->name('search.index');
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::get('/newsletter/confirm/{subscriber}', [NewsletterController::class, 'confirm'])->name('newsletter.confirm');
Route::get('/newsletter/unsubscribe/{subscriber}', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

Route::get('/posts/{slug}', [PublicPostController::class, 'show'])->name('posts.show');
Route::get('/categories/{slug}', [PublicTaxonomyController::class, 'category'])->name('categories.show');
Route::get('/tags/{slug}', [PublicTaxonomyController::class, 'tag'])->name('tags.show');
Route::get('/authors/{slug}', [PublicTaxonomyController::class, 'author'])->name('authors.show');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->name('login.store');

    Route::get('/entrar', [SiteAuthController::class, 'createLogin'])->name('site.login');
    Route::post('/entrar', [SiteAuthController::class, 'storeLogin'])->name('site.login.store');
    Route::get('/cadastro', [SiteAuthController::class, 'createRegister'])->name('site.register');
    Route::post('/cadastro', [SiteAuthController::class, 'storeRegister'])->name('site.register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/perfil', [ProfileController::class, 'edit'])->name('site.profile.edit');
    Route::put('/perfil', [ProfileController::class, 'update'])->name('site.profile.update');
    Route::post('/perfil/avatar', [ProfileController::class, 'updateAvatar'])
        ->middleware('throttle:10,1')
        ->name('site.profile.avatar.update');
    Route::delete('/perfil/avatar', [ProfileController::class, 'destroyAvatar'])->name('site.profile.avatar.destroy');
    Route::put('/perfil/senha', [ProfileController::class, 'updatePassword'])
        ->middleware('throttle:6,1')
        ->name('site.profile.password.update');

    Route::view('/dashboard', 'admin.dashboard')->name('admin.dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
    Route::post('/sair', [SiteAuthController::class, 'destroyLogout'])->name('site.logout');

    Route::view('/email/verify', 'auth.verify-email')->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('home')->with('site_auth_status', 'E-mail confirmado! Agora você já pode comentar e enviar depoimentos.');
    })->middleware('signed')->name('verification.verify');
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1')->name('verification.send');

    Route::post('/depoimentos', [PublicTestimonialController::class, 'store'])
        ->middleware(['engagement:testimonials', 'verified'])
        ->name('testimonials.store');

    Route::post('/posts/{post}/comentarios', [PublicCommentController::class, 'store'])
        ->middleware(['engagement:comments', 'verified'])
        ->name('posts.comments.store');
});

Route::get('/depoimentos', [PublicTestimonialController::class, 'index'])
    ->middleware('engagement:testimonials')
    ->name('testimonials.index');
