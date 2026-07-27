<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostCommentResource\Pages;

use App\Enums\PostCommentStatus;
use App\Filament\Resources\PostCommentResource;
use App\Models\PostComment;
use App\Services\EngagementService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPostComment extends EditRecord
{
    protected static string $resource = PostCommentResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        /** @var PostComment $record */
        $record = $this->record;

        $newStatus = $data['status'] ?? $record->status->value;

        if ($newStatus === PostCommentStatus::Approved->value && $record->status !== PostCommentStatus::Approved) {
            $data['approved_at'] = now();
            $data['rejected_at'] = null;
            $data['rejection_reason'] = null;
        } elseif ($newStatus === PostCommentStatus::Rejected->value && $record->status !== PostCommentStatus::Rejected) {
            $data['rejected_at'] = now();
            $data['approved_at'] = null;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Aprovar')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(function (): bool {
                    /** @var PostComment $record */
                    $record = $this->record;

                    return $record->status !== PostCommentStatus::Approved;
                })
                ->action(function (): void {
                    /** @var PostComment $record */
                    $record = $this->record;
                    app(EngagementService::class)->approve($record);
                    $this->refreshFormData(['status', 'approved_at', 'rejected_at', 'rejection_reason']);
                }),
            Action::make('reject')
                ->label('Rejeitar')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(function (): bool {
                    /** @var PostComment $record */
                    $record = $this->record;

                    return $record->status !== PostCommentStatus::Rejected;
                })
                ->action(function (): void {
                    /** @var PostComment $record */
                    $record = $this->record;
                    app(EngagementService::class)->reject($record, 'manual');
                    $this->refreshFormData(['status', 'approved_at', 'rejected_at', 'rejection_reason']);
                }),
            DeleteAction::make(),
        ];
    }
}
