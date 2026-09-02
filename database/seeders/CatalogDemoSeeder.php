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
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CatalogDemoSeeder extends Seeder
{
    public function run(): void
    {
        $settings = CatalogSetting::current();

        $this->seedSettings($settings);

        $categories = $this->seedCategories();
        $brands = $this->seedBrands();
        $attributeGroup = $this->seedAttributes();

        $this->seedProducts($categories, $brands, $attributeGroup);
        $this->seedInquiries();
    }

    private function seedSettings(CatalogSetting $settings): void
    {
        $settings->forceFill([
            'title' => 'Era1x Ler Artes',
            'intro_text' => 'Materiais artesanais selecionados com carinho para quem cria com amor e técnica.',
            'hero_enabled' => true,
            'hero_title' => 'Tudo para a sua arte, em um só lugar',
            'hero_subtitle' => 'Explore nosso catálogo de materiais artísticos e artesanais com curadoria especial para cada técnica.',
            'hero_cta_label' => 'Explorar catálogo',
            'hero_cta_url' => '/produtos',
            'hero_alignment' => CatalogSetting::HERO_ALIGNMENT_LEFT,
            'hero_image_path' => null,
            'inquiry_button_text' => 'Consulte',
            'inquiry_confirmation_message' => 'Obrigada pelo seu interesse! Nossa equipe entrará em contato em breve.',
            'per_page' => 24,
            'default_sort' => 'recent',
            'show_unavailable' => false,
        ])->save();
    }

    /**
     * @return Collection<string, CatalogCategory>
     */
    private function seedCategories(): Collection
    {
        $tree = [
            'Pintura e Desenho' => [
                'description' => 'Tintas, pincéis, telas e tudo para expressar sua arte com cores.',
                'children' => [
                    'Tintas e Pigmentos' => 'Tintas acrílicas, aquarela, guache e pigmentos artísticos.',
                    'Pincéis e Espátulas' => 'Pincéis de todos os tipos, espátulas e ferramentas de pintura.',
                    'Suportes de Pintura' => 'Telas canvas, papéis de aquarela, papelão prensado e blocos.',
                ],
            ],
            'Bordado e Costura' => [
                'description' => 'Linhas, agulhas, bastidores e acessórios para bordado e costura criativa.',
                'children' => [
                    'Linhas e Fios' => 'Linhas de algodão, seda, linho e fios para tricô e crochê.',
                    'Bastidores e Acessórios' => 'Bastidores de bambu e madeira, agulhas e ferramentas de bordado.',
                    'Tecidos para Arte' => 'Tecidos de linho, algodão cru e basis para bordado e patchwork.',
                ],
            ],
            'Papel e Scrapbook' => [
                'description' => 'Papéis especiais, carimbos, adesivos e acessórios para scrapbook.',
                'children' => [
                    'Papéis Especiais' => 'Cardstock, kraft, papel de aquarela, vegetal e estampados.',
                    'Carimbos e Tintas' => 'Carimbos de silicone e borracha, tintas de carimbo e almofadas.',
                    'Adesivos e Recortes' => 'Washi tape, adesivos decorativos, die cuts e apliques.',
                ],
            ],
            'Artesanato Geral' => [
                'description' => 'Materiais versáteis para decoupage, macramê, biscuit e muito mais.',
                'children' => [
                    'Bases e Suportes' => 'Peças de MDF, madeira, vidro e cerâmica para artesanato.',
                    'Colas e Vernizes' => 'Cola branca, cola para decoupage, verniz acrílico e selantes.',
                    'Fibras e Cordas' => 'Cordas para macramê, rafias, fios de sisal e barbantes.',
                ],
            ],
        ];

        $result = collect();

        $sort = 1;
        foreach ($tree as $parentName => $data) {
            $parent = CatalogCategory::query()->updateOrCreate(
                ['slug' => Str::slug($parentName)],
                [
                    'parent_id' => null,
                    'name' => $parentName,
                    'description' => $data['description'],
                    'is_active' => true,
                    'sort_order' => $sort++,
                ],
            );
            $result->put($parentName, $parent);

            $childSort = 1;
            foreach ($data['children'] as $childName => $childDesc) {
                $child = CatalogCategory::query()->updateOrCreate(
                    ['slug' => Str::slug($childName)],
                    [
                        'parent_id' => $parent->id,
                        'name' => $childName,
                        'description' => $childDesc,
                        'is_active' => true,
                        'sort_order' => $childSort++,
                    ],
                );
                $result->put($childName, $child);
            }
        }

        return $result;
    }

    /**
     * @return Collection<string, Brand>
     */
    private function seedBrands(): Collection
    {
        $brands = [
            'ArteBranca' => 'Marca especializada em tintas, pincéis e suportes para pintura artística.',
            'Fio & Arte' => 'Referência em linhas, fios e acessórios para bordado, crochê e macramê.',
            'Paperviva' => 'Papéis e acessórios de alta qualidade para scrapbook e papelaria criativa.',
            'TerraCriativa' => 'Materiais versáteis para decoupage, biscuit e artesanato em geral.',
        ];

        return collect($brands)->map(function (string $description, string $name): Brand {
            return Brand::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => $description,
                    'is_active' => true,
                    'sort_order' => 0,
                ],
            );
        });
    }

    private function seedAttributes(): AttributeGroup
    {
        $group = AttributeGroup::query()->updateOrCreate(
            ['slug' => 'especificacoes'],
            ['name' => 'Especificações', 'sort_order' => 1],
        );

        $attributes = ['Cor', 'Material', 'Dimensões', 'Quantidade', 'Volume'];
        $sort = 1;
        foreach ($attributes as $name) {
            Attribute::query()->updateOrCreate(
                ['attribute_group_id' => $group->id, 'slug' => Str::slug($name)],
                ['name' => $name, 'type' => 'text', 'sort_order' => $sort++],
            );
        }

        return $group;
    }

    /**
     * @param  Collection<string, CatalogCategory>  $categories
     * @param  Collection<string, Brand>  $brands
     */
    private function seedProducts(Collection $categories, Collection $brands, AttributeGroup $attributeGroup): void
    {
        $products = [
            [
                'name' => 'Tinta Acrílica Premium 250ml',
                'image' => 'tinta-acrilica-premium-250ml.png',
                'category' => 'Tintas e Pigmentos',
                'brand' => 'ArteBranca',
                'sku' => 'AB-ACRIL-250',
                'short_description' => 'Tinta acrílica de alta cobertura, acabamento fosco e ampla gama de cores para telas e artesanato.',
                'description' => '<p>Tinta acrílica profissional com pigmentação intensa e secagem rápida. Ideal para telas, madeira, papel e cerâmica. Alta cobertura em apenas uma demão, com excelente aderência em diferentes superfícies.</p><p>Disponível em mais de 40 cores sólidas. Base aquosa, atóxica e de fácil limpeza com água.</p>',
                'specifications' => "Volume: 250ml\nAcabamento: Fosco\nBase: Aquosa\nSecagem ao toque: 20 min",
                'features' => "Alta cobertura\nBase aquosa — fácil limpeza\nResistente à água após seca\nCompatível com verniz acrílico",
                'price' => 28.90,
                'price_display' => PriceDisplayMode::Show,
                'availability' => ProductAvailability::Available,
                'status' => ProductStatus::Published,
                'featured' => true,
            ],
            [
                'name' => 'Aquarela Profissional 12 Cores',
                'image' => 'aquarela-profissional-12-cores.png',
                'category' => 'Tintas e Pigmentos',
                'brand' => 'ArteBranca',
                'sku' => 'AB-AQUA-12',
                'short_description' => 'Estojo de aquarela profissional com 12 pastilhas de cores transparentes e luminosas.',
                'description' => '<p>Aquarela em pastilhas de alta qualidade com pigmentos resistentes à luz, ideal para pinturas sobre papel aquarela e mixed media. Cores vibrantes, totalmente reativáveis com água.</p><p>Inclui pincel de fibra sintética nº 8 para começar imediatamente.</p>',
                'specifications' => "Formato: Pastilha\nQuantidade: 12 cores\nPapel recomendado: 200g/m² ou superior\nConteúdo: 12 pastilhas + 1 pincel",
                'features' => "Pigmentos resistentes à luz\nFacilmente reativável com água\nCores translúcidas e luminosas\nInclui pincel",
                'price' => 89.90,
                'price_display' => PriceDisplayMode::Show,
                'availability' => ProductAvailability::Available,
                'status' => ProductStatus::Published,
                'featured' => true,
            ],
            [
                'name' => 'Kit 12 Pincéis Cerdas Sintéticas',
                'image' => 'kit-12-pinceis-cerdas-sinteticas.png',
                'category' => 'Pincéis e Espátulas',
                'brand' => 'ArteBranca',
                'sku' => 'AB-KIT-PNC-12',
                'short_description' => 'Kit com 12 pincéis de cerdas sintéticas em diferentes formatos para pintura acrílica e aquarela.',
                'description' => '<p>Kit completo com pincéis redondos, chatos, em leque e língua de gato em diferentes numerações. Cerdas sintéticas de alta durabilidade que não perdem a forma após lavagem.</p><p>Adequados para tintas acrílicas, aquarela e guache. Cabo em madeira ergonômico.</p>',
                'specifications' => "Quantidade: 12 pincéis\nTipos: Redondo, Chato, Língua de gato, Leque\nNumerações: 2, 4, 6, 8, 10, 12\nCerdas: Fibra sintética",
                'features' => "Cerdas que não soltam\nCabo ergonômico em madeira\nLaváveis em água\nKit versátil para múltiplas técnicas",
                'price' => 54.90,
                'price_display' => PriceDisplayMode::Show,
                'availability' => ProductAvailability::Available,
                'status' => ProductStatus::Published,
                'featured' => false,
            ],
            [
                'name' => 'Tela Canvas 40×50cm',
                'image' => 'tela-canvas-40x50cm.png',
                'category' => 'Suportes de Pintura',
                'brand' => 'ArteBranca',
                'sku' => 'AB-CANVAS-4050',
                'short_description' => 'Tela canvas de algodão 100% em bastidor de pinho, gramatura 280g/m², pré-imprimada.',
                'description' => '<p>Tela profissional de algodão 100% montada em bastidor de pinho resistente. Pré-imprimada com gesso acrílico para uso imediato com acrílica, óleo ou aquarela pesada.</p><p>Bordas de 2cm ideais para exposição sem moldura.</p>',
                'specifications' => "Dimensões: 40 × 50 cm\nGramatura: 280 g/m²\nFibra: Algodão 100%\nBastidor: Pinho\nBorda: 2 cm",
                'features' => "Pré-imprimada — pronta para uso\nBordas pintáveis\nBastidor travado — sem empenamento\nIdeal para exposição sem moldura",
                'price' => 32.90,
                'price_display' => PriceDisplayMode::Show,
                'availability' => ProductAvailability::Available,
                'status' => ProductStatus::Draft,
                'featured' => false,
            ],

            [
                'name' => 'Kit Linhas de Bordado 100% Algodão — 50 cores',
                'image' => 'kit-linhas-de-bordado-100-algodao-50-cores.png',
                'category' => 'Linhas e Fios',
                'brand' => 'Fio & Arte',
                'sku' => 'FA-LINHA-50C',
                'short_description' => 'Kit com 50 meadas de linha de bordado 100% algodão mercerizado em cores vibrantes e tons pastéis.',
                'description' => '<p>Kit abrangente para bordadeiras de todos os níveis. Linhas de 6 fios, 100% algodão mercerizado, com alto brilho e resistência à lavagem. Cada meada tem 8 metros.</p><p>Inclui paleta de cores balanceada com tons terrosos, pastéis e cores vivas para diferentes projetos.</p>',
                'specifications' => "Quantidade: 50 meadas\nFibra: Algodão 100% mercerizado\nComprimento: 8m por meada\nFios: 6 fios divisíveis",
                'features' => "Resistente à lavagem\nAlto brilho natural\nFácil de separar os fios\nPaleta balanceada inclusa",
                'price' => 69.90,
                'price_display' => PriceDisplayMode::Show,
                'availability' => ProductAvailability::Available,
                'status' => ProductStatus::Published,
                'featured' => true,
            ],
            [
                'name' => 'Bastidor de Bambu 25cm',
                'image' => 'bastidor-de-bambu-25cm.png',
                'category' => 'Bastidores e Acessórios',
                'brand' => 'Fio & Arte',
                'sku' => 'FA-BAST-25',
                'short_description' => 'Bastidor redondo de bambu natural 25cm com parafuso ajustável para bordado e ponto de cruz.',
                'description' => '<p>Bastidor de bambu natural com acabamento lixado para não danificar tecidos delicados. Parafuso de madeira com rosca larga para tensão uniforme do tecido.</p><p>Ideal para bordado livre, ponto de cruz, needle felting e exibição de peças.</p>',
                'specifications' => "Diâmetro: 25 cm\nMaterial: Bambu natural\nFechamento: Parafuso de madeira\nEspessura: 10 mm",
                'features' => "Bambu tratado e lixado\nNão mancha o tecido\nTensão uniforme\nPode ser usado como moldura de exibição",
                'price' => 18.90,
                'price_display' => PriceDisplayMode::Show,
                'availability' => ProductAvailability::Available,
                'status' => ProductStatus::Published,
                'featured' => false,
            ],
            [
                'name' => 'Kit Macramê Iniciante Completo',
                'image' => 'kit-macrame-iniciante-completo.png',
                'category' => 'Fibras e Cordas',
                'brand' => 'Fio & Arte',
                'sku' => 'FA-MACRO-KIT',
                'short_description' => 'Kit completo para começar no macramê: 100m de corda, argola, régua de nós e cartilha de técnicas.',
                'description' => '<p>Kit pensado para quem quer dar os primeiros passos no macramê com segurança. Inclui 100 metros de corda de algodão encerado 3mm (natural), argola de madeira 15cm, régua para medir nós e cartilha digital com 10 projetos para iniciantes.</p><p>Perfeito para criar porta-vasos, painéis e chaveiros.</p>',
                'specifications' => "Corda: 100m — algodão encerado 3mm\nArgola: Madeira 15cm\nCartilha: 10 projetos com fotos\nNível: Iniciante",
                'features' => "Tudo em um só kit\nCartilha digital inclusa\nCorda natural sem branqueamento\nIdeal para porta-vasos e painéis",
                'price' => null,
                'price_display' => PriceDisplayMode::OnRequest,
                'availability' => ProductAvailability::Available,
                'status' => ProductStatus::Published,
                'featured' => true,
            ],

            [
                'name' => 'Papel Scrapbook Floral Premium 30×30cm — 30 folhas',
                'image' => 'papel-scrapbook-floral-premium-30x30cm-30-folhas.png',
                'category' => 'Papéis Especiais',
                'brand' => 'Paperviva',
                'sku' => 'PV-SCRAP-FL30',
                'short_description' => 'Bloco de papéis scrapbook com 30 folhas em estampas florais exclusivas, 180g/m².',
                'description' => '<p>Bloco com 30 folhas de papel scrapbook premium em estampas florais aquareladas, boho e cottagecore. Gramatura 180g/m², ideal para álbuns, cartões e layouts.</p><p>Estampas frente e verso, coordenadas por paleta de cores harmoniosa em tons rosados, terracota e verde salvia.</p>',
                'specifications' => "Dimensões: 30 × 30 cm\nGramatura: 180 g/m²\nQuantidade: 30 folhas (15 estampas frente/verso)\nAcabamento: Fosco texturizado",
                'features' => "Estampas frente e verso\nAcid-free — não amarela\nCompatível com impressão\nCoordenado em paleta de cores",
                'price' => null,
                'price_display' => PriceDisplayMode::Hidden,
                'availability' => ProductAvailability::Available,
                'status' => ProductStatus::Published,
                'featured' => false,
            ],
            [
                'name' => 'Kit Carimbos de Silicone — Coleção Flora',
                'image' => 'kit-carimbos-de-silicone-colecao-flora.png',
                'category' => 'Carimbos e Tintas',
                'brand' => 'Paperviva',
                'sku' => 'PV-CARIMBO-FL',
                'short_description' => 'Kit com 12 carimbos de silicone transparente com motivos florais e folhagens para scrapbook e papelaria.',
                'description' => '<p>Coleção Flora com 12 carimbos de silicone transparent (clear stamps), fáceis de posicionar com precisão. Inclui flores, galhos, folhas e elementos de natureza coordenados.</p><p>Compatíveis com tintas de carimbo de base híbrida. Laváveis com água e sabão neutro.</p>',
                'specifications' => "Quantidade: 12 carimbos\nMaterial: Silicone transparente\nMaior peça: 6 × 8 cm\nCompatível: Tintas híbridas e water-based",
                'features' => "Silicone translúcido — posicionamento preciso\nFácil de limpar\nReutilizáveis\nArmazenamento em pasta plástica inclusa",
                'price' => 42.90,
                'price_display' => PriceDisplayMode::Show,
                'availability' => ProductAvailability::Available,
                'status' => ProductStatus::Published,
                'featured' => false,
            ],

            [
                'name' => 'Massa para Biscuit Branca 500g',
                'image' => 'massa-para-biscuit-branca-500g.png',
                'category' => 'Bases e Suportes',
                'brand' => 'TerraCriativa',
                'sku' => 'TC-BISCUIT-500',
                'short_description' => 'Massa para biscuit branca de alta qualidade, secagem ao ar, ideal para modelagem de figuras e lembranças.',
                'description' => '<p>Massa para biscuit de alta qualidade, secagem natural ao ar sem necessidade de forno. Textura macia e maleável, ideal para modelagem de figuras, flores, ímãs e lembranças de festas.</p><p>Após seca, pode ser pintada com tinta acrílica e finalizada com verniz.</p>',
                'specifications' => "Peso: 500g\nSecagem: Natural ao ar (24–48h)\nCor: Branca\nAcabamento: Compatível com tinta acrílica",
                'features' => "Sem necessidade de forno\nSecagem uniforme sem rachaduras\nAceita pintura após seca\nBase não tóxica",
                'price' => null,
                'price_display' => PriceDisplayMode::OnRequest,
                'availability' => ProductAvailability::Available,
                'status' => ProductStatus::Published,
                'featured' => false,
            ],
            [
                'name' => 'Cola para Decoupage 250ml — Fosca',
                'image' => 'cola-para-decoupage-250ml-fosca.png',
                'category' => 'Colas e Vernizes',
                'brand' => 'TerraCriativa',
                'sku' => 'TC-DECP-250F',
                'short_description' => 'Cola e verniz para decoupage 2 em 1, acabamento fosco, compatível com papel, madeira e tecido.',
                'description' => '<p>Cola para decoupage multifuncional que funciona como cola e selante em uma única etapa. Acabamento fosco de alta qualidade que não amarela com o tempo.</p><p>Ideal para aplicação de guardanapos, papel de seda e tecidos sobre vidro, madeira, MDF e cerâmica. Dilua com água para efeitos transparentes.</p>',
                'specifications' => "Volume: 250ml\nAcabamento: Fosco\nBase: Aquosa\nCompatível: Papel, madeira, vidro, cerâmica, tecido",
                'features' => "Cola e verniz em um produto\nNão amarela\nDilúível em água\nFácil aplicação com pincel ou esponja",
                'price' => 24.90,
                'price_display' => PriceDisplayMode::Show,
                'availability' => ProductAvailability::Available,
                'status' => ProductStatus::Published,
                'featured' => false,
            ],
            [
                'name' => 'Aquarela Pérola 24 Cores (fora de linha)',
                'image' => 'aquarela-perola-24-cores-fora-de-linha.png',
                'category' => 'Tintas e Pigmentos',
                'brand' => 'ArteBranca',
                'sku' => 'AB-AQUA-PEARL-24',
                'short_description' => 'Aquarela metálica e perolada em 24 cores — edição descontinuada.',
                'description' => '<p>Edição especial descontinuada da linha Pérola com 24 pastilhas de aquarela com brilho nacarado e efeitos metálicos exclusivos.</p>',
                'specifications' => "Formato: Pastilha\nQuantidade: 24 cores\nEfeito: Nacarado/Metálico\nStatus: Descontinuado",
                'features' => "Efeito nacarado e metálico\nEdição limitada descontinuada",
                'price' => 129.90,
                'price_display' => PriceDisplayMode::Show,
                'availability' => ProductAvailability::Discontinued,
                'status' => ProductStatus::Unavailable,
                'featured' => false,
            ],
        ];

        foreach ($products as $data) {
            $category = $categories->get($data['category']);
            $brand = $brands->get($data['brand']);
            $slug = Str::slug($data['name']);

            $product = Product::query()->firstOrCreate(
                ['slug' => $slug],
                [
                    'catalog_category_id' => $category?->id,
                    'brand_id' => $brand?->id,
                    'name' => $data['name'],
                    'slug' => $slug,
                    'sku' => $data['sku'],
                    'internal_code' => (string) fake()->unique()->numberBetween(1000, 9999),
                    'short_description' => $data['short_description'],
                    'description' => $data['description'],
                    'specifications' => $data['specifications'],
                    'features' => $data['features'],
                    'price' => $data['price'],
                    'promotional_price' => null,
                    'price_display_mode' => $data['price_display'],
                    'currency' => 'BRL',
                    'unit' => 'un',
                    'availability' => $data['availability'],
                    'is_featured' => $data['featured'],
                    'sort_order' => 0,
                    'status' => $data['status'],
                    'published_at' => in_array($data['status'], [ProductStatus::Published, ProductStatus::Unavailable])
                        ? now()->subDays(fake()->numberBetween(1, 30))
                        : null,
                ],
            );

            $this->attachCoverImage($product, $data['image']);
        }
    }

    private function attachCoverImage(Product $product, string $filename): void
    {
        if ($product->hasMedia('cover')) {
            return;
        }

        $path = database_path('seeders/assets/catalog/'.$filename);

        if (! is_file($path)) {
            $this->command?->warn("Imagem de demonstração ausente: {$filename}");

            return;
        }

        $product
            ->addMedia($path)
            ->preservingOriginal()
            ->usingFileName($filename)
            ->usingName($product->name)
            ->toMediaCollection('cover');
    }

    private function seedInquiries(): void
    {
        if (ProductInquiry::query()->count() > 0) {
            return;
        }

        $products = Product::query()
            ->where('status', ProductStatus::Published)
            ->get();

        if ($products->isEmpty()) {
            return;
        }

        $admin = User::query()->where('email', 'admin@example.com')->first()
            ?? User::query()->first();

        $inquiries = [
            [
                'name' => 'Juliana Martins',
                'email' => 'juliana@email.com',
                'phone' => '+55 11 98765-4321',
                'city' => 'São Paulo',
                'state' => 'SP',
                'status' => InquiryStatus::New,
                'assignee_id' => null,
                'first_answered_at' => null,
                'completed_at' => null,
            ],
            [
                'name' => 'Camila Rocha',
                'email' => 'camila@email.com',
                'phone' => '+55 41 99876-5432',
                'city' => 'Curitiba',
                'state' => 'PR',
                'status' => InquiryStatus::InService,
                'assignee_id' => $admin?->id,
                'first_answered_at' => now()->subHours(3),
                'completed_at' => null,
            ],
            [
                'name' => 'Fernanda Lima',
                'email' => 'fernanda@email.com',
                'phone' => '+55 21 97654-3210',
                'city' => 'Rio de Janeiro',
                'state' => 'RJ',
                'status' => InquiryStatus::Answered,
                'assignee_id' => $admin?->id,
                'first_answered_at' => now()->subDays(2),
                'completed_at' => null,
            ],
            [
                'name' => 'Beatriz Santos',
                'email' => 'beatriz@email.com',
                'phone' => '+55 31 98888-7777',
                'city' => 'Belo Horizonte',
                'state' => 'MG',
                'status' => InquiryStatus::Completed,
                'assignee_id' => $admin?->id,
                'first_answered_at' => now()->subDays(5),
                'completed_at' => now()->subDays(3),
            ],
            [
                'name' => 'Robô Spam Bot',
                'email' => 'spam@bot.example.com',
                'phone' => null,
                'city' => null,
                'state' => null,
                'status' => InquiryStatus::Spam,
                'assignee_id' => null,
                'first_answered_at' => null,
                'completed_at' => null,
            ],
        ];

        foreach ($inquiries as $data) {
            $product = $products->random();

            ProductInquiry::query()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'city' => $data['city'],
                'state' => $data['state'],
                'subject' => 'Interesse em '.$product->name,
                'message' => 'Olá! Gostaria de mais informações sobre '.$product->name.'. Aguardo retorno.',
                'origin' => 'web',
                'source_url' => url('/produtos/'.$product->slug),
                'utm' => [],
                'ip' => fake()->ipv4(),
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'status' => $data['status'],
                'assignee_id' => $data['assignee_id'],
                'first_viewed_at' => $data['status'] !== InquiryStatus::New ? now()->subDays(3) : null,
                'first_answered_at' => $data['first_answered_at'],
                'completed_at' => $data['completed_at'],
            ]);
        }
    }
}
