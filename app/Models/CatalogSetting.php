<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title',
    'intro_text',
    'banner',
    'per_page',
    'default_sort',
    'show_unavailable',
    'inquiry_button_text',
    'inquiry_recipients',
    'inquiry_confirmation_message',
    'commercial_phone',
    'commercial_whatsapp',
    'captcha_enabled',
    'seo_title',
    'seo_description',
])]
class CatalogSetting extends Model
{
    use HasFactory;

    protected $table = 'catalog_settings';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'banner' => 'array',
            'per_page' => 'integer',
            'show_unavailable' => 'boolean',
            'captcha_enabled' => 'boolean',
        ];
    }

    public static function defaults(): array
    {
        return [
            'title' => 'Catálogo',
            'intro_text' => 'Conheça nossos produtos e solicite uma consulta comercial.',
            'per_page' => 12,
            'default_sort' => 'recent',
            'show_unavailable' => true,
            'inquiry_button_text' => 'Consulte',
            'inquiry_confirmation_message' => 'Sua mensagem foi registrada. Em breve entraremos em contato.',
            'captcha_enabled' => false,
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], static::defaults());
    }
}
