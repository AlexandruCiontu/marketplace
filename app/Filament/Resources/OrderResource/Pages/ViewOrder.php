<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Card;
use Filament\Infolists\Components\Group;
use Illuminate\Support\Number;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make()
                    ->schema([
                        // First row - Order summary card
                        Card::make()
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        // First column - Order identifiers
                                        Group::make([
                                            TextEntry::make('id')
                                                ->label('Order #')
                                                ->size('lg')
                                                ->weight('bold')
                                                ->copyable(),
                                            TextEntry::make('created_at')
                                                ->label('Date')
                                                ->dateTime('M d, Y h:i A'),
                                        ])
                                        ->columnSpan(['lg' => 1]),

                                        // Second column - Status
                                        Group::make([
                                            TextEntry::make('status')
                                                ->label('Status')
                                                ->badge()
                                                ->size('lg')
                                                ->color(fn (string $state): string => match($state) {
                                                    OrderStatusEnum::Paid->value => 'primary',
                                                    OrderStatusEnum::Shipped->value => 'warning',
                                                    OrderStatusEnum::Delivered->value => 'success',
                                                    OrderStatusEnum::Cancelled->value => 'danger',
                                                    default => 'gray',
                                                })
                                        ])
                                        ->columnSpan(['lg' => 1]),

                                        // Third column - Totals
                                        Group::make([
                                            TextEntry::make('total_price')
                                                ->label('Total')
                                                ->formatStateUsing(fn ($state) => Number::currency($state, config('app.currency')))
                                                ->size('lg')
                                                ->weight('bold')
                                                ->color('success')
                                        ])
                                        ->columnSpan(['lg' => 1])
                                    ])
                                    ->columns(3)
                            ])
                            ->columnSpan(['lg' => 2]),

                        // Second row - Financial details card
                        Card::make()
                            ->schema([
                                Group::make([
                                    TextEntry::make('vendor_subtotal')
                                        ->label('Vendor Subtotal')
                                        ->formatStateUsing(fn ($state) => Number::currency($state, config('app.currency')))
                                        ->inlineLabel()
                                        ->size('sm'),

                                    TextEntry::make('online_payment_commission')
                                        ->label('Payment Commission')
                                        ->formatStateUsing(fn ($state) => Number::currency($state, config('app.currency')))
                                        ->inlineLabel()
                                        ->size('sm'),

                                    TextEntry::make('website_commission')
                                        ->label('Website Commission')
                                        ->formatStateUsing(fn ($state) => Number::currency($state, config('app.currency')))
                                        ->inlineLabel()
                                        ->size('sm'),
                                ])
                            ])
                            ->collapsible()
                            ->columnSpan(['lg' => 1]),
                    ])
                    ->columns(['lg' => 3]),

                // Customer and Shipping Information
                Grid::make()
                    ->schema([
                        // Customer information
                        Card::make()
                            ->schema([
                                Section::make('Customer Information')
                                    ->collapsible(false)
                                    ->compact()
                                    ->schema([
                                        TextEntry::make('user.name')
                                            ->label('Name')
                                            ->icon('heroicon-o-user')
                                            ->weight('medium'),

                                        TextEntry::make('user.email')
                                            ->label('Email')
                                            ->icon('heroicon-o-envelope')
                                            ->copyable(),
                                    ])
                                    ->columns(1),
                            ])
                            ->columnSpan(['lg' => 1]),

                        // Shipping address
                        Card::make()
                            ->schema([
                                Section::make('Shipping Details')
                                    ->compact()
                                    ->collapsible(false)
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('shippingAddress.full_name')
                                                    ->label('Recipient')
                                                    ->icon('heroicon-o-user')
                                                    ->weight('medium'),

                                                TextEntry::make('shippingAddress.phone')
                                                    ->label('Phone')
                                                    ->icon('heroicon-o-phone')
                                                    ->copyable(),
                                            ]),

                                        TextEntry::make('shippingAddress.address1')
                                            ->label('Address')
                                            ->icon('heroicon-o-map-pin'),

                                        TextEntry::make('shippingAddress.address2')
                                            ->label('Address Line 2')
                                            ->visible(fn ($record) => !empty($record->shippingAddress?->address2)),

                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('shippingAddress.city')
                                                    ->label('City'),

                                                TextEntry::make('shippingAddress.state')
                                                    ->label('State'),

                                                TextEntry::make('shippingAddress.zipcode')
                                                    ->label('Zip Code'),
                                            ]),

                                        TextEntry::make('shippingAddress.country.name')
                                            ->label('Country'),

                                        TextEntry::make('shippingAddress.delivery_instructions')
                                            ->label('Delivery Notes')
                                            ->visible(fn ($record) => !empty($record->shippingAddress?->delivery_instructions))
                                            ->icon('heroicon-o-information-circle'),
                                    ]),
                            ])
                            ->columnSpan(['lg' => 2]),
                    ])
                    ->columns(['lg' => 3]),

                // Order Items Section
                Card::make()
                    ->schema([
                        Section::make('Order Items')
                            ->schema([
                                // Totals summary
                                Grid::make()
                                    ->schema([
                                        Group::make([
                                            TextEntry::make('subtotal')
                                                ->label('Items Subtotal')
                                                ->state(function ($record) {
                                                    $subtotal = 0;
                                                    foreach ($record->orderItems as $item) {
                                                        $subtotal += $item->price * $item->quantity;
                                                    }
                                                    return $subtotal;
                                                })
                                                ->formatStateUsing(fn ($state) => Number::currency($state, config('app.currency')))
                                                ->inlineLabel()
                                        ])
                                        ->columnSpan(1),

                                        Group::make([
                                            TextEntry::make('total')
                                                ->label('Grand Total')
                                                ->state(function ($record) {
                                                    return $record->total_price;
                                                })
                                                ->formatStateUsing(fn ($state) => Number::currency($state, config('app.currency')))
                                                ->weight('bold')
                                                ->size('lg')
                                                ->color('success')
                                                ->inlineLabel()
                                        ])
                                        ->columnSpan(1),
                                    ])
                                    ->columns(2),

                                // Order items list
                                RepeatableEntry::make('orderItems')
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                // Product image
                                                ImageEntry::make('product_image')
                                                    ->getStateUsing(function ($record) {
                                                        if ($record->product) {
                                                            if (!empty($record->variation_type_option_ids)) {
                                                                return $record->product->getImageForOptions($record->variation_type_option_ids);
                                                            }
                                                            return $record->product->getFirstImageUrl();
                                                        }
                                                        return null;
                                                    })
                                                    ->defaultImageUrl(asset('images/placeholder-product.jpg'))
                                                    ->height(60)
                                                    ->width(60)
                                                    ->columnSpan(['lg' => 1]),

                                                // Product details
                                                Group::make([
                                                    TextEntry::make('product.title')
                                                        ->label(null)
                                                        ->placeholder('Product')
                                                        ->weight('bold'),

                                                    TextEntry::make('product.user.vendor.store_name')
                                                        ->label('Sold by')
                                                        ->color('gray')
                                                        ->size('sm')
                                                        ->visible(fn ($record) => $record->product?->user?->vendor),

                                                    TextEntry::make('variationDetails')
                                                        ->label(null)
                                                        ->size('sm')
                                                        ->color('gray')
                                                        ->getStateUsing(function ($record) {
                                                            if (empty($record->variation_type_option_ids)) {
                                                                return null;
                                                            }

                                                            $variations = [];
                                                            foreach ($record->variationOptions as $option) {
                                                                $variations[] = "{$option->variationType->name}: {$option->name}";
                                                            }

                                                            return !empty($variations) ? implode(', ', $variations) : null;
                                                        })
                                                        ->visible(fn ($record) => !empty($record->variation_type_option_ids)),
                                                ])
                                                ->columnSpan(['lg' => 4]),

                                                // Price details
                                                Group::make([
                                                    Grid::make(3)
                                                        ->schema([
                                                            TextEntry::make('price')
                                                                ->label('Unit Price')
                                                                ->formatStateUsing(fn ($state) => Number::currency($state, config('app.currency')))
                                                                ->size('sm'),

                                                            TextEntry::make('quantity')
                                                                ->label('Qty')
                                                                ->size('sm'),

                                                            TextEntry::make('itemTotal')
                                                                ->label('Total')
                                                                ->formatStateUsing(fn ($record) => Number::currency($record->price * $record->quantity, config('app.currency')))
                                                                ->getStateUsing(fn ($record) => $record->price * $record->quantity)
                                                                ->weight('medium')
                                                                ->size('sm')
                                                                ->color('success'),
                                                        ])
                                                ])
                                                ->columnSpan(['lg' => 3]),
                                            ])
                                            ->columns(['lg' => 8])
                                    ])
                                    ->contained(false)
                                    ->columnSpanFull(),
                            ])
                            ->compact(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->icon('heroicon-o-pencil'),
            Actions\Action::make('print_invoice')
                ->label('Print Invoice')
                ->icon('heroicon-o-printer')
                ->url(fn (Order $record) => route('filament.admin.resources.orders.invoice', $record))
                ->openUrlInNewTab(),
        ];
    }
}
