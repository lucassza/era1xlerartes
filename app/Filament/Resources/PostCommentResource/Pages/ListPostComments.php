<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostCommentResource\Pages;

use App\Filament\Resources\PostCommentResource;
use Filament\Resources\Pages\ListRecords;

class ListPostComments extends ListRecords
{
    protected static string $resource = PostCommentResource::class;
}
