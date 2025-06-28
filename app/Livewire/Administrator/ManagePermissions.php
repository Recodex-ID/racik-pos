<?php

namespace App\Livewire\Administrator;

use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;

class ManagePermissions extends Component
{
    use WithPagination;

    public $name = '';

    public $editingPermissionId = null;

    public $showModal = false;

    public $search = '';

    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
        ];

        if ($this->editingPermissionId) {
            $rules['name'] .= '|unique:permissions,name,'.$this->editingPermissionId;
        } else {
            $rules['name'] .= '|unique:permissions,name';
        }

        return $rules;
    }

    #[Computed]
    public function permissions()
    {
        return Permission::when($this->search, function ($query) {
            $query->where('name', 'like', '%'.$this->search.'%');
        })
            ->latest()
            ->paginate(10);
    }

    public function create()
    {
        $this->reset(['name', 'editingPermissionId']);
        $this->showModal = true;
    }

    public function edit($permissionId)
    {
        $permission = Permission::findOrFail($permissionId);
        $this->editingPermissionId = $permission->id;
        $this->name = $permission->name;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->editingPermissionId) {
            $permission = Permission::findOrFail($this->editingPermissionId);
            $permission->update(['name' => $this->name]);
        } else {
            Permission::create(['name' => $this->name]);
        }

        $message = $this->editingPermissionId ? 'Permission successfully updated!' : 'Permission successfully created!';

        $this->reset(['name', 'editingPermissionId']);
        $this->showModal = false;

        session()->flash('message', $message);
    }

    public function delete($permissionId)
    {
        Permission::findOrFail($permissionId)->delete();
        session()->flash('message', 'Permission successfully deleted!');

        // Close the confirmation modal after delete
        $this->modal("delete-permission-{$permissionId}")->close();
    }

    public function resetForm()
    {
        $this->reset(['name', 'editingPermissionId']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.administrator.manage-permissions');
    }
}
