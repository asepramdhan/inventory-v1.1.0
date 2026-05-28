<?php

namespace App\Filament\Resources\AdsCosts;

use App\Filament\Resources\AdsCosts\Pages\ManageAdsCosts;
use App\Models\AdsCost;
use App\Models\Store;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Override;
use UnitEnum;

class AdsCostResource extends Resource
{
    protected static ?string $model = AdsCost::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|UnitEnum|null $navigationGroup = 'Keuangan & Analisa';

    protected static ?string $navigationLabel = ' Biaya Iklan';

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::user()->id);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')->default(Auth::user()->id),
                Select::make('store_id')
                    ->label('Toko / Store')
                    ->options(
                        Store::query()
                            ->where('user_id', Auth::user()->id)
                            ->pluck('shop_name', 'id')
                    )
                    ->placeholder('Pilih Toko')
                    ->searchable()
                    ->native(false)
                    ->required(),

                DateTimePicker::make('created_at')
                    ->label('Tanggal Pembuatan')
                    ->default(now())
                    ->required()
                    ->extraAttributes(['class' => 'mt-4']),

                TextInput::make('amount')
                    ->label('Nominal Biaya Iklan')
                    ->prefix('Rp')
                    ->required()
                    ->extraInputAttributes(['type' => 'text', 'inputmode' => 'numeric'])
                    ->mask(RawJs::make('$money($input, \'.\', \',\', 0)'))
                    ->dehydrateStateUsing(function ($state) {
                        if (! $state) return null;
                        return preg_replace('/[^0-9]/', '', $state);
                    })
                    ->extraAttributes(['class' => 'mt-4']),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('store.shop_name')
                    ->label('Toko / Store')
                    ->badge()
                    ->color('warning'),

                TextEntry::make('amount')
                    ->label('Nominal Biaya Iklan')
                    ->money('IDR', locale: 'id_ID', decimalPlaces: 0)
                    ->weight('bold')
                    ->extraAttributes(['class' => 'mt-4']),

                TextEntry::make('created_at')
                    ->label('Tanggal & Waktu')
                    ->dateTime('d M Y H:i')
                    ->extraAttributes(['class' => 'mt-4']),

                TextEntry::make('updated_at')
                    ->label('Diubah pada')
                    ->dateTime('d M Y H:i')
                    ->extraAttributes(['class' => 'mt-4']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('AdsCost')
            ->deferLoading()
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('store.shop_name')
                    ->label('Nama Toko')
                    ->searchable(),

                TextColumn::make('amount')
                    ->label('Biaya Iklan')
                    ->money('IDR', locale: 'id_ID', decimalPlaces: 0)
                    ->badge()
                    ->color('warning')
                    ->sortable(),
            ])
            ->filters([
                // Filter bisa ditambahkan di sini jika diperlukan
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->modalHeading('Detail Biaya Iklan')
                    ->modalWidth('md')
                    ->slideOver(),
                EditAction::make()
                    ->iconButton()
                    ->modalHeading('Ubah Biaya Iklan')
                    ->modalWidth('md')
                    ->slideOver()
                    ->after(function ($livewire) {
                        $livewire->dispatch('refreshTabs');
                    }),
                DeleteAction::make()
                    ->iconButton()
                    ->modalHeading('Hapus Biaya Iklan')
                    ->after(function ($livewire) {
                        $livewire->dispatch('refreshTabs');
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading('Hapus Biaya Yang Dipilih'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAdsCosts::route('/'),
        ];
    }
}
