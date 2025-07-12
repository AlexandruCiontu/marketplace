<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatusEnum;
use App\Enums\RolesEnum;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Number;
use NumberFormatter;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->forVendor()
            ->where('status', '!=', OrderStatusEnum::Draft);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options(OrderStatusEnum::labels())
                    ->required(),
                Forms\Components\TextInput::make('tracking_code')
                    ->label(__('Shipping Tracking Code'))
                    ->placeholder('Enter shipping tracking number')
                    ->helperText('Provide the tracking code from the shipping carrier. The customer will receive an email notification.')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('Order Number'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('Customer'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label(__('Order Total'))
                    ->formatStateUsing(function ($state) {
                        return Number::currency($state, config('app.currency'));
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('vendor_subtotal')
                    ->label(__('My Subtotal'))
                    ->formatStateUsing(function ($state) {
                        return Number::currency($state, config('app.currency'));
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors(OrderStatusEnum::colors())
                    ->sortable(),
                Tables\Columns\TextColumn::make('tracking_code')
                    ->label(__('Tracking Code'))
                    ->searchable()
                    ->placeholder('—')
                    ->copyable()
                    ->icon('heroicon-o-truck'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = Filament::auth()->user();

        // Example: Show this menu item only to users with the 'admin' role
        return $user && $user->hasRole(RolesEnum::Vendor);
    }
}
