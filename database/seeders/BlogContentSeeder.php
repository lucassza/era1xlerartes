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
    }

    /**
     * @return Collection<int, Category>
     */
    private function seedCategories(): Collection
    {
        $items = [
            ['name' => 'Notícias', 'description' => 'Atualizações e novidades do universo TDBMix.'],
            ['name' => 'Tutoriais', 'description' => 'Passo a passo para miniaturas, cenários e personalizações.'],
            ['name' => 'Histórias', 'description' => 'Narrativas e universos das personagens do blog.'],
            ['name' => 'Eventos', 'description' => 'Cobertura de encontros, lançamentos e comemorações.'],
            ['name' => 'Colecionismo', 'description' => 'Curadoria, dicas e bastidores de coleção.'],
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
            'TDBMix',
            'Barbie',
            'Diorama',
            'DIY',
            'Tutorial',
            'Wedding',
            'Colecionismo',
            'Fotografia',
            'História de amor',
            'Evento',
            'Curitiba',
            'Mini mundo',
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
            ['name' => 'Editor TDBMix', 'email' => 'editor@example.com', 'role' => 'Editor', 'type' => UserType::Admin],
            ['name' => 'Angel TDBMix', 'email' => 'angelmic@gmail.com', 'role' => 'Super Admin', 'type' => UserType::Admin, 'password' => 'Senha123*'],
            ['name' => 'Equipe TDBMix', 'email' => 'equipe@example.com', 'role' => 'Author', 'type' => UserType::Admin],
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
                    'bio' => 'Autor seedado automaticamente para demonstração do projeto.',
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
            'tutoriais' => [
                ['title' => 'Como fazer um diorama de casamento passo a passo', 'excerpt' => 'Aprenda a criar um cenário romântico em miniatura com materiais simples e muito charme.'],
                ['title' => 'Pintando miniaturas com tinta acrílica: guia para iniciantes', 'excerpt' => 'Técnicas básicas de pintura que vão transformar suas miniaturas em obras de arte.'],
                ['title' => 'Como customizar roupas de Barbie com tricô e crochê', 'excerpt' => 'Tutorial completo para criar peças exclusivas de roupa para suas bonecas favoritas.'],
                ['title' => 'Construindo móveis em escala 1:6 com papelão e cola', 'excerpt' => 'Transforme materiais do dia a dia em mobiliário detalhado para suas cenas em miniatura.'],
                ['title' => 'Criando cenários de jardim em miniatura com musgo e pedrinhas', 'excerpt' => 'Dicas práticas para montar jardins realistas no universo das miniaturas.'],
                ['title' => 'Fotografia de miniaturas: como capturar a magia dos detalhes', 'excerpt' => 'Configurações de câmera e iluminação para fotografar suas criações com qualidade profissional.'],
                ['title' => 'Técnicas de decoupage para decorar acessórios de Barbie', 'excerpt' => 'Use papéis estampados e verniz para criar peças decorativas únicas para suas bonecas.'],
                ['title' => 'Como fazer cabelos realistas para bonecas com lã e fios', 'excerpt' => 'Método detalhado para criar cabeleiras volumosas e naturais para suas miniaturas.'],
                ['title' => 'Criando iluminação em miniatura com LEDs para dioramas noturnos', 'excerpt' => 'Técnica passo a passo para instalar micro LEDs e criar atmosfera noturna em suas cenas.'],
                ['title' => 'Moldando árvores em miniatura com arame e espuma floral', 'excerpt' => 'Construa vegetação realista para seus cenários usando materiais acessíveis e técnicas simples.'],
            ],
            'colecionismo' => [
                ['title' => 'As 10 Barbies vintage mais valiosas de todos os tempos', 'excerpt' => 'Conheça as edições raras que todo colecionador sonha em ter e quanto valem hoje.'],
                ['title' => 'Guia completo para conservar bonecas de coleção', 'excerpt' => 'Aprenda a armazenar, limpar e preservar suas preciosidades para que durem gerações.'],
                ['title' => 'Onde comprar miniaturas raras no Brasil: feiras e sebos', 'excerpt' => 'Um roteiro pelos melhores lugares para garimpar peças exclusivas para sua coleção.'],
                ['title' => 'Como identificar uma Barbie original de uma réplica', 'excerpt' => 'Diferenças sutis entre peças originais e cópias que todo colecionador precisa conhecer.'],
                ['title' => 'Organizando sua coleção de miniaturas: dicas de display', 'excerpt' => 'Ideias criativas para expor e organizar sua coleção de forma bonita e funcional.'],
                ['title' => 'Edições comemorativas de Barbie: um panorama histórico', 'excerpt' => 'Da primeira Barbie em 1959 até as edições de aniversário mais recentes e cobiçadas.'],
                ['title' => 'Mercado de miniaturas no Brasil: tendências e oportunidades', 'excerpt' => 'O colecionismo de miniaturas cresce no país — entenda o cenário atual do mercado.'],
                ['title' => 'Restaurando bonecas antigas: devolvendo vida às peças da infância', 'excerpt' => 'Técnicas de restauro para recuperar bonecas desgastadas com carinho e dedicação.'],
                ['title' => 'Barbies de edição limitada: como avaliar o investimento', 'excerpt' => 'Critérios para decidir quais edições limitadas valem a pena como peça de coleção.'],
                ['title' => 'Vitrine de exposição para miniaturas: monta DIY completo', 'excerpt' => 'Construa sua própria vitrine de acrílico para proteger e exibir sua coleção com estilo.'],
            ],
            'historias' => [
                ['title' => 'A história de amor de Angel e Leo em miniatura', 'excerpt' => 'Um conto emocionante narrado através de dioramas detalhados que capturam cada momento do casal.'],
                ['title' => 'O casamento dos sonhos de Lily: uma história em 12 cenas', 'excerpt' => 'Acompanhe do pedido ao buquê jogado em uma narrativa visual contada com muito talento.'],
                ['title' => 'A coleção que atravessou gerações: memórias de avó para neta', 'excerpt' => 'Uma história real e emocionante sobre bonecas que conectam passado e presente em uma família.'],
                ['title' => 'Mini mundo encantado: a aventura de Sofia no jardim das fadas', 'excerpt' => 'Uma história infantil ilustrada com dioramas mágicos criados especialmente para esta narrativa.'],
                ['title' => 'Como uma Barbie de 1975 mudou minha vida', 'excerpt' => 'Relato pessoal sobre a boneca herdada da mãe que despertou uma paixão pelo colecionismo.'],
                ['title' => 'O ateliê de moda em miniatura: a saga de uma estilista', 'excerpt' => 'Saga fictícia sobre uma pequena estilista no mundo das miniaturas que conquista passarelas imaginárias.'],
                ['title' => 'A casa dos sonhos: reconstruindo a infância em miniatura', 'excerpt' => 'Relato de como a montagem de uma casa de bonecas virou um projeto terapêutico e criativo.'],
                ['title' => 'Barbie cozinheira: a história da personagem que inspirou gerações', 'excerpt' => 'Da cozinha de 1960 às edições chef contemporâneas — uma narrativa sobre sonhos em plástico.'],
                ['title' => 'O diorama que levou três meses: bastidores de uma criação épica', 'excerpt' => 'A artista Angel conta cada detalhe do processo de construção de seu maior diorama até hoje.'],
                ['title' => 'Minha primeira Barbie e o início de tudo', 'excerpt' => 'Um depoimento sobre como uma boneca de presente de aniversário virou uma coleção de centenas de peças.'],
            ],
            'eventos' => [
                ['title' => 'Resumo do encontro TDBMix em Curitiba — edição 2026', 'excerpt' => 'Tudo que aconteceu no maior encontro de colecionadores de miniaturas do sul do Brasil.'],
                ['title' => 'Feira de miniaturas de São Paulo: o que esperar em 2026', 'excerpt' => 'Antecipamos os expositores confirmados e os lançamentos mais aguardados do evento.'],
                ['title' => 'Workshop de diorama em Belo Horizonte: inscrições abertas', 'excerpt' => 'Aprenda técnicas avançadas com artistas renomados neste workshop presencial de dois dias.'],
                ['title' => 'TDBMix no Rio: cobertura completa do encontro de janeiro', 'excerpt' => 'Fotos, destaques e entrevistas com os participantes do encontro carioca.'],
                ['title' => 'Lançamento da coleção especial Barbie Brasil 2026', 'excerpt' => 'Estivemos no evento de lançamento e trouxemos tudo sobre as novas edições exclusivas.'],
                ['title' => 'Concurso de dioramas TDBMix 2026: conheça os finalistas', 'excerpt' => 'As criações mais impressionantes que concorrem ao prêmio máximo do ano.'],
                ['title' => 'Festival de miniaturas de Florianópolis: guia completo', 'excerpt' => 'Programação, expositores e dicas de hospedagem para o festival anual da ilha.'],
                ['title' => 'Swap TDBMix: troque peças da sua coleção com outros fãs', 'excerpt' => 'Saiba como participar do nosso evento de troca de peças e encontrar raridades.'],
                ['title' => 'Encontro TDBMix em Porto Alegre: programação confirmada', 'excerpt' => 'Confirme presença e confira todas as atividades do encontro gaúcho de miniaturas.'],
                ['title' => 'Exposição TDBMix em parceria com museu local — entrada gratuita', 'excerpt' => 'Uma exposição especial de dioramas históricos aberta ao público por duas semanas.'],
            ],
            'noticias' => [
                ['title' => 'Novidades do universo TDBMix — Julho 2026', 'excerpt' => 'Atualizações sobre novos produtos, parcerias e o que está por vir no blog.'],
                ['title' => 'Mattel anuncia novas edições colecionáveis para o segundo semestre', 'excerpt' => 'Confira os modelos confirmados e as datas de lançamento no mercado brasileiro.'],
                ['title' => 'Entrevista: artista mineirinha faz sucesso com dioramas no Instagram', 'excerpt' => 'Conversamos com a criadora que acumula milhares de seguidores com suas cenas em miniatura.'],
                ['title' => 'TDBMix completa 5 anos: uma jornada de amor pelas miniaturas', 'excerpt' => 'Celebramos cinco anos do blog com uma retrospectiva dos momentos mais marcantes.'],
                ['title' => 'Tendências de colecionismo para 2026: o que está em alta', 'excerpt' => 'Do cottagecore ao maximalism, as estéticas que dominam o mundo das miniaturas agora.'],
                ['title' => 'Parceria TDBMix com artesãs locais: apoie quem faz com amor', 'excerpt' => 'Apresentamos a iniciativa que conecta colecionadores com produtoras independentes de miniaturas.'],
                ['title' => 'Nova seção do blog: tutoriais em vídeo chegam em agosto', 'excerpt' => 'Em breve o TDBMix terá conteúdo audiovisual para complementar nossos tutoriais escritos.'],
                ['title' => 'Barbie Movie um ano depois: o impacto no mercado colecionável brasileiro', 'excerpt' => 'Como o filme transformou o interesse em Barbie vintage e o que mudou nas coleções.'],
                ['title' => 'TDBMix agora tem newsletter semanal: inscreva-se!', 'excerpt' => 'Receba os melhores conteúdos do blog diretamente no seu e-mail toda semana.'],
                ['title' => 'Comunidade TDBMix no Discord: venha fazer parte!', 'excerpt' => 'Junte-se a centenas de colecionadores que já compartilham fotos e dicas no nosso servidor.'],
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
}
