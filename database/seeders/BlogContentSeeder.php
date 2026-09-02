<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PostStatus;
use App\Enums\UserType;
use App\Models\Author;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BlogContentSeeder extends Seeder
{
    public function run(): void
    {
        $categories = $this->seedCategories();
        $tags = $this->seedTags();
        $authors = $this->seedAuthors();

        $this->seedPosts($categories, $tags, $authors);
        $this->attachCoverImages();
    }

    /**
     * @return Collection<int, Category>
     */
    private function seedCategories(): Collection
    {
        $items = [
            ['name' => 'Técnicas Artesanais', 'description' => 'Passo a passo de bordado, pintura, macramê, crochê e muito mais.'],
            ['name' => 'Materiais e Ferramentas', 'description' => 'Guias completos sobre tintas, pincéis, papéis, fios e tudo que você precisa para criar.'],
            ['name' => 'Inspiração e Criatividade', 'description' => 'Tendências, paletas de cor, organização do espaço criativo e ideias para seus projetos.'],
            ['name' => 'Histórias de Arte', 'description' => 'Relatos de artesãs, tradições culturais e a arte que conecta gerações.'],
            ['name' => 'Eventos e Workshops', 'description' => 'Cobertura de feiras, encontros, aulas ao vivo e novidades da Era1x.'],
        ];

        return collect($items)->map(function (array $item): Category {
            return Category::query()->updateOrCreate(
                ['slug' => Str::slug($item['name'])],
                [
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'is_active' => true,
                ],
            );
        });
    }

    /**
     * @return Collection<int, Tag>
     */
    private function seedTags(): Collection
    {
        $items = [
            'Artesanato',
            'Pintura',
            'Bordado',
            'Crochê',
            'Macramê',
            'Decoupage',
            'Scrapbook',
            'Aquarela',
            'Costura',
            'Feltro',
            'Biscuit',
            'Patchwork',
            'DIY',
            'Sustentável',
            'Workshop',
        ];

        return collect($items)->map(function (string $name): Tag {
            return Tag::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => null,
                    'is_active' => true,
                ],
            );
        });
    }

    /**
     * @return Collection<int, Author>
     */
    private function seedAuthors(): Collection
    {
        $profiles = [
            ['name' => 'Super Admin', 'email' => 'admin@example.com', 'role' => 'Super Admin', 'type' => UserType::Admin],
            ['name' => 'Editora Era1x', 'email' => 'editor@example.com', 'role' => 'Editor', 'type' => UserType::Admin],
            ['name' => 'Angel Era1x', 'email' => 'angelmic@gmail.com', 'role' => 'Super Admin', 'type' => UserType::Admin, 'password' => 'Senha123*'],
            ['name' => 'Equipe Era1x', 'email' => 'equipe@example.com', 'role' => 'Author', 'type' => UserType::Admin],
        ];

        return collect($profiles)->map(function (array $profile): Author {
            $user = User::query()->firstOrCreate(
                ['email' => $profile['email']],
                [
                    'name' => $profile['name'],
                    'password' => $profile['password'] ?? 'password',
                    'email_verified_at' => now(),
                    'type' => $profile['type']->value,
                ],
            );

            if ($user->type->value !== $profile['type']->value) {
                $user->type = $profile['type']->value;
                $user->save();
            }

            if (! $user->hasRole($profile['role'])) {
                $user->assignRole($profile['role']);
            }

            return Author::query()->firstOrCreate(
                ['user_id' => $user->getKey()],
                [
                    'name' => $profile['name'],
                    'slug' => Str::slug($profile['name']),
                    'bio' => 'Autora e artesã apaixonada pela Era1x Ler Artes.',
                    'is_active' => true,
                ],
            );
        });
    }

    /**
     * @param  Collection<int, Category>  $categories
     * @param  Collection<int, Tag>  $tags
     * @param  Collection<int, Author>  $authors
     */
    private function seedPosts(Collection $categories, Collection $tags, Collection $authors): void
    {
        if (Post::query()->whereNull('source_platform')->count() >= 50) {
            return;
        }

        Post::query()->whereNull('source_platform')->delete();

        $postsByCategorySlug = [
            'tecnicas-artesanais' => [
                ['title' => 'Como pintar com aquarela: guia completo para iniciantes', 'excerpt' => 'Descubra os primeiros passos para criar peças encantadoras com aquarela, desde a escolha do papel até as técnicas básicas de molhado sobre molhado.'],
                ['title' => 'Bordado básico: os pontos essenciais para começar hoje', 'excerpt' => 'Aprenda os pontos mais utilizados no bordado manual e transforme qualquer tecido em uma obra de arte com linha e agulha.'],
                ['title' => 'Macramê moderno: nós e técnicas para peças contemporâneas', 'excerpt' => 'Do nó quadrado ao espiral, descubra as amarrações que criam pendentes, porta-vasos e painéis cheios de personalidade.'],
                ['title' => 'Decoupage em vidro: transformando potes em objetos únicos', 'excerpt' => 'Com guardanapos estampados, cola e verniz, qualquer vidro pode virar uma peça decorativa digna de exposição.'],
                ['title' => 'Crochê para iniciantes: como dar seus primeiros pontos com confiança', 'excerpt' => 'Nada de nós impossíveis — aprenda a pegar o fio e o gancho e dê seus primeiros pontos com tranquilidade e método.'],
                ['title' => 'Feltro com agulha de feltrar: introdução ao needle felting', 'excerpt' => 'Transforme fibras de lã em esculturas tridimensionais delicadas usando apenas agulhas especiais e muita paciência criativa.'],
                ['title' => 'Scrapbook analógico: como montar seu primeiro álbum de memórias', 'excerpt' => 'Papéis, carimbos, adesivos e muito afeto — aprenda a documentar histórias com camadas, texturas e identidade visual única.'],
                ['title' => 'Patchwork: unindo retalhos com técnica, cor e muita arte', 'excerpt' => 'Do corte à costura, um guia prático para criar colchas, almofadas e bolsas com retalhos de tecido coloridos.'],
                ['title' => 'Biscuit: modelando figurinhas delicadas passo a passo', 'excerpt' => 'Aprenda a preparar a massa, modelar formas simples e finalizar com tinta acrílica para criar lembranças e decorações únicas.'],
                ['title' => 'Pintura em tela: como preparar o canvas e aplicar a primeira camada', 'excerpt' => 'Da tela crua ao primeiro traço — tudo sobre gestão, imprimação e as técnicas iniciais para pintar com acrílica em tela.'],
            ],
            'materiais-e-ferramentas' => [
                ['title' => 'Guia completo de tintas acrílicas para artesanato', 'excerpt' => 'Diferenças entre marcas, viscosidades e acabamentos — tudo que você precisa saber antes de comprar sua próxima tinta.'],
                ['title' => 'Pincéis de artesanato: como escolher, usar e conservar', 'excerpt' => 'Cerdas naturais ou sintéticas, redondos ou chatos — entenda qual pincel serve para cada técnica e como prolongar sua vida útil.'],
                ['title' => 'As melhores colas para cada tipo de projeto artesanal', 'excerpt' => 'Cola branca, silicone, hot glue, decoupage — um comparativo honesto de qual cola usar em papel, tecido, madeira e EVA.'],
                ['title' => 'Papéis especiais para scrapbook: gramatura, textura e acabamentos', 'excerpt' => 'Cardstock, kraft, aquarela, transparente — um panorama dos papéis que todo scrapbooker precisa conhecer e testar.'],
                ['title' => 'Linhas de bordado: diferenças entre algodão, seda e poliéster', 'excerpt' => 'Escolher a linha certa faz toda a diferença no resultado final — descubra as características de cada fibra e quando usá-las.'],
                ['title' => 'Tecidos para patchwork: tipos, tramas e como cortar com precisão', 'excerpt' => 'Tricoline, musseline, linho — aprenda as diferenças e descubra como a régua e o cortador rotativo transformam seu trabalho.'],
                ['title' => 'Ferramentas essenciais para trabalhar com biscuit e cerâmica fria', 'excerpt' => 'Palitos, estecas, rolinhos e moldes — conheça os acessórios que fazem a diferença na modelagem de peças em biscuit.'],
                ['title' => 'Fios para macramê: espessuras, fibras e como calcular a quantidade', 'excerpt' => 'Algodão encerado, corda de sisal, fio náutico — um guia prático para escolher o fio ideal para cada projeto de macramê.'],
                ['title' => 'Paleta de tintas: como organizar, misturar e limpar corretamente', 'excerpt' => 'Evite o desperdício e potencialize suas misturas com boas práticas de organização de tinta acrílica e aquarela.'],
                ['title' => 'Tesouras artesanais: tipos e usos para cada técnica criativa', 'excerpt' => 'Tesoura de ponta fina, recorte decorativo, zig-zag e fabric — descubra o par certo para cada material e finalidade.'],
            ],
            'inspiracao-e-criatividade' => [
                ['title' => 'Tendências do artesanato para 2026: o que está em alta', 'excerpt' => 'Do macramê monocromático ao bordado maximalist, conheça as estéticas que dominam o universo criativo este ano.'],
                ['title' => 'Como encontrar inspiração nos elementos da natureza', 'excerpt' => 'Flores, folhas, texturas de pedra e a paleta das estações — aprenda a traduzir o mundo natural em peças artesanais únicas.'],
                ['title' => 'Arte sustentável: criando com materiais reciclados e conscientes', 'excerpt' => 'Revistas velhas, galhos secos, frascos descartados — ideias criativas para transformar o que seria lixo em arte.'],
                ['title' => 'Cottagecore e artesanato: a tendência que chegou para ficar', 'excerpt' => 'Flores secas, bordados florais, rendas e tons terrosos — entenda como o cottagecore influenciou o artesanato contemporâneo.'],
                ['title' => 'Paletas de cores para artesanato: criando harmonia nos projetos', 'excerpt' => 'Analogias, complementares, triádicas — aprenda a montar combinações de cores coesas que elevam qualquer trabalho manual.'],
                ['title' => 'Como organizar seu espaço criativo em casa com pouco espaço', 'excerpt' => 'Prateleiras, caixas organizadoras, pegboards — soluções criativas e acessíveis para ter um ateliê funcional em qualquer cômodo.'],
                ['title' => 'Bullet journal com elementos artesanais: junção perfeita', 'excerpt' => 'Combine planejamento e criatividade com carimbos, washi tape, aquarela e lettering no seu caderno de organização.'],
                ['title' => 'Fotografando seu artesanato: dicas práticas para redes sociais', 'excerpt' => 'Luz natural, fundos limpos e enquadramento — aprenda a fotografar suas peças artesanais de forma que faça jus à beleza do trabalho.'],
                ['title' => 'Vendendo artesanato online: onde e como começar com confiança', 'excerpt' => 'Elo7, Instagram, WhatsApp Business — um guia para quem quer transformar a paixão pelo artesanato em renda extra.'],
                ['title' => 'Arte terapêutica: como o artesanato melhora a saúde mental', 'excerpt' => 'Bordado, crochê, pintura — entenda por que as atividades manuais são reconhecidas como aliadas do bem-estar emocional.'],
            ],
            'historias-de-arte' => [
                ['title' => 'A história do bordado no Brasil: tradição e modernidade', 'excerpt' => 'Do ponto de cruz dos conventos coloniais ao bordado contemporâneo — como essa arte resistiu e se reinventou ao longo dos séculos.'],
                ['title' => 'Rendeiras do Nordeste: guardiãs de um patrimônio cultural', 'excerpt' => 'As artesãs do Ceará, da Paraíba e do Piauí que mantêm viva a arte da renda de bilro, reconhecida pela UNESCO.'],
                ['title' => 'Artesãs brasileiras que estão conquistando o mundo com suas criações', 'excerpt' => 'Da roça ao Instagram internacional — conheça mulheres que usam técnicas tradicionais e alcançam mercados globais.'],
                ['title' => 'Macramê: a volta de uma técnica que atravessou milênios', 'excerpt' => 'Dos marinheiros árabes às paredes dos lofts modernos — a incrível trajetória histórica do macramê ao redor do planeta.'],
                ['title' => 'Da lã ao tapete: a arte ancestral da tecelagem manual no Brasil', 'excerpt' => 'Tear manual, tingimento natural, geometrias indígenas — a tecelagem como expressão cultural de povos e regiões.'],
                ['title' => 'Como uma tesoura mudou minha vida: relato de uma artesã', 'excerpt' => 'Uma história pessoal sobre como começar a costurar em um momento difícil virou um ateliê, uma renda e uma identidade.'],
                ['title' => 'A herança da minha avó em ponto de cruz: bordando memórias', 'excerpt' => 'Um relato emocionante sobre reaprender o ponto de cruz com a avó e o que essa arte têxtil carrega de história familiar.'],
                ['title' => 'Bordado como linguagem: a arte têxtil contemporânea e seus manifestos', 'excerpt' => 'Artistas que usam o bordado como crítica social, expressão política e resistência cultural no século XXI.'],
                ['title' => 'Minha jornada no scrapbook: do primeiro álbum até o ateliê', 'excerpt' => 'Dez anos depois da primeira folha colada, uma apaixonada conta como o scrapbook transformou sua forma de guardar histórias.'],
                ['title' => 'Arte e artesanato: as fronteiras que o fazer manual sempre desafiou', 'excerpt' => 'O que separa arte de artesanato? Uma reflexão honesta sobre valor, técnica, autoria e o reconhecimento do trabalho manual.'],
            ],
            'eventos-e-workshops' => [
                ['title' => 'Resumo da Feira de Artesanato de Curitiba 2026', 'excerpt' => 'Cobertura completa do maior encontro de artesãs do sul do Brasil: expositores, tendências e os produtos mais admirados.'],
                ['title' => 'Workshop de aquarela em São Paulo: o que esperar do evento', 'excerpt' => 'Antecipamos a programação, os instrutores confirmados e os materiais inclusos no workshop mais aguardado do ano.'],
                ['title' => 'Encontro Era1x em Belo Horizonte: inscrições abertas', 'excerpt' => 'Venha para um dia inteiro de técnicas ao vivo, troca de materiais e muito artesanato coletivo em BH.'],
                ['title' => 'Festival de Artesanato Sustentável do Rio de Janeiro 2026', 'excerpt' => 'Um festival que celebra o fazer consciente: do tingimento natural ao macramê com fibras reutilizadas.'],
                ['title' => 'Lançamento da coleção Era1x Ler Artes — Primavera 2026', 'excerpt' => 'Estivemos no lançamento da nova coleção de materiais artesanais e trouxemos tudo sobre os kits exclusivos da temporada.'],
                ['title' => 'Concurso de bordado Era1x 2026: conheça os finalistas', 'excerpt' => 'As criações mais impressionantes que concorrem ao prêmio máximo do nosso concurso anual de bordado criativo.'],
                ['title' => 'Aula ao vivo de macramê: participe online de forma gratuita', 'excerpt' => 'Toda semana uma nova técnica ao vivo no Instagram da Era1x — confira o calendário e separe seus fios.'],
                ['title' => 'Swap Era1x: troque materiais artesanais com outras criadoras', 'excerpt' => 'Saiba como participar do nosso evento virtual de troca de papéis, linhas e acessórios com artesãs de todo o Brasil.'],
                ['title' => 'Encontro Era1x em Porto Alegre: programação completa confirmada', 'excerpt' => 'Oficinas, feira de materiais e tarde de networking — confira tudo que espera as participantes do encontro gaúcho.'],
                ['title' => 'Exposição Ler Artes em parceria com museu: entrada gratuita', 'excerpt' => 'Uma exposição especial de peças artesanais contemporâneas abertas ao público por duas semanas em São Paulo.'],
            ],
        ];

        $index = 1;
        foreach ($postsByCategorySlug as $slug => $posts) {
            $category = $categories->firstWhere('slug', $slug);
            if (! $category) {
                continue;
            }

            foreach ($posts as $postData) {
                $status = match (true) {
                    $index <= 35 => PostStatus::Published,
                    $index <= 42 => PostStatus::Draft,
                    $index <= 46 => PostStatus::Scheduled,
                    default => PostStatus::Archived,
                };

                $post = Post::factory()->state([
                    'author_id' => $authors->random()->getKey(),
                    'category_id' => $category->getKey(),
                    'title' => $postData['title'],
                    'slug' => Str::slug($postData['title']).'-'.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                    'excerpt' => $postData['excerpt'],
                    'content' => '<p>'.$postData['excerpt'].'</p><p>Conteúdo completo em breve.</p>',
                    'status' => $status,
                    'published_at' => match ($status) {
                        PostStatus::Published => now()->subDays(max(1, 60 - $index)),
                        PostStatus::Scheduled => now()->addDays($index),
                        default => null,
                    },
                    'archived_at' => $status === PostStatus::Archived ? now()->subDays($index) : null,
                    'views_count' => $status === PostStatus::Published ? ($index * 17 % 490 + 10) : 0,
                    'source_platform' => null,
                    'source_url' => null,
                ])->create();

                $post->tags()->sync($tags->random(min(4, $tags->count()))->slice(0, random_int(2, 4))->pluck('id')->all());
                $index++;
            }
        }
    }

    private function attachCoverImages(): void
    {
        $coversByCategory = [
            'tecnicas-artesanais' => 'tecnicas-artesanais.png',
            'materiais-e-ferramentas' => 'materiais-e-ferramentas.png',
            'inspiracao-e-criatividade' => 'inspiracao-e-criatividade.png',
            'historias-de-arte' => 'historias-de-arte.png',
            'eventos-e-workshops' => 'eventos-e-workshops.png',
        ];

        Post::query()
            ->whereNull('source_platform')
            ->with('category')
            ->get()
            ->each(function (Post $post) use ($coversByCategory): void {
                if ($post->hasMedia('cover')) {
                    return;
                }

                $filename = $coversByCategory[$post->category?->slug] ?? null;

                if ($filename === null) {
                    return;
                }

                $path = database_path('seeders/assets/blog/'.$filename);

                if (! is_file($path)) {
                    $this->command?->warn("Imagem de demonstração ausente: {$filename}");

                    return;
                }

                $post
                    ->addMedia($path)
                    ->preservingOriginal()
                    ->usingFileName($filename)
                    ->usingName($post->title)
                    ->toMediaCollection('cover');
            });
    }
}
