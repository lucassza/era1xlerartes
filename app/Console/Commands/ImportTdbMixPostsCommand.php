<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\TdbMixImportService;
use Illuminate\Console\Command;

class ImportTdbMixPostsCommand extends Command
{
    protected $signature = 'tdbmix:import-posts {--dry-run} {--limit= : Limita a quantidade de posts importados} {--url= : Importa apenas uma URL específica}';

    protected $description = 'Importa posts do TDBMix.com.br a partir do sitemap do Blogger.';

    public function handle(TdbMixImportService $importService): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $url = $this->option('url');
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;

        $urls = $url ? collect([(string) $url]) : $importService->fetchPostUrls($limit);

        if ($urls->isEmpty()) {
            $this->warn('Nenhuma URL encontrada para importação.');

            return self::SUCCESS;
        }

        foreach ($urls as $postUrl) {
            $parsedPost = $importService->fetchAndParse($postUrl);
            $result = $importService->importParsedPost($parsedPost, $dryRun);

            $this->line(sprintf(
                '[%s] %s',
                $result['created'] ? 'created' : 'updated',
                $result['source_url'],
            ));
        }

        $this->info(sprintf('%d post(s) processado(s).', $urls->count()));

        return self::SUCCESS;
    }
}
