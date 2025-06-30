<?php

namespace App\Livewire\Tenant;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ManageExpenses extends Component
{
    use WithFileUploads, WithPagination;

    public $title = '';

    public $description = '';

    public $amount = '';

    public $category = '';

    public $expense_date = '';

    public $receipt_file = null;

    public $editingExpenseId = null;

    public $showModal = false;

    public $search = '';

    public $selectedCategory = '';

    public $isManualCategory = false;

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string|max:255',
            'expense_date' => 'required|date',
            'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }

    #[Computed]
    public function expenses()
    {
        return Expense::with('user')
            ->whereHas('user', function ($query) {
                $query->where('tenant_id', auth()->user()->tenant_id);
            })
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhere('category', 'like', '%'.$this->search.'%');
            })
            ->when($this->selectedCategory, function ($query) {
                $query->where('category', $this->selectedCategory);
            })
            ->orderBy('expense_date', 'desc')
            ->paginate(10);
    }

    #[Computed]
    public function tenantUsers()
    {
        return User::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function categories()
    {
        return ['Operasional', 'Inventaris', 'Gaji', 'Utilitas', 'Pemasaran', 'Transportasi', 'Konsumsi'];
    }

    #[Computed]
    public function totalExpenses()
    {
        return Expense::whereHas('user', function ($query) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        })
            ->when($this->selectedCategory, function ($query) {
                $query->where('category', $this->selectedCategory);
            })
            ->sum('amount');
    }

    public function create()
    {
        $this->reset(['title', 'description', 'amount', 'category', 'expense_date', 'receipt_file', 'editingExpenseId', 'isManualCategory']);
        $this->expense_date = now()->format('Y-m-d');
        $this->showModal = true;
    }

    public function edit($expenseId)
    {
        $expense = Expense::findOrFail($expenseId);
        $this->editingExpenseId = $expense->id;
        $this->title = $expense->title;
        $this->description = $expense->description;
        $this->amount = $expense->amount;
        $this->category = $expense->category;
        $this->expense_date = $expense->expense_date->format('Y-m-d');
        $this->receipt_file = null;
        $this->isManualCategory = !in_array($expense->category, $this->categories);
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $expenseData = [
            'title' => $this->title,
            'description' => $this->description,
            'amount' => $this->amount,
            'category' => $this->category,
            'expense_date' => $this->expense_date,
            'user_id' => auth()->user()->id,
        ];

        if ($this->receipt_file) {
            $filePath = $this->receipt_file->store('receipts', 'public');
            $expenseData['receipt_file'] = $filePath;
        }

        if ($this->editingExpenseId) {
            $expense = Expense::findOrFail($this->editingExpenseId);

            if ($this->receipt_file && $expense->receipt_file) {
                Storage::disk('public')->delete($expense->receipt_file);
            }

            $expense->update($expenseData);
            $message = 'Pengeluaran berhasil diperbarui!';
        } else {
            Expense::create($expenseData);
            $message = 'Pengeluaran berhasil ditambahkan!';
        }

        $this->reset(['title', 'description', 'amount', 'category', 'expense_date', 'receipt_file', 'editingExpenseId', 'isManualCategory']);
        $this->showModal = false;

        session()->flash('message', $message);
    }

    public function delete($expenseId)
    {
        $expense = Expense::findOrFail($expenseId);

        if ($expense->receipt_file) {
            Storage::disk('public')->delete($expense->receipt_file);
        }

        $expense->delete();
        session()->flash('message', 'Pengeluaran berhasil dihapus!');

        $this->modal("delete-expense-{$expenseId}")->close();
    }

    public function resetForm()
    {
        $this->reset(['title', 'description', 'amount', 'category', 'expense_date', 'receipt_file', 'editingExpenseId', 'isManualCategory']);
        $this->resetValidation();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'selectedCategory']);
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.tenant.manage-expenses');
    }
}
