<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\InquiryStatus;
use App\Models\ProductInquiry;
use App\Models\ProductInquiryNote;
use App\Models\ProductInquiryStatusHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ProductInquiryService
{
    public function markAsViewed(ProductInquiry $inquiry, ?User $user = null): bool
    {
        if ($inquiry->first_viewed_at !== null) {
            return false;
        }

        $inquiry->first_viewed_at = now();
        $inquiry->status = InquiryStatus::Viewed;
        $inquiry->save();

        $this->logStatusChange($inquiry, InquiryStatus::New, InquiryStatus::Viewed, $user, 'Primeira visualização');

        return true;
    }

    public function changeStatus(ProductInquiry $inquiry, InquiryStatus $newStatus, ?User $user = null, ?string $note = null): void
    {
        $currentStatus = $inquiry->getAttribute('status');
        $previous = $currentStatus instanceof InquiryStatus
            ? $currentStatus
            : InquiryStatus::from((string) $currentStatus);

        if ($previous === $newStatus) {
            return;
        }

        $inquiry->status = $newStatus;

        if ($newStatus === InquiryStatus::InService && $inquiry->first_answered_at === null) {
            $inquiry->first_answered_at = now();
        }

        if ($newStatus === InquiryStatus::Completed && $inquiry->completed_at === null) {
            $inquiry->completed_at = now();
        }

        $inquiry->save();

        $this->logStatusChange($inquiry, $previous, $newStatus, $user, $note);
    }

    public function assign(ProductInquiry $inquiry, ?User $assignee, ?User $actor = null): void
    {
        $previousId = $inquiry->assignee_id;
        $inquiry->assignee_id = $assignee?->getKey();
        $inquiry->save();

        $note = $assignee
            ? "Responsável atribuído a {$assignee->name}."
            : 'Responsável removido.';

        ProductInquiryStatusHistory::query()->create([
            'product_inquiry_id' => $inquiry->id,
            'from_status' => $inquiry->status,
            'to_status' => $inquiry->status,
            'user_id' => $actor?->getKey(),
            'note' => $note,
        ]);
    }

    public function addNote(ProductInquiry $inquiry, User $user, string $note): ProductInquiryNote
    {
        return ProductInquiryNote::query()->create([
            'product_inquiry_id' => $inquiry->id,
            'user_id' => $user->getKey(),
            'note' => $note,
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function buildListQuery(array $filters): Builder
    {
        $query = ProductInquiry::query()->with(['product', 'assignee'])->latest('created_at');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (! empty($filters['assignee_id'])) {
            $query->where('assignee_id', $filters['assignee_id']);
        }

        if (! empty($filters['origin'])) {
            $query->where('origin', $filters['origin']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboardStats(): array
    {
        $base = ProductInquiry::query();

        $new = (clone $base)->where('status', InquiryStatus::New->value)->count();
        $inService = (clone $base)->where('status', InquiryStatus::InService->value)->count();
        $completed = (clone $base)->where('status', InquiryStatus::Completed->value)->count();
        $total = (clone $base)->count();
        $open = (clone $base)->whereIn('status', [
            InquiryStatus::New->value,
            InquiryStatus::Viewed->value,
            InquiryStatus::InService->value,
            InquiryStatus::WaitingCustomer->value,
            InquiryStatus::Answered->value,
        ])->count();

        $completionRate = $total > 0 ? round(($completed / $total) * 100, 1) : 0.0;

        $avgResponseSeconds = (clone $base)
            ->whereNotNull('first_answered_at')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (first_answered_at - created_at))) as avg_seconds')
            ->value('avg_seconds');

        $avgResponseMinutes = $avgResponseSeconds !== null
            ? round((float) $avgResponseSeconds / 60, 1)
            : null;

        return [
            'new' => $new,
            'in_service' => $inService,
            'completed' => $completed,
            'open' => $open,
            'total' => $total,
            'completion_rate' => $completionRate,
            'avg_first_response_minutes' => $avgResponseMinutes,
        ];
    }

    private function logStatusChange(
        ProductInquiry $inquiry,
        InquiryStatus $from,
        InquiryStatus $to,
        ?User $user,
        ?string $note,
    ): void {
        ProductInquiryStatusHistory::query()->create([
            'product_inquiry_id' => $inquiry->id,
            'from_status' => $from,
            'to_status' => $to,
            'user_id' => $user?->getKey(),
            'note' => $note,
        ]);
    }
}
