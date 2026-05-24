<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Override;

class Dashboard extends BaseDashboard
{
  use HasFiltersForm;

  protected static ?string $title = 'Ringkasan Bisnis';

  protected static ?string $navigationLabel = 'Dashboard'; // Judul di Sidebar

  #[Override]
  public function getSubheading(): string|Htmlable|null
  {
    return 'Update terakhir: ' . now()->format('d F Y H:i');
  }

  public function filtersForm(Schema $schema): Schema
  {
    return $schema
      ->components([
        Section::make()
          ->schema([
            DatePicker::make('startDate'),
            DatePicker::make('endDate'),
            // ...
          ])
          ->columnSpanFull()
          ->columns(3),
      ]);
  }
}
