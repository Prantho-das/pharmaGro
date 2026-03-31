<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('key')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Unique setting identifier (e.g., tax_rate, is_pharmacy_active)')
                    ->disabled(fn (string $operation): bool => $operation === 'edit'),

                // Dynamic field based on key
                Forms\Components\Select::make('value_type')
                    ->label('Value Type')
                    ->options([
                        'text' => 'Text',
                        'number' => 'Number',
                        'boolean' => 'Boolean (true/false)',
                    ])
                    ->default('text')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => self::updateValueField($set, $get)),

                // Conditional value field
                Forms\Components\TextInput::make('value')
                    ->required()
                    ->maxLength(65535)
                    ->helperText(fn (Forms\Get $get) => match ($get('value_type')) {
                        'number' => 'Numeric value (e.g., 15 for 15%)',
                        'boolean' => 'Enter "true" or "false"',
                        default => 'Setting value as text',
                    })
                    ->visible(fn (Forms\Get $get): bool => $get('key') !== null)
                    ->columnSpanFull(),
            ]);
    }

    private static function updateValueField(Forms\Set $set, Forms\Get $get): void
    {
        $key = $get('key');
        $type = $get('value_type');

        // Auto-detect type based on key if not manually set
        if (! $type) {
            if (in_array($key, ['is_pharmacy_active', 'some_boolean_setting'])) {
                $set('value_type', 'boolean');
            } elseif (is_numeric($key) || in_array($key, ['tax_rate', 'some_number_setting'])) {
                $set('value_type', 'number');
            } else {
                $set('value_type', 'text');
            }
        }

        // Set appropriate placeholder
        $placeholder = match ($type) {
            'number' => 'e.g., 15 for 15%',
            'boolean' => 'true or false',
            default => 'Enter value',
        };
        $set('value_placeholder', $placeholder);
    }
}
