<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\PostEditorialWorkflowService;
use Illuminate\Console\Command;

class PublishScheduledPostsCommand extends Command
{
    protected $signature = 'posts:publish-scheduled';

    protected $description = 'Publica posts agendados cuja data de publicação já venceu.';

    public function handle(PostEditorialWorkflowService $workflowService): int
    {
        $count = $workflowService->publishDueScheduledPosts();

        $this->info(sprintf('%d post(s) publicado(s).', $count));

        return self::SUCCESS;
    }
}
