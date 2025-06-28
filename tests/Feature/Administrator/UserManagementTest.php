<?php

namespace Tests\Feature\Administrator;

use App\Livewire\Administrator\ManageUsers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user for testing
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_render_user_management_component()
    {
        Livewire::test(ManageUsers::class)
            ->assertStatus(200);
    }

    public function test_can_create_new_user()
    {
        $role1 = Role::create(['name' => 'admin']);
        $role2 = Role::create(['name' => 'user']);

        Livewire::test(ManageUsers::class)
            ->set('name', 'Test User')
            ->set('username', 'testuser')
            ->set('email', 'test@example.com')
            ->set('password', 'password123')
            ->set('selectedRoles', ['admin', 'user'])
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('name', '')
            ->assertSet('username', '')
            ->assertSet('email', '')
            ->assertSet('password', '')
            ->assertSet('selectedRoles', [])
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('user'));
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_can_edit_existing_user()
    {
        $role1 = Role::create(['name' => 'admin']);
        $role2 = Role::create(['name' => 'user']);
        $role3 = Role::create(['name' => 'manager']);

        $user = User::factory()->create([
            'name' => 'Original Name',
            'username' => 'originaluser',
            'email' => 'original@example.com',
        ]);
        $user->assignRole(['admin', 'user']);

        Livewire::test(ManageUsers::class)
            ->call('edit', $user->id)
            ->assertSet('editingUserId', $user->id)
            ->assertSet('name', 'Original Name')
            ->assertSet('username', 'originaluser')
            ->assertSet('email', 'original@example.com')
            ->assertSet('password', '')
            ->assertSet('selectedRoles', ['admin', 'user'])
            ->assertSet('showModal', true)
            ->set('name', 'Updated Name')
            ->set('username', 'updateduser')
            ->set('email', 'updated@example.com')
            ->set('selectedRoles', ['user', 'manager'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'username' => 'updateduser',
            'email' => 'updated@example.com',
        ]);

        $user->refresh();
        $this->assertFalse($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('user'));
        $this->assertTrue($user->hasRole('manager'));
    }

    public function test_can_edit_user_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        Livewire::test(ManageUsers::class)
            ->call('edit', $user->id)
            ->set('password', 'newpassword123')
            ->call('save')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_can_edit_user_without_changing_password()
    {
        $originalPassword = Hash::make('originalpassword');
        $user = User::factory()->create([
            'password' => $originalPassword,
        ]);

        Livewire::test(ManageUsers::class)
            ->call('edit', $user->id)
            ->set('name', 'Updated Name')
            ->set('password', '')
            ->call('save')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertEquals($originalPassword, $user->password);
    }

    public function test_can_delete_user()
    {
        $user = User::factory()->create();

        Livewire::test(ManageUsers::class)
            ->call('delete', $user->id);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_validates_required_fields()
    {
        Livewire::test(ManageUsers::class)
            ->set('name', '')
            ->set('username', '')
            ->set('email', '')
            ->set('password', '')
            ->call('save')
            ->assertHasErrors([
                'name' => 'required',
                'username' => 'required',
                'email' => 'required',
                'password' => 'required',
            ]);
    }

    public function test_validates_unique_username_and_email()
    {
        User::factory()->create([
            'username' => 'existinguser',
            'email' => 'existing@example.com',
        ]);

        Livewire::test(ManageUsers::class)
            ->set('name', 'Test User')
            ->set('username', 'existinguser')
            ->set('email', 'existing@example.com')
            ->set('password', 'password123')
            ->call('save')
            ->assertHasErrors([
                'username' => 'unique',
                'email' => 'unique',
            ]);
    }

    public function test_validates_email_format()
    {
        Livewire::test(ManageUsers::class)
            ->set('name', 'Test User')
            ->set('username', 'testuser')
            ->set('email', 'invalid-email')
            ->set('password', 'password123')
            ->call('save')
            ->assertHasErrors(['email' => 'email']);
    }

    public function test_validates_password_minimum_length()
    {
        Livewire::test(ManageUsers::class)
            ->set('name', 'Test User')
            ->set('username', 'testuser')
            ->set('email', 'test@example.com')
            ->set('password', '123')
            ->call('save')
            ->assertHasErrors(['password' => 'min']);
    }

    public function test_can_search_users()
    {
        User::factory()->create(['name' => 'John Doe', 'username' => 'johndoe', 'email' => 'john@example.com']);
        User::factory()->create(['name' => 'Jane Smith', 'username' => 'janesmith', 'email' => 'jane@example.com']);
        User::factory()->create(['name' => 'Bob Wilson', 'username' => 'bobwilson', 'email' => 'bob@example.com']);

        $component = Livewire::test(ManageUsers::class)
            ->set('search', 'john');

        $users = $component->get('users');
        $this->assertEquals(1, $users->count());
        $this->assertEquals('John Doe', $users->first()->name);
    }

    public function test_can_search_users_by_username()
    {
        User::factory()->create(['name' => 'John Doe', 'username' => 'johndoe']);
        User::factory()->create(['name' => 'Jane Smith', 'username' => 'janesmith']);

        $component = Livewire::test(ManageUsers::class)
            ->set('search', 'jane');

        $users = $component->get('users');
        $this->assertEquals(1, $users->count());
        $this->assertEquals('janesmith', $users->first()->username);
    }

    public function test_can_search_users_by_email()
    {
        User::factory()->create(['email' => 'test@company.com']);
        User::factory()->create(['email' => 'admin@company.com']);
        User::factory()->create(['email' => 'user@different.com']);

        $component = Livewire::test(ManageUsers::class)
            ->set('search', 'company');

        $users = $component->get('users');
        $this->assertEquals(2, $users->count());
    }

    public function test_can_reset_form()
    {
        Livewire::test(ManageUsers::class)
            ->set('name', 'Test User')
            ->set('username', 'testuser')
            ->set('email', 'test@example.com')
            ->set('password', 'password123')
            ->set('selectedRoles', ['admin'])
            ->set('editingUserId', 1)
            ->call('resetForm')
            ->assertSet('name', '')
            ->assertSet('username', '')
            ->assertSet('email', '')
            ->assertSet('password', '')
            ->assertSet('selectedRoles', [])
            ->assertSet('editingUserId', null);
    }

    public function test_can_open_create_modal()
    {
        Livewire::test(ManageUsers::class)
            ->call('create')
            ->assertSet('name', '')
            ->assertSet('username', '')
            ->assertSet('email', '')
            ->assertSet('password', '')
            ->assertSet('selectedRoles', [])
            ->assertSet('editingUserId', null)
            ->assertSet('showModal', true);
    }

    public function test_users_are_paginated()
    {
        // Create more than 10 users
        User::factory()->count(15)->create();

        $component = Livewire::test(ManageUsers::class);
        $users = $component->get('users');

        $this->assertEquals(10, $users->perPage());
        $this->assertEquals(16, $users->total()); // 15 + 1 from setUp
    }

    public function test_can_create_user_without_roles()
    {
        Livewire::test(ManageUsers::class)
            ->set('name', 'No Role User')
            ->set('username', 'noroleuser')
            ->set('email', 'norole@example.com')
            ->set('password', 'password123')
            ->set('selectedRoles', [])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'name' => 'No Role User',
            'username' => 'noroleuser',
            'email' => 'norole@example.com',
        ]);

        $user = User::where('email', 'norole@example.com')->first();
        $this->assertEquals(0, $user->roles->count());
    }

    public function test_users_load_with_roles()
    {
        $role = Role::create(['name' => 'test-role']);
        $user = User::factory()->create();
        $user->assignRole($role);

        $component = Livewire::test(ManageUsers::class);
        $users = $component->get('users');

        $testUser = $users->where('id', $user->id)->first();
        $this->assertTrue($testUser->relationLoaded('roles'));
    }
}
