<?php

namespace App\Livewire\Tenant;

use App\Models\Product;
use App\Models\Category;
use App\Models\Tenant;
use App\Models\Transaction;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class InventoryReports extends Component
{
    use WithPagination;

    public $search = '';
    public $filterCategory = '';
    public $filterStockStatus = 'all'; // all, low_stock, out_of_stock, in_stock
    public $sortBy = 'name'; // name, stock, price, last_updated
    public $sortDirection = 'asc';

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'filterCategory' => 'nullable|exists:categories,id',
            'filterStockStatus' => 'in:all,low_stock,out_of_stock,in_stock',
            'sortBy' => 'in:name,stock,price,last_updated',
            'sortDirection' => 'in:asc,desc',
        ];
    }

    #[Computed]
    public function products()
    {
        $query = Product::with(['category'])
            ->where('tenant_id', $this->getCurrentTenant()->id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%')
                      ->orWhereHas('category', function ($category) {
                          $category->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->filterCategory, function ($query) {
                $query->where('category_id', $this->filterCategory);
            })
            ->when($this->filterStockStatus !== 'all', function ($query) {
                switch ($this->filterStockStatus) {
                    case 'low_stock':
                        $query->whereColumn('stock', '<=', 'min_stock');
                        break;
                    case 'out_of_stock':
                        $query->where('stock', 0);
                        break;
                    case 'in_stock':
                        $query->where('stock', '>', 0)->whereColumn('stock', '>', 'min_stock');
                        break;
                }
            });

        // Apply sorting
        switch ($this->sortBy) {
            case 'stock':
                $query->orderBy('stock', $this->sortDirection);
                break;
            case 'price':
                $query->orderBy('price', $this->sortDirection);
                break;
            case 'last_updated':
                $query->orderBy('updated_at', $this->sortDirection);
                break;
            default:
                $query->orderBy('name', $this->sortDirection);
                break;
        }

        return $query->paginate(15);
    }

    #[Computed]
    public function categories()
    {
        return Category::where('tenant_id', $this->getCurrentTenant()->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function inventorySummary()
    {
        $storeId = $this->getCurrentTenant()->id;

        return [
            'total_products' => Product::where('tenant_id', $storeId)->count(),
            'total_stock_value' => Product::where('tenant_id', $storeId)->selectRaw('SUM(stock * cost)')->value('SUM(stock * cost)') ?? 0,
            'low_stock_count' => Product::where('tenant_id', $storeId)->whereColumn('stock', '<=', 'min_stock')->count(),
            'out_of_stock_count' => Product::where('tenant_id', $storeId)->where('stock', 0)->count(),
            'total_categories' => Category::where('tenant_id', $storeId)->where('is_active', true)->count(),
        ];
    }

    #[Computed]
    public function stockValueByCategory()
    {
        $data = Product::where('products.tenant_id', $this->getCurrentTenant()->id)
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->selectRaw('categories.name, SUM(products.stock * products.cost) as total_value, SUM(products.stock) as total_stock')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_value')
            ->get();

        return [
            'labels' => $data->pluck('name')->toArray(),
            'values' => $data->pluck('total_value')->toArray(),
            'stocks' => $data->pluck('total_stock')->toArray(),
        ];
    }

    #[Computed]
    public function lowStockProducts()
    {
        return Product::with('category')
            ->where('tenant_id', $this->getCurrentTenant()->id)
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock', 'asc')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function mostSoldProducts()
    {
        $storeId = $this->getCurrentTenant()->id;

        return Transaction::where('transactions.status', 'completed')
            ->where('transactions.tenant_id', $storeId)
            ->whereBetween('transactions.transaction_date', [now()->subDays(30), now()])
            ->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->selectRaw('products.name, products.stock, SUM(transaction_items.quantity) as total_sold')
            ->groupBy('products.id', 'products.name', 'products.stock')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function stockMovementTrend()
    {
        $storeId = $this->getCurrentTenant()->id;
        $data = [];
        $labels = [];

        // Get last 7 days of stock movement (sales)
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d/m');

            $dailySold = Transaction::where('transactions.status', 'completed')
                ->where('transactions.tenant_id', $storeId)
                ->whereDate('transactions.transaction_date', $date)
                ->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id')
                ->sum('transaction_items.quantity');

            $data[] = $dailySold;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->filterCategory = '';
        $this->filterStockStatus = 'all';
        $this->sortBy = 'name';
        $this->sortDirection = 'asc';
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterCategory()
    {
        $this->resetPage();
    }

    public function updatedFilterStockStatus()
    {
        $this->resetPage();
    }

    public function exportInventory()
    {
        // Export functionality can be implemented here
        session()->flash('message', 'Export feature will be implemented soon.');
    }

    private function getCurrentTenant()
    {
        // Get user's tenant
        $user = auth()->user();
        if ($user->tenant_id) {
            return Tenant::find($user->tenant_id);
        }

        throw new \Exception('No tenant context available');
    }

    public function render()
    {
        return view('livewire.tenant.inventory-reports');
    }
}
