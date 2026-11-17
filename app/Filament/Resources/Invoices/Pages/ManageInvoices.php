<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ManageInvoices extends ManageRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        $tabs = ['all' => Tab::make('Semua')];
        foreach (Invoice::STATUSES as $status => $label) {
            $tabs[$status] = Tab::make($label)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $status))
                ->badge($status === 'waiting' ? (Invoice::where('status', 'waiting')->count() ?: null) : null)
                ->badgeColor('warning');
        }

        return $tabs;
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return Invoice::where('status', 'waiting')->exists() ? 'waiting' : 'all';
    }
}
