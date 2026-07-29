<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;
use App\Services\EngagementService;
use Illuminate\Support\Facades\DB;

class UserObserver
{
    public function __construct(private readonly EngagementService $engagementService) {}

    public function updated(User $user): void
    {
        if (! $user->wasChanged('banned_at')) {
            return;
        }

        $previous = $user->getOriginal('banned_at');
        $current = $user->banned_at;

        $justBanned = filled($current) && blank($previous);

        if ($justBanned) {
            DB::transaction(function () use ($user): void {
                $this->engagementService->rejectPendingByUser($user, 'user_banned');
            });
        }
    }
}
