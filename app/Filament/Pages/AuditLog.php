<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

class AuditLog extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Auditoria';

    protected static ?string $title = 'Auditoria';

    protected static ?string $slug = 'audit';

    protected static ?int $navigationSort = 20;

    public static function getNavigationGroup(): ?string
    {
        return 'Configurações';
    }

    protected string $view = 'filament.pages.audit-log';

    /**
     * @var Collection<int, Activity>
     */
    public Collection $activities;

    public function mount(): void
    {
        $this->activities = Activity::query()
            ->with(['causer', 'subject'])
            ->latest('created_at')
            ->limit(100)
            ->get();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('audit.read') ?? false;
    }
}
