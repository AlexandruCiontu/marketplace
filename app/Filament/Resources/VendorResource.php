<?php

namespace App\Filament\Resources;

use App\Enums\RolesEnum;
use App\Enums\VendorStatusEnum;
use App\Filament\Resources\VendorResource\Pages;
use App\Filament\Resources\VendorResource\RelationManagers;
use App\Models\Vendor;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('User Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                    ])
                    ->relationship('user'),
                Forms\Components\Fieldset::make('Vendor Details')
                    ->schema([
                        Forms\Components\TextInput::make('store_name')
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options(VendorStatusEnum::labels())
                            ->required(),
                        Forms\Components\Select::make('country_code')
                            ->options([
                                'RO' => 'România',
                                'HU' => 'Ungaria',
                                'BG' => 'Bulgaria',
                            ])
                            ->required()
                            ->reactive(),
                        Forms\Components\TextInput::make('commission_rate')
                            ->numeric()
                            ->required()
                            ->label('Commission %'),
                        Forms\Components\TextInput::make('anaf_pfx_path')
                            ->label('ANAF PFX Path')
                            ->visible(fn (callable $get) => $get('country_code') === 'RO'),
                        Forms\Components\TextInput::make('nav_user_id')
                            ->label('NAV User ID')
                            ->visible(fn (callable $get) => $get('country_code') === 'HU'),
                        Forms\Components\TextInput::make('nav_exchange_key')
                            ->label('NAV Exchange Key')
                            ->visible(fn (callable $get) => $get('country_code') === 'HU'),
                        Forms\Components\Textarea::make('store_address')
                            ->columnSpan(2),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('store_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('country_code')
                    ->label('Country')
                    ->sortable(),
                TextColumn::make('commission_rate')
                    ->label('Commission %')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors(VendorStatusEnum::colors())
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVendors::route('/'),
            'create' => Pages\CreateVendor::route('/create'),
            'edit' => Pages\EditVendor::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = Filament::auth()->user();

        // Example: Show this menu item only to users with the 'admin' role
        return $user && $user->hasRole(RolesEnum::Admin);
    }
}
