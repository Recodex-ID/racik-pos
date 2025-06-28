<?php

namespace Tests\Feature\Administrator;

use App\Livewire\Administrator\ManageRoles;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user for testing
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_render_role_management_component()
    {
        Livewire::test(ManageRoles::class)
            ->assertStatus(200);
    }

    public function test_can_create_new_role()
    {
        $permission1 = Permission::create(['name' => 'test-permission-1']);
        $permission2 = Permission::create(['name' => 'test-permission-2']);

        Livewire::test(ManageRoles::class)
            ->set('name', 'test-role')
            ->set('selectedPermissions', ['test-permission-1', 'test-permission-2'])
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('name', '')
            ->assertSet('selectedPermissions', [])
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('roles', [
            'name' => 'test-role',
        ]);

        $role = Role::where('name', 'test-role')->first();
        $this->assertTrue($role->hasPermissionTo('test-permission-1'));
        $this->assertTrue($role->hasPermissionTo('test-permission-2'));
    }

    public function test_can_edit_existing_role()
    {
        $permission1 = Permission::create(['name' => 'permission-1']);
        $permission2 = Permission::create(['name' => 'permission-2']);
        $permission3 = Permission::create(['name' => 'permission-3']);

        $role = Role::create(['name' => 'original-role']);
        $role->givePermissionTo(['permission-1', 'permission-2']);

        Livewire::test(ManageRoles::class)
            ->call('edit', $role->id)
            ->assertSet('editingRoleId', $role->id)
            ->assertSet('name', 'original-role')
            ->assertSet('selectedPermissions', ['permission-1', 'permission-2'])
            ->assertSet('showModal', true)
            ->set('name', 'updated-role')
            ->set('selectedPermissions', ['permission-2', 'permission-3'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'updated-role',
        ]);

        $role->refresh();
        $this->assertFalse($role->hasPermissionTo('permission-1'));
        $this->assertTrue($role->hasPermissionTo('permission-2'));
        $this->assertTrue($role->hasPermissionTo('permission-3'));
    }

    public function test_can_delete_role()
    {
        $role = Role::create(['name' => 'delete-role']);

        Livewire::test(ManageRoles::class)
            ->call('delete', $role->id);

        $this->assertDatabaseMissing('roles', [
            'id' => $role->id,
        ]);
    }

    public function test_validates_required_name_field()
    {
        Livewire::test(ManageRoles::class)
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    public function test_validates_unique_role_name()
    {
        Role::create(['name' => 'existing-role']);

        Livewire::test(ManageRoles::class)
            ->set('name', 'existing-role')
            ->call('save')
            ->assertHasErrors(['name' => 'unique']);
    }

    public function test_can_search_roles()
    {
        Role::create(['name' => 'admin-role']);
        Role::create(['name' => 'user-role']);
        Role::create(['name' => 'manager-position']);

        $component = Livewire::test(ManageRoles::class)
            ->set('search', 'role');

        $roles = $component->get('roles');
        $this->assertEquals(2, $roles->count());
    }

    public function test_can_reset_form()
    {
        Livewire::test(ManageRoles::class)
            ->set('name', 'test-role')
            ->set('selectedPermissions', ['test-permission'])
            ->set('editingRoleId', 1)
            ->call('resetForm')
            ->assertSet('name', '')
            ->assertSet('selectedPermissions', [])
            ->assertSet('editingRoleId', null);
    }

    public function test_can_open_create_modal()
    {
        Livewire::test(ManageRoles::class)
            ->call('create')
            ->assertSet('name', '')
            ->assertSet('selectedPermissions', [])
            ->assertSet('editingRoleId', null)
            ->assertSet('showModal', true);
    }

    public function test_roles_are_paginated()
    {
        // Create more than 10 roles
        for ($i = 1; $i <= 15; $i++) {
            Role::create(['name' => "role-{$i}"]);
        }

        $component = Livewire::test(ManageRoles::class);
        $roles = $component->get('roles');

        $this->assertEquals(10, $roles->perPage());
        $this->assertEquals(15, $roles->total());
    }

    public function test_can_create_role_without_permissions()
    {
        Livewire::test(ManageRoles::class)
            ->set('name', 'no-permission-role')
            ->set('selectedPermissions', [])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('roles', [
            'name' => 'no-permission-role',
        ]);

        $role = Role::where('name', 'no-permission-role')->first();
        $this->assertEquals(0, $role->permissions->count());
    }

    public function test_roles_load_with_permissions()
    {
        $permission = Permission::create(['name' => 'test-permission']);
        $role = Role::create(['name' => 'test-role']);
        $role->givePermissionTo($permission);

        $component = Livewire::test(ManageRoles::class);
        $roles = $component->get('roles');

        $firstRole = $roles->first();
        $this->assertTrue($firstRole->relationLoaded('permissions'));
    }
}
