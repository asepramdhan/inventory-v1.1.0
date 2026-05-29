<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Illuminate\Contracts\Support\Htmlable;
use Override;
use App\Filament\Widgets\DevelopmentAlertWidget;

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

  #[Override]
  public function getHeaderWidgets(): array
  {
    return [
      DevelopmentAlertWidget::class, // <--- Pasang widget di sini
    ];
  }

  #[Override]
  public function getHeaderWidgetsColumns(): int|array
  {
    return 1;
  }

  // public function filtersForm(Schema $schema): Schema
  // {
  //   return $schema
  //     ->components([
  //       Section::make()
  //         ->schema([
  //           DatePicker::make('startDate'),
  //           DatePicker::make('endDate'),
  //           // ...
  //         ])
  //         ->columnSpanFull()
  //         ->columns(3),
  //     ]);
  // }
}
