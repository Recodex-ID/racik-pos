<?php

namespace App\Livewire\Administrator;

use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class ManageRoles extends Component
{
    use WithPagination;

    public $name = '';

    public $editingRoleId = null;

    public $showModal = false;

    public $search = '';

    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
        ];

        if ($this->editingRoleId) {
            $rules['name'] .= '|unique:roles,name,'.$this->editingRoleId;
        } else {
            $rules['name'] .= '|unique:roles,name';
        }

        return $rules;
    }

    #[Computed]
    public function roles()
    {
        return Role::when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->latest()
            ->paginate(10);
    }

    public function create()
    {
        $this->reset(['name', 'editingRoleId']);
        $this->showModal = true;
    }

    public function edit($roleId)
    {
        $role = Role::findOrFail($roleId);
        $this->editingRoleId = $role->id;
        $this->name = $role->name;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->editingRoleId) {
            $role = Role::findOrFail($this->editingRoleId);
            $role->update(['name' => $this->name]);
        } else {
            $role = Role::create(['name' => $this->name]);
        }


        $message = $this->editingRoleId ? 'Role successfully updated!' : 'Role successfully created!';

        $this->reset(['name', 'editingRoleId']);
        $this->showModal = false;

        session()->flash('message', $message);
    }

    public function delete($roleId)
    {
        Role::findOrFail($roleId)->delete();
        session()->flash('message', 'Role successfully deleted!');

        // Close the confirmation modal after delete
        $this->modal("delete-role-{$roleId}")->close();
    }

    public function resetForm()
    {
        $this->reset(['name', 'editingRoleId']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.administrator.manage-roles');
    }
}
