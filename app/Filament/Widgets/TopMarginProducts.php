<?php

namespace App\Filament\Widgets;

use App\Models\TransactionItem;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TopMarginProducts extends TableWidget
{
    use InteractsWithPageFilters; // Agar tabel ikut terfilter tanggal & toko

    protected static ?string $heading = 'Top 5 Produk Paling Menguntungkan';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn(): Builder => TransactionItem::query())
            ->columns([
                TextColumn::make('product.name')
                    ->label('Nama Produk'),
                TextColumn::make('product.category.name')
                    ->label('Kategori'),
                TextColumn::make('price')
                    ->label('Harga'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
