<?php

namespace App\Filament\Resources\ProductPrices\Pages;

use App\Filament\Resources\ProductPrices\ProductPriceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProductPrice extends EditRecord
{
    protected static string $resource = ProductPriceResource::class;

    protected static ?string $title = 'Ubah HPP Produk';

    // 1. MENGAMBIL DATA DARI DATABASE KE REPEATER
    // #[Override]
    // protected function mutateFormDataBeforeFill(array $data): array
    // {
    //     // Kita ambil semua data modal milik user ini
    //     // atau jika Anda ingin membatasi berdasarkan kriteria tertentu
    //     $data['data_modal'] = ProductPrice::where('user_id', Auth::id())
    //         ->get()
    //         ->map(fn($item) => [
    //             'product_id' => $item->product_id,
    //             'store_id'   => $item->store_id,
    //             'price'      => $item->price,
    //         ])
    //         ->toArray();

    //     return $data;
    // }

    // // 2. MENGHANDLE UPDATE (SIMPAN ULANG)
    // #[Override]
    // protected function handleRecordUpdate(Model $record, array $data): Model
    // {
    //     $items = $data['data_modal'] ?? [];

    //     // Hapus dulu data lama agar tidak duplikat saat disimpan ulang
    //     // (Sistem Sync: Hapus lama, Masukkan yang ada di form)
    //     ProductPrice::where('user_id', Auth::id())->delete();

    //     foreach ($items as $item) {
    //         ProductPrice::create([
    //             'user_id'    => Auth::id(),
    //             'product_id' => $item['product_id'],
    //             'store_id'   => $item['store_id'],
    //             'price'      => $item['price'],
    //         ]);
    //     }

    //     // Return record terakhir sebagai formalitas Filament
    //     return $record;
    // }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalHeading('Hapus HPP Produk'),
        ];
    }
}
