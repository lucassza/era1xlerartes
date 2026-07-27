<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\PostCommentStatus;
use App\Enums\TestimonialStatus;
use App\Models\PostComment;
use App\Models\Testimonial;
use App\Models\User;
use Filament\Support\Colors\Color;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Spatie\Activitylog\Models\Activity;

class EngagementOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $pendingTestimonials = Testimonial::query()
            ->where('status', TestimonialStatus::Pending->value)
            ->count();

        $pendingComments = PostComment::query()
            ->where('status', PostCommentStatus::Pending->value)
            ->count();

        $newUsersLast7Days = User::query()
            ->where('type', 'site')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $bannedUsers = User::query()
            ->whereNotNull('banned_at')
            ->count();

        $lastActivity = Activity::query()
            ->latest('created_at')
            ->first();

        $primary = Color::hex('#d42020');
        $accent = Color::hex('#e6287e');
        $warning = Color::hex('#b45309');
        $info = Color::hex('#0ea5e9');

        return [
            Stat::make('Depoimentos pendentes', (string) $pendingTestimonials)
                ->description('Aguardando sua aprovação')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color($pendingTestimonials > 0 ? $warning : 'gray')
                ->url(route('filament.admin.resources.testimonials.index', ['tableFilters[status][value]' => 'pending'])),

            Stat::make('Comentários pendentes', (string) $pendingComments)
                ->description('Aguardando moderação')
                ->descriptionIcon('heroicon-m-chat-bubble-bottom-center-text')
                ->color($pendingComments > 0 ? $primary : 'gray')
                ->url(route('filament.admin.resources.post-comments.index', ['tableFilters[status][value]' => 'pending'])),

            Stat::make('Novos usuários (7d)', (string) $newUsersLast7Days)
                ->description('Site público')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color($info),

            Stat::make('Usuários banidos', (string) $bannedUsers)
                ->description('Bloqueados pelo admin')
                ->descriptionIcon('heroicon-m-no-symbol')
                ->color($bannedUsers > 0 ? 'danger' : 'gray'),

            Stat::make('Última atividade', optional($lastActivity?->created_at)->diffForHumans() ?? '—')
                ->description($lastActivity ? mb_strimwidth($lastActivity->description ?? '-', 0, 40, '…') : 'Sem registros')
                ->descriptionIcon('heroicon-m-clock')
                ->color($accent),

        ];
    }
}
