<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\InquiryStatus;
use App\Enums\PriceDisplayMode;
use App\Enums\ProductAvailability;
use App\Enums\ProductStatus;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\Brand;
use App\Models\CatalogCategory;
use App\Models\CatalogSetting;
use App\Models\Product;
use App\Models\ProductInquiry;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogDemoSeeder extends Seeder
{
    public function run(): void
    {
        CatalogSetting::current();

        $categories = collect([
            ['name' => 'Móveis', 'description' => 'Móveis para casa e escritório.'],
            ['name' => 'Iluminação', 'description' => 'Luminárias e lâmpadas.'],
            ['name' => 'Decoração', 'description' => 'Itens decorativos.'],
            ['name' => 'Escritório', 'description' => 'Produtos para ambientes corporativos.'],
        ])->mapWithKeys(function (array $data): array {
            $category = CatalogCategory::query()->firstOrCreate(
                ['slug' => Str::slug($data['name'])],
                [...$data, 'is_active' => true, 'sort_order' => 0],
            );

            return [$data['name'] => $category];
        });

        $subcategories = [
            'Móveis' => ['Sofás', 'Mesas'],
            'Iluminação' => ['Pendentes', 'Luminárias de Mesa'],
            'Decoração' => ['Vasos', 'Quadros'],
            'Escritório' => ['Cadeiras', 'Estações'],
        ];

        foreach ($subcategories as $parentName => $children) {
            $parent = $categories->get($parentName);
            if (! $parent) {
                continue;
            }

            foreach ($children as $childName) {
                CatalogCategory::query()->firstOrCreate(
                    ['slug' => Str::slug($parentName.' '.$childName)],
                    [
                        'parent_id' => $parent->id,
                        'name' => $childName,
                        'description' => $childName.' da categoria '.$parentName.'.',
                        'is_active' => true,
                        'sort_order' => 0,
                    ],
                );
            }
        }

        $brands = collect(['Lumera', 'Casa Verde', 'Oficce+', 'Nordwood'])
            ->mapWithKeys(fn (string $name): array => [
                $name => Brand::query()->firstOrCreate(
                    ['slug' => Str::slug($name)],
                    ['name' => $name, 'is_active' => true, 'sort_order' => 0],
                ),
            ]);

        $attributesGroup = AttributeGroup::query()->firstOrCreate(
            ['slug' => 'especificacoes'],
            ['name' => 'Especificações', 'sort_order' => 0],
        );

        foreach (['Cor', 'Material', 'Dimensões', 'Peso'] as $name) {
            Attribute::query()->firstOrCreate(
                ['attribute_group_id' => $attributesGroup->id, 'slug' => Str::slug($name)],
                ['name' => $name, 'type' => 'text', 'sort_order' => 0],
            );
        }

        $products = [
            [
                'name' => 'Sofá Oslo 3 Lugares',
                'category' => 'Móveis',
                'subcategory' => 'Sofás',
                'brand' => 'Casa Verde',
                'status' => ProductStatus::Published,
                'price_display' => PriceDisplayMode::Show,
                'featured' => true,
            ],
            [
                'name' => 'Mesa de Jantar Nordic',
                'category' => 'Móveis',
                'subcategory' => 'Mesas',
                'brand' => 'Nordwood',
                'status' => ProductStatus::Published,
                'price_display' => PriceDisplayMode::Show,
                'featured' => true,
            ],
            [
                'name' => 'Pendente Sphere LED',
                'category' => 'Iluminação',
                'subcategory' => 'Pendentes',
                'brand' => 'Lumera',
                'status' => ProductStatus::Published,
                'price_display' => PriceDisplayMode::OnRequest,
                'featured' => false,
            ],
            [
                'name' => 'Luminária de Mesa Boreal',
                'category' => 'Iluminação',
                'subcategory' => 'Luminárias de Mesa',
                'brand' => 'Lumera',
                'status' => ProductStatus::Published,
                'price_display' => PriceDisplayMode::Show,
                'featured' => false,
            ],
            [
                'name' => 'Vaso Decorativo Juta',
                'category' => 'Decoração',
                'subcategory' => 'Vasos',
                'brand' => 'Casa Verde',
                'status' => ProductStatus::Published,
                'price_display' => PriceDisplayMode::Hidden,
                'featured' => false,
            ],
            [
                'name' => 'Quadro Abstrato Linha Fina',
                'category' => 'Decoração',
                'subcategory' => 'Quadros',
                'brand' => 'Casa Verde',
                'status' => ProductStatus::Published,
                'price_display' => PriceDisplayMode::Show,
                'featured' => false,
            ],
            [
                'name' => 'Cadeira Ergonômica Pro',
                'category' => 'Escritório',
                'subcategory' => 'Cadeiras',
                'brand' => 'Oficce+',
                'status' => ProductStatus::Published,
                'price_display' => PriceDisplayMode::Show,
                'featured' => true,
            ],
            [
                'name' => 'Estação Compacta 120cm',
                'category' => 'Escritório',
                'subcategory' => 'Estações',
                'brand' => 'Oficce+',
                'status' => ProductStatus::Published,
                'price_display' => PriceDisplayMode::OnRequest,
                'featured' => false,
            ],
            [
                'name' => 'Rascunho: Mesa Bistro (em preparação)',
                'category' => 'Móveis',
                'subcategory' => 'Mesas',
                'brand' => 'Nordwood',
                'status' => ProductStatus::Draft,
                'price_display' => PriceDisplayMode::Show,
                'featured' => false,
            ],
            [
                'name' => 'Indisponível: Luminária Solar (em falta)',
                'category' => 'Iluminação',
                'subcategory' => 'Luminárias de Mesa',
                'brand' => 'Lumera',
                'status' => ProductStatus::Unavailable,
                'price_display' => PriceDisplayMode::Show,
                'featured' => false,
            ],
        ];

        $admin = User::query()->where('email', 'admin@example.com')->first()
            ?? User::query()->first();

        foreach ($products as $data) {
            $category = $categories->get($data['category']);
            $sub = $category?->children()->firstWhere('slug', Str::slug($data['category'].' '.$data['subcategory']));
            $brand = $brands->get($data['brand']);
            $slug = Str::slug($data['name']);

            Product::query()->firstOrCreate(
                ['slug' => $slug],
                [
                    'catalog_category_id' => $sub?->id ?? $category?->id,
                    'brand_id' => $brand?->id,
                    'name' => $data['name'],
                    'slug' => $slug,
                    'sku' => strtoupper(Str::slug(Str::words($data['name'], 2, ''))),
                    'internal_code' => (string) fake()->unique()->numberBetween(1000, 9999),
                    'short_description' => $data['name'].' da linha '.$data['brand'].'.',
                    'description' => '<p>'.fake()->paragraph().'</p>',
                    'specifications' => 'Material: madeira certificada.',
                    'features' => 'Garantia de 12 meses.',
                    'price' => $data['price_display'] === PriceDisplayMode::OnRequest ? null : fake()->randomFloat(2, 200, 4000),
                    'promotional_price' => null,
                    'price_display_mode' => $data['price_display'],
                    'currency' => 'BRL',
                    'unit' => 'un',
                    'availability' => ProductAvailability::Available,
                    'is_featured' => $data['featured'],
                    'status' => $data['status'],
                    'published_at' => $data['status'] === ProductStatus::Published ? now()->subDay() : null,
                ],
            );
        }

        $publishedProducts = Product::query()->where('status', ProductStatus::Published)->get();

        if ($publishedProducts->isNotEmpty() && ProductInquiry::query()->count() === 0) {
            $demoInquiries = [
                [
                    'name' => 'Marina Souza',
                    'email' => 'marina@example.com',
                    'status' => InquiryStatus::New,
                ],
                [
                    'name' => 'Paulo Reis',
                    'email' => 'paulo@example.com',
                    'status' => InquiryStatus::InService,
                    'assignee_id' => $admin?->id,
                    'first_answered_at' => now()->subHour(),
                ],
                [
                    'name' => 'Carla Mendes',
                    'email' => 'carla@example.com',
                    'status' => InquiryStatus::Completed,
                    'assignee_id' => $admin?->id,
                    'first_answered_at' => now()->subDays(2),
                    'completed_at' => now()->subDay(),
                ],
                [
                    'name' => 'Robô Suspeito',
                    'email' => 'spam@bot.example',
                    'status' => InquiryStatus::Spam,
                ],
            ];

            foreach ($demoInquiries as $data) {
                $product = $publishedProducts->random();

                ProductInquiry::query()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => '+55 11 9'.str_pad((string) random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                    'subject' => 'Interesse em '.$product->name,
                    'message' => 'Olá, gostaria de mais informações sobre '.$product->name.'.',
                    'origin' => 'web',
                    'source_url' => 'https://example.com/produtos/'.$product->slug,
                    'status' => $data['status'],
                    'assignee_id' => $data['assignee_id'] ?? null,
                    'first_answered_at' => $data['first_answered_at'] ?? null,
                    'completed_at' => $data['completed_at'] ?? null,
                ]);
            }
        }
    }
}
