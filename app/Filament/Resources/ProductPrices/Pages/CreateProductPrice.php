<?php

namespace App\Filament\Resources\ProductPrices\Pages;

use App\Filament\Resources\ProductPrices\ProductPriceResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Override;

class CreateProductPrice extends CreateRecord
{
    protected static string $resource = ProductPriceResource::class;

    protected static ?string $title = 'Buat HPP Produk';

    #[Override]
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::user()->id;

        return parent::mutateFormDataBeforeCreate($data);
    }

    #[Override]
    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Buat HPP');
    }

    #[Override]
    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()->label('Tambah & Buat Lagi');
    }

    #[Override]
    protected function preserveFormDataWhenCreatingAnother(array $data): array
    {
        return Arr::only($data, 'store_id');
    }

    #[Override]
    protected function getCreatedNotification(): ?Notification
    {
        return parent::getCreatedNotification()->title('HPP berhasil dibuat');
    }

    // #[Override]
    // protected function handleRecordCreation(array $data): Model
    // {
    //     $items = $data['data_modal'] ?? [];
    //     $lastRecord = null;

    //     foreach ($items as $item) {
    //         $lastRecord = ProductPrice::create([
    //             'user_id'    => Auth::id(),
    //             'product_id' => $item['product_id'],
    //             'store_id'   => $item['store_id'],
    //             'price'      => $item['price'],
    //         ]);
    //     }

    //     // Filament butuh mengembalikan satu objek model agar tidak error
    //     // Kita kembalikan record terakhir yang dibuat
    //     return $lastRecord;
    // }
}
