<?php

namespace Tests\Feature\Administrator;

use App\Livewire\Administrator\ManagePermissions;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PermissionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user for testing
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_render_permission_management_component()
    {
        Livewire::test(ManagePermissions::class)
            ->assertStatus(200);
    }

    public function test_can_create_new_permission()
    {
        Livewire::test(ManagePermissions::class)
            ->set('name', 'test-permission')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('name', '')
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('permissions', [
            'name' => 'test-permission',
        ]);
    }

    public function test_can_edit_existing_permission()
    {
        $permission = Permission::create(['name' => 'original-permission']);

        Livewire::test(ManagePermissions::class)
            ->call('edit', $permission->id)
            ->assertSet('editingPermissionId', $permission->id)
            ->assertSet('name', 'original-permission')
            ->assertSet('showModal', true)
            ->set('name', 'updated-permission')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('permissions', [
            'id' => $permission->id,
            'name' => 'updated-permission',
        ]);
    }

    public function test_can_delete_permission()
    {
        $permission = Permission::create(['name' => 'delete-permission']);

        Livewire::test(ManagePermissions::class)
            ->call('delete', $permission->id);

        $this->assertDatabaseMissing('permissions', [
            'id' => $permission->id,
        ]);
    }

    public function test_validates_required_name_field()
    {
        Livewire::test(ManagePermissions::class)
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    public function test_validates_unique_permission_name()
    {
        Permission::create(['name' => 'existing-permission']);

        Livewire::test(ManagePermissions::class)
            ->set('name', 'existing-permission')
            ->call('save')
            ->assertHasErrors(['name' => 'unique']);
    }

    public function test_can_search_permissions()
    {
        Permission::create(['name' => 'first-permission']);
        Permission::create(['name' => 'second-permission']);
        Permission::create(['name' => 'another-name']);

        $component = Livewire::test(ManagePermissions::class)
            ->set('search', 'permission');

        $permissions = $component->get('permissions');
        $this->assertEquals(2, $permissions->count());
    }

    public function test_can_reset_form()
    {
        Livewire::test(ManagePermissions::class)
            ->set('name', 'test-permission')
            ->set('editingPermissionId', 1)
            ->call('resetForm')
            ->assertSet('name', '')
            ->assertSet('editingPermissionId', null);
    }

    public function test_can_open_create_modal()
    {
        Livewire::test(ManagePermissions::class)
            ->call('create')
            ->assertSet('name', '')
            ->assertSet('editingPermissionId', null)
            ->assertSet('showModal', true);
    }

    public function test_permissions_are_paginated()
    {
        // Create more than 10 permissions
        for ($i = 1; $i <= 15; $i++) {
            Permission::create(['name' => "permission-{$i}"]);
        }

        $component = Livewire::test(ManagePermissions::class);
        $permissions = $component->get('permissions');

        $this->assertEquals(10, $permissions->perPage());
        $this->assertEquals(15, $permissions->total());
    }
}
