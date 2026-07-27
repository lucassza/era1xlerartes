<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use App\Services\PostEditorialWorkflowService;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! auth()->user()?->hasRole('Editor') && auth()->user()?->author) {
            $data['author_id'] = auth()->user()->author->getKey();
        }

        return app(PostEditorialWorkflowService::class)
            ->guardAndNormalizePayload($data, auth()->user());
    }
}
