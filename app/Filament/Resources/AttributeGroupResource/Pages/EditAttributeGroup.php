<?php

declare(strict_types=1);

namespace App\Filament\Resources\AttributeGroupResource\Pages;

use App\Filament\Resources\AttributeGroupResource;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditAttributeGroup extends EditRecord
{
    protected static string $resource = AttributeGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('addAttribute')
                ->label('Adicionar atributo')
                ->icon('heroicon-o-plus')
                ->schema([
                    TextInput::make('name')->label('Nome')->required()->maxLength(255),
                    Textarea::make('description')->label('Descrição')->rows(2),
                ])
                ->action(function (array $data): void {
                    $groupId = $this->record instanceof AttributeGroup
                        ? $this->record->getKey()
                        : null;

                    if (! $groupId) {
                        Notification::make()
                            ->title('Grupo inválido.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $attribute = Attribute::query()->create([
                        'attribute_group_id' => $groupId,
                        'name' => $data['name'],
                        'slug' => Str::slug($data['name']),
                    ]);

                    Notification::make()
                        ->title("Atributo '{$attribute->name}' criado.")
                        ->success()
                        ->send();
                }),
            DeleteAction::make()->label('Excluir grupo'),
        ];
    }
}
