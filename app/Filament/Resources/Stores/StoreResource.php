<?php

namespace App\Filament\Resources\Stores;

use App\Filament\Resources\Stores\Pages\ManageStores;
use App\Models\Store;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Override;
use UnitEnum;

class StoreResource extends Resource
{
    protected static ?string $model = Store::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Toko';

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::user()->id);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')->default(Auth::user()->id),
                Select::make('platform')
                    ->label('Platform')
                    ->options([
                        'shopee' => 'Shopee',
                        'lazada' => 'Lazada',
                        'tokopedia' => 'Tokopedia',
                        'blibli' => 'Blibli',
                        'bukalapak' => 'Bukalapak',
                        'tiktokshop' => 'Tiktokshop',
                        'offline' => 'Offline / Toko Fisik',
                    ])
                    ->searchable()
                    ->required(),
                TextInput::make('shop_name')
                    ->label('Nama Toko')
                    ->autofocus()
                    ->required(),
                Section::make()
                    ->schema([
                        TextInput::make('admin_fee')
                            ->label('Biaya Admin (%)')
                            ->numeric()
                            ->columnSpan(1)
                            ->required(),
                        TextInput::make('processing_fee')
                            ->label('Biaya Pesanan')
                            ->columnSpan(1)
                            ->required()
                            ->extraInputAttributes(['type' => 'text', 'inputmode' => 'numeric'])
                            ->mask(RawJs::make('$money($input, \'.\', \',\', 0)'))

                            // PERBAIKAN: Buang koma (,), atau bersihkan semua karakter non-digit agar aman 100%
                            ->dehydrateStateUsing(function ($state) {
                                if (! $state) return null;

                                // Menghapus semua karakter selain angka murni (termasuk titik atau koma)
                                return preg_replace('/[^0-9]/', '', $state);
                            }),
                        TextInput::make('extra_fee')
                            ->label('Biaya Lainnya')
                            ->columnSpan(1)
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ])
                    ->columns(3)
                    ->columnSpanFull()
                    ->compact(),
                Toggle::make('status')
                    // buat kondisi label agar bisa dinamis
                    ->label(fn($get) => $get('status') ? 'Aktif' : 'Tidak Aktif')
                    ->default(true)
                    ->columnStart(1)
                    ->required(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 2,
                ])
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('platform')->label('Platform')
                            ->formatStateUsing(fn($state) => ucwords($state)),
                        TextEntry::make('shop_name')->label('Nama Toko')
                            ->formatStateUsing(fn($state) => ucwords($state)),
                        TextEntry::make('admin_fee')->label('Biaya Admin (%)')
                            ->suffix('%'),
                        TextEntry::make('processing_fee')->label('Biaya Proses Per Pesanan')
                            ->money('IDR', locale: 'id_ID', decimalPlaces: 0),
                        TextEntry::make('extra_fee')->label('Biaya Lainnya')
                            ->money('IDR', locale: 'id_ID', decimalPlaces: 0),
                        IconEntry::make('status')->label('Status')
                            ->color(fn($get) => $get('status') ? 'success' : 'danger')
                            ->icon(fn($get) => $get('status') ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle'),
                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime('d M y H:i')
                            ->timezone('Asia/Jakarta'),
                        TextEntry::make('updated_at')
                            ->label('Diubah')
                            ->dateTime('d M y H:i')
                            ->timezone('Asia/Jakarta'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Store')
            ->deferLoading()
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M y H:i')
                    ->timezone('Asia/Jakarta')
                    ->sortable()
                    ->color('gray'),
                TextColumn::make('platform')
                    ->label('Platform')
                    ->sortable()
                    ->weight('bold')
                    ->formatStateUsing(fn($state) => ucfirst($state)),
                TextColumn::make('shop_name')
                    ->label('Nama Toko')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn($state) => ucwords($state))
                    ->searchable(),
                ColumnGroup::make('Biaya Operasional')
                    ->columns([
                        TextColumn::make('admin_fee')
                            ->label('Admin (%)')
                            ->suffix('%')
                            ->badge()
                            ->color('info')
                            ->alignment('center'),
                        TextColumn::make('processing_fee')
                            ->label('Proses (Rp)')
                            ->money('IDR', locale: 'id_ID', decimalPlaces: 0)
                            ->color('warning')
                            ->badge()
                            ->alignment('center'),
                    ])
                    ->alignment('center'),
                ToggleColumn::make('status')
                    ->label('Status'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->modalHeading('Detail Toko')
                    ->modalDescription('Detail Toko Marketplace atau Toko Offline')
                    ->modalWidth('lg')
                    ->slideOver(),
                EditAction::make()
                    ->iconButton()
                    ->modalHeading('Edit Toko')
                    ->modalDescription('Pastikan nama toko belum terdaftar sebelumnya.')
                    ->modalWidth('lg')
                    ->slideOver(),
                DeleteAction::make()
                    ->iconButton()
                    ->modalHeading('Hapus Toko'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading('Hapus Toko Yang Dipilih'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageStores::route('/'),
        ];
    }
}
