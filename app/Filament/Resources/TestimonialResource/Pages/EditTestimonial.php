<?php

declare(strict_types=1);

namespace App\Filament\Resources\TestimonialResource\Pages;

use App\Enums\TestimonialStatus;
use App\Filament\Resources\TestimonialResource;
use App\Models\Testimonial;
use App\Services\EngagementService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTestimonial extends EditRecord
{
    protected static string $resource = TestimonialResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        /** @var Testimonial $record */
        $record = $this->record;

        $newStatus = $data['status'] ?? $record->status->value;

        if ($newStatus === TestimonialStatus::Approved->value && $record->status !== TestimonialStatus::Approved) {
            $data['approved_at'] = now();
            $data['rejected_at'] = null;
            $data['rejection_reason'] = null;
        } elseif ($newStatus === TestimonialStatus::Rejected->value && $record->status !== TestimonialStatus::Rejected) {
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
                    /** @var Testimonial $record */
                    $record = $this->record;

                    return $record->status !== TestimonialStatus::Approved;
                })
                ->action(function (): void {
                    /** @var Testimonial $record */
                    $record = $this->record;
                    app(EngagementService::class)->approve($record);
                    $this->refreshFormData(['status', 'approved_at', 'rejected_at', 'rejection_reason']);
                }),
            Action::make('reject')
                ->label('Rejeitar')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(function (): bool {
                    /** @var Testimonial $record */
                    $record = $this->record;

                    return $record->status !== TestimonialStatus::Rejected;
                })
                ->action(function (): void {
                    /** @var Testimonial $record */
                    $record = $this->record;
                    app(EngagementService::class)->reject($record, 'manual');
                    $this->refreshFormData(['status', 'approved_at', 'rejected_at', 'rejection_reason']);
                }),
            DeleteAction::make(),
        ];
    }
}
