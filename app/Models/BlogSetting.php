<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'site_name',
    'site_description',
    'default_meta_title',
    'default_meta_description',
    'contact_email',
    'facebook_url',
    'instagram_url',
    'x_url',
    'newsletter_enabled',
    'comments_enabled',
    'testimonials_enabled',
])]
class BlogSetting extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'newsletter_enabled' => 'boolean',
            'comments_enabled' => 'boolean',
            'testimonials_enabled' => 'boolean',
        ];
    }

    public static function defaults(): array
    {
        return [
            'site_name' => config('app.name', 'Era1x Ler Artes'),
            'site_description' => 'Blog moderno em Laravel.',
            'default_meta_title' => config('app.name', 'Era1x Ler Artes'),
            'default_meta_description' => 'Blog moderno em Laravel.',
            'newsletter_enabled' => true,
            'comments_enabled' => true,
            'testimonials_enabled' => true,
        ];
    }
}
