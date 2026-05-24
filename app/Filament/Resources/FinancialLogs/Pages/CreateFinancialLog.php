<?php

namespace App\Filament\Resources\FinancialLogs\Pages;

use App\Filament\Resources\FinancialLogs\FinancialLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFinancialLog extends CreateRecord
{
    protected static string $resource = FinancialLogResource::class;
}
