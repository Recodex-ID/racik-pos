<?php

namespace App\Livewire\Administrator;

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

    public $editingUserId = null;

    public $showModal = false;

    public $search = '';

    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'selectedRoles' => 'array',
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
        return User::with('roles')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('username', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            })
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function roles()
    {
        return Role::all();
    }

    public function create()
    {
        $this->reset(['name', 'username', 'email', 'password', 'selectedRoles', 'editingUserId']);
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
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $userData = [
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
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

        $message = $this->editingUserId ? 'User successfully updated!' : 'User successfully created!';

        $this->reset(['name', 'username', 'email', 'password', 'selectedRoles', 'editingUserId']);
        $this->showModal = false;

        session()->flash('message', $message);
    }

    public function delete($userId)
    {
        User::findOrFail($userId)->delete();
        session()->flash('message', 'User successfully deleted!');

        // Close the confirmation modal after delete
        $this->modal("delete-user-{$userId}")->close();
    }

    public function resetForm()
    {
        $this->reset(['name', 'username', 'email', 'password', 'selectedRoles', 'editingUserId']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.administrator.manage-users');
    }
}
