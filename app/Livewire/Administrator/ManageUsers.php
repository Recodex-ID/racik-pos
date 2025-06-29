<?php

namespace App\Livewire\Administrator;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class ManageUsers extends Component
{
    use WithPagination;

    public $name = '';

    public $username = '';

    public $email = '';

    public $password = '';

    public $selectedRoles = [];

    public $tenant_id = '';

    public $editingUserId = null;

    public $showModal = false;

    public $search = '';

    public $showSuperAdmins = false;

    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'selectedRoles' => 'array',
            'tenant_id' => 'nullable|exists:tenants,id',
        ];

        if ($this->editingUserId) {
            $rules['username'] .= '|unique:users,username,'.$this->editingUserId;
            $rules['email'] .= '|unique:users,email,'.$this->editingUserId;
            $rules['password'] = 'nullable|string|min:8';
        } else {
            $rules['username'] .= '|unique:users,username';
            $rules['email'] .= '|unique:users,email';
            $rules['password'] = 'required|string|min:8';
        }

        return $rules;
    }

    #[Computed]
    public function users()
    {
        return User::with(['roles', 'tenant'])
            ->whereHas('roles', function ($query) {
                $query->where('name', '!=', 'Super Admin');
            })
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('username', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            })
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function superAdmins()
    {
        return User::with(['roles', 'tenant'])
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Super Admin');
            })
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('username', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            })
            ->latest()
            ->get();
    }

    #[Computed]
    public function roles()
    {
        return Role::all();
    }

    #[Computed]
    public function tenants()
    {
        return Tenant::active()->orderBy('name')->get();
    }

    public function create()
    {
        $this->reset(['name', 'username', 'email', 'password', 'selectedRoles', 'tenant_id', 'editingUserId']);
        $this->showModal = true;
    }

    public function edit($userId)
    {
        $user = User::with('roles')->findOrFail($userId);
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->username = $user->username;
        $this->email = $user->email;
        $this->password = '';
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
        $this->tenant_id = $user->tenant_id;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $userData = [
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'tenant_id' => $this->tenant_id ?: null,
        ];

        if ($this->password) {
            $userData['password'] = Hash::make($this->password);
        }

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);
            $user->update($userData);
        } else {
            $user = User::create($userData);
        }

        $user->syncRoles($this->selectedRoles);

        $message = $this->editingUserId ? 'User berhasil diperbarui!' : 'User berhasil dibuat!';

        $this->reset(['name', 'username', 'email', 'password', 'selectedRoles', 'tenant_id', 'editingUserId']);
        $this->showModal = false;

        session()->flash('message', $message);
    }

    public function delete($userId)
    {
        User::findOrFail($userId)->delete();
        session()->flash('message', 'User berhasil dihapus!');

        // Close the confirmation modal after delete
        $this->modal("delete-user-{$userId}")->close();
    }

    public function toggleSuperAdminView()
    {
        $this->showSuperAdmins = ! $this->showSuperAdmins;
        $this->resetPage(); // Reset pagination when switching views
    }

    public function resetForm()
    {
        $this->reset(['name', 'username', 'email', 'password', 'selectedRoles', 'tenant_id', 'editingUserId']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.administrator.manage-users');
    }
}
