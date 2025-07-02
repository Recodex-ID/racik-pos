# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Point of Sale (POS) system built with Laravel, Livewire, and Flux UI. The application features a multi-tenant architecture with role-based access control.

### Key Architecture Components

- **Multi-tenant Architecture**: Each tenant has isolated data (categories, products, customers, transactions)
- **Role-based Access Control**: Uses Spatie Permission package with roles: Super Admin, Admin, Cashier
- **Livewire Components**: Full-page Livewire components for all UI functionality
- **Flux UI**: Modern UI components for consistent design
- **Scoped Models**: Models are scoped to tenants for data isolation

### Tech Stack

- Laravel 12.x
- Livewire 3.x with Volt
- Flux UI 2.x
- Spatie Laravel Permission
- Pest for testing
- Vite for asset compilation
- Chart.js for reporting

## Development Commands

### Development Server
```bash
composer run dev
```
This starts the complete development environment with:
- Laravel dev server
- Queue worker
- Log viewer (Pail)
- Vite asset watcher

Individual commands:
```bash
php artisan serve        # Development server
php artisan queue:listen # Queue worker
php artisan pail         # Log viewer
npm run dev             # Vite asset watcher
```

### Testing
```bash
composer run test       # Run all tests
php artisan test        # Direct artisan command
php artisan test --filter UserManagementTest  # Run specific test
```

### Building Assets
```bash
npm run build          # Production build
npm run dev           # Development build with watching
```

### Database
```bash
php artisan migrate           # Run migrations
php artisan db:seed          # Seed database
php artisan migrate:fresh --seed  # Fresh migration with seeding
```

## Project Structure

### Models and Relationships

- **Tenant**: Central model for multi-tenancy
  - Has many: Users, Categories, Products, Customers, Transactions
- **User**: Authentication with role-based permissions
  - Belongs to: Tenant
  - Has many: Transactions
- **Product**: Inventory items
  - Belongs to: Tenant, Category
- **Transaction**: Sales records
  - Belongs to: Tenant, User, Customer
  - Has many: TransactionItems
- **Category/Customer/Expense**: Tenant-scoped models

### Role Hierarchy

1. **Super Admin**: System-wide access, manages tenants and users
2. **Admin**: Tenant-level access, manages products, categories, reports
3. **Cashier**: POS access only, can process transactions

### Route Structure

- `/dashboard` - Main dashboard (all authenticated users)
- `/administrator/*` - Super Admin only routes
- `/tenant/*` - Admin only routes (tenant management)
- `/reports/*` - Admin only routes (reporting)
- `/pos/*` - Admin/Cashier routes (POS system)
- `/settings/*` - User settings (all authenticated users)

### Livewire Components Organization

- **Administrator/**: Super admin components (ManageUsers, ManageRoles, ManageTenants)
- **Tenant/**: Admin components (ManageProducts, ManageCategories, etc.)
- **Pos/**: POS components (Cashier)
- **Reports/**: Reporting components (MonthlyTransaction, MonthlyExpense)

## Multi-tenant Data Isolation

All tenant-scoped models must include tenant_id and use scoping:

```php
// In queries
$products = Product::byTenant(auth()->user()->tenant_id)->get();

// In model scopes
public function scopeByTenant(Builder $query, $tenantId): Builder
{
    return $query->where('tenant_id', $tenantId);
}
```

## Testing Strategy

- Uses Pest testing framework
- Feature tests for Livewire components
- Role-based access testing
- Multi-tenant data isolation testing
- Uses in-memory SQLite for testing

## Important Development Notes

- Always check user permissions before allowing access to tenant data
- Use `auth()->user()->tenant_id` for tenant scoping in queries
- Follow the existing multi-tenant patterns when creating new features
- All POS functionality should work for both Admin and Cashier roles
- Use Livewire Form Objects for complex forms
- Implement proper loading states for better UX

## Laravel best practices

### **Single responsibility principle**

A class should have only one responsibility.

Bad:

```php
public function update(Request $request): string
{
    $validated = $request->validate([
        'title' => 'required|max:255',
        'events' => 'required|array:date,type'
    ]);

    foreach ($request->events as $event) {
        $date = $this->carbon->parse($event['date'])->toString();

        $this->logger->log('Update event ' . $date . ' :: ' . $);
    }

    $this->event->updateGeneralEvent($request->validated());

    return back();
}
```

Good:

```php
public function update(UpdateRequest $request): string
{
    $this->logService->logEvents($request->events);

    $this->event->updateGeneralEvent($request->validated());

    return back();
}
```


### **Methods should do just one thing**

A function should do just one thing and do it well.

Bad:

```php
public function getFullNameAttribute(): string
{
    if (auth()->user() && auth()->user()->hasRole('client') && auth()->user()->isVerified()) {
        return 'Mr. ' . $this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name;
    } else {
        return $this->first_name[0] . '. ' . $this->last_name;
    }
}
```

Good:

```php
public function getFullNameAttribute(): string
{
    return $this->isVerifiedClient() ? $this->getFullNameLong() : $this->getFullNameShort();
}

public function isVerifiedClient(): bool
{
    return auth()->user() && auth()->user()->hasRole('client') && auth()->user()->isVerified();
}

public function getFullNameLong(): string
{
    return 'Mr. ' . $this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name;
}

public function getFullNameShort(): string
{
    return $this->first_name[0] . '. ' . $this->last_name;
}
```


### **Fat models, skinny controllers**

Put all DB related logic into Eloquent models.

Bad:

```php
public function index()
{
    $clients = Client::verified()
        ->with(['orders' => function ($q) {
            $q->where('created_at', '>', Carbon::today()->subWeek());
        }])
        ->get();

    return view('index', ['clients' => $clients]);
}
```

Good:

```php
public function index()
{
    return view('index', ['clients' => $this->client->getWithNewOrders()]);
}

class Client extends Model
{
    public function getWithNewOrders(): Collection
    {
        return $this->verified()
            ->with(['orders' => function ($q) {
                $q->where('created_at', '>', Carbon::today()->subWeek());
            }])
            ->get();
    }
}
```


### **Validation**

Move validation from controllers to Request classes.

Bad:

```php
public function store(Request $request)
{
    $request->validate([
        'title' => 'required|unique:posts|max:255',
        'body' => 'required',
        'publish_at' => 'nullable|date',
    ]);

    ...
}
```

Good:

```php
public function store(PostRequest $request)
{
    ...
}

class PostRequest extends Request
{
    public function rules(): array
    {
        return [
            'title' => 'required|unique:posts|max:255',
            'body' => 'required',
            'publish_at' => 'nullable|date',
        ];
    }
}
```


### **Business logic should be in service class**

A controller must have only one responsibility, so move business logic from controllers to service classes.

Bad:

```php
public function store(Request $request)
{
    if ($request->hasFile('image')) {
        $request->file('image')->move(public_path('images') . 'temp');
    }
    
    ...
}
```

Good:

```php
public function store(Request $request)
{
    $this->articleService->handleUploadedImage($request->file('image'));

    ...
}

class ArticleService
{
    public function handleUploadedImage($image): void
    {
        if (!is_null($image)) {
            $image->move(public_path('images') . 'temp');
        }
    }
}
```


### **Don't repeat yourself (DRY)**

Reuse code when you can. SRP is helping you to avoid duplication. Also, reuse Blade templates, use Eloquent scopes etc.

Bad:

```php
public function getActive()
{
    return $this->where('verified', 1)->whereNotNull('deleted_at')->get();
}

public function getArticles()
{
    return $this->whereHas('user', function ($q) {
            $q->where('verified', 1)->whereNotNull('deleted_at');
        })->get();
}
```

Good:

```php
public function scopeActive($q)
{
    return $q->where('verified', true)->whereNotNull('deleted_at');
}

public function getActive(): Collection
{
    return $this->active()->get();
}

public function getArticles(): Collection
{
    return $this->whereHas('user', function ($q) {
            $q->active();
        })->get();
}
```


### **Prefer to use Eloquent over using Query Builder and raw SQL queries. Prefer collections over arrays**

Eloquent allows you to write readable and maintainable code. Also, Eloquent has great built-in tools like soft deletes, events, scopes etc. You may want to check out [Eloquent to SQL reference](https://github.com/alexeymezenin/eloquent-sql-reference)

Bad:

```sql
SELECT *
FROM `articles`
WHERE EXISTS (SELECT *
              FROM `users`
              WHERE `articles`.`user_id` = `users`.`id`
              AND EXISTS (SELECT *
                          FROM `profiles`
                          WHERE `profiles`.`user_id` = `users`.`id`) 
              AND `users`.`deleted_at` IS NULL)
AND `verified` = '1'
AND `active` = '1'
ORDER BY `created_at` DESC
```

Good:

```php
Article::has('user.profile')->verified()->latest()->get();
```


### **Mass assignment**

Bad:

```php
$article = new Article;
$article->title = $request->title;
$article->content = $request->content;
$article->verified = $request->verified;

// Add category to article
$article->category_id = $category->id;
$article->save();
```

Good:

```php
$category->article()->create($request->validated());
```


### **Do not execute queries in Blade templates and use eager loading (N + 1 problem)**

Bad (for 100 users, 101 DB queries will be executed):

```blade
@foreach (User::all() as $user)
    {{ $user->profile->name }}
@endforeach
```

Good (for 100 users, 2 DB queries will be executed):

```php
$users = User::with('profile')->get();

@foreach ($users as $user)
    {{ $user->profile->name }}
@endforeach
```


### **Chunk data for data-heavy tasks**

Bad:

```php
$users = $this->get();

foreach ($users as $user) {
    ...
}
```

Good:

```php
$this->chunk(500, function ($users) {
    foreach ($users as $user) {
        ...
    }
});
```


### **Prefer descriptive method and variable names over comments**

Bad:

```php
// Determine if there are any joins
if (count((array) $builder->getQuery()->joins) > 0)
```

Good:

```php
if ($this->hasJoins())
```


### **Do not put JS and CSS in Blade templates and do not put any HTML in PHP classes**

Bad:

```javascript
let article = `{{ json_encode($article) }}`;
```

Better:

```php
<input id="article" type="hidden" value='@json($article)'>

Or

<button class="js-fav-article" data-article='@json($article)'>{{ $article->name }}<button>
```

In a Javascript file:

```javascript
let article = $('#article').val();
```

The best way is to use specialized PHP to JS package to transfer the data.


### **Use standard Laravel tools accepted by community**

Prefer to use built-in Laravel functionality and community packages instead of using 3rd party packages and tools. Any developer who will work with your app in the future will need to learn new tools. Also, chances to get help from the Laravel community are significantly lower when you're using a 3rd party package or tool. Do not make your client pay for that.

| Task                      | Standard tools                         | 3rd party tools                                         |
|---------------------------|----------------------------------------|---------------------------------------------------------|
| Authorization             | Policies                               | Entrust, Sentinel and other packages                    |
| Compiling assets          | Laravel Mix, Vite                      | Grunt, Gulp, 3rd party packages                         |
| Development Environment   | Laravel Sail, Homestead                | Docker                                                  |
| Deployment                | Laravel Forge                          | Deployer and other solutions                            |
| Unit testing              | PHPUnit, Mockery                       | Phpspec, Pest                                           |
| Browser testing           | Laravel Dusk                           | Codeception                                             |
| DB                        | Eloquent                               | SQL, Doctrine                                           |
| Templates                 | Blade                                  | Twig                                                    |
| Working with data         | Laravel collections                    | Arrays                                                  |
| Form validation           | Request classes                        | 3rd party packages, validation in controller            |
| Authentication            | Built-in                               | 3rd party packages, your own solution                   |
| API authentication        | Laravel Passport, Laravel Sanctum      | 3rd party JWT and OAuth packages                        |
| Creating API              | Built-in                               | Dingo API and similar packages                          |
| Working with DB structure | Migrations                             | Working with DB structure directly                      |
| Localization              | Built-in                               | 3rd party packages                                      |
| Realtime user interfaces  | Laravel Echo, Pusher                   | 3rd party packages and working with WebSockets directly |
| Generating testing data   | Seeder classes, Model Factories, Faker | Creating testing data manually                          |
| Task scheduling           | Laravel Task Scheduler                 | Scripts and 3rd party packages                          |
| DB                        | MySQL, PostgreSQL, SQLite, SQL Server  | MongoDB                                                 |

### **Follow Laravel naming conventions**

Follow [PSR standards](https://www.php-fig.org/psr/psr-12/).

Also, follow naming conventions accepted by Laravel community:

| What                                                                  | How                                                                       | Good                                    | Bad                                                             |
|-----------------------------------------------------------------------|---------------------------------------------------------------------------|-----------------------------------------|-----------------------------------------------------------------|
| Controller                                                            | singular                                                                  | ArticleController                       | ~~ArticlesController~~                                          |
| Route                                                                 | plural                                                                    | articles/1                              | ~~article/1~~                                                   |
| Route name                                                            | snake_case with dot notation                                              | users.show_active                       | ~~users.show-active, show-active-users~~                        |
| Model                                                                 | singular                                                                  | User                                    | ~~Users~~                                                       |
| hasOne or belongsTo relationship                                      | singular                                                                  | articleComment                          | ~~articleComments, article_comment~~                            |
| All other relationships                                               | plural                                                                    | articleComments                         | ~~articleComment, article_comments~~                            |
| Table                                                                 | plural                                                                    | article_comments                        | ~~article_comment, articleComments~~                            |
| Pivot table                                                           | singular model names in alphabetical order                                | article_user                            | ~~user_article, articles_users~~                                |
| Table column                                                          | snake_case without model name                                             | meta_title                              | ~~MetaTitle; article_meta_title~~                               |
| Model property                                                        | snake_case                                                                | $model->created_at                      | ~~$model->createdAt~~                                           |
| Foreign key                                                           | singular model name with _id suffix                                       | article_id                              | ~~ArticleId, id_article, articles_id~~                          |
| Primary key                                                           | -                                                                         | id                                      | ~~custom_id~~                                                   |
| Migration                                                             | -                                                                         | 2017_01_01_000000_create_articles_table | ~~2017_01_01_000000_articles~~                                  |
| Method                                                                | camelCase                                                                 | getAll                                  | ~~get_all~~                                                     |
| Method in resource controller                                         | [table](https://laravel.com/docs/master/controllers#resource-controllers) | store                                   | ~~saveArticle~~                                                 |
| Method in test class                                                  | camelCase                                                                 | testGuestCannotSeeArticle               | ~~test_guest_cannot_see_article~~                               |
| Variable                                                              | camelCase                                                                 | $articlesWithAuthor                     | ~~$articles_with_author~~                                       |
| Collection                                                            | descriptive, plural                                                       | $activeUsers = User::active()->get()    | ~~$active, $data~~                                              |
| Object                                                                | descriptive, singular                                                     | $activeUser = User::active()->first()   | ~~$users, $obj~~                                                |
| Config and language files index                                       | snake_case                                                                | articles_enabled                        | ~~ArticlesEnabled; articles-enabled~~                           |
| View                                                                  | kebab-case                                                                | show-filtered.blade.php                 | ~~showFiltered.blade.php, show_filtered.blade.php~~             |
| Config                                                                | snake_case                                                                | google_calendar.php                     | ~~googleCalendar.php, google-calendar.php~~                     |
| Contract (interface)                                                  | adjective or noun                                                         | AuthenticationInterface                 | ~~Authenticatable, IAuthentication~~                            |
| Trait                                                                 | adjective                                                                 | Notifiable                              | ~~NotificationTrait~~                                           |
| Trait [(PSR)](https://www.php-fig.org/bylaws/psr-naming-conventions/) | adjective                                                                 | NotifiableTrait                         | ~~Notification~~                                                |
| Enum                                                                  | singular                                                                  | UserType                                | ~~UserTypes~~, ~~UserTypeEnum~~                                 |
| FormRequest                                                           | singular                                                                  | UpdateUserRequest                       | ~~UpdateUserFormRequest~~, ~~UserFormRequest~~, ~~UserRequest~~ |
| Seeder                                                                | singular                                                                  | UserSeeder                              | ~~UsersSeeder~~                                                 |

### **Convention over configuration**

As long as you follow certain conventions, you do not need to add additional configuration.

Bad:

```php
// Table name 'Customer'
// Primary key 'customer_id'
class Customer extends Model
{
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $table = 'Customer';
    protected $primaryKey = 'customer_id';

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_customer', 'customer_id', 'role_id');
    }
}
```

Good:

```php
// Table name 'customers'
// Primary key 'id'
class Customer extends Model
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
}
```


### **Use shorter and more readable syntax where possible**

Bad:

```php
$request->session()->get('cart');
$request->input('name');
```

Good:

```php
session('cart');
$request->name;
```

More examples:

| Common syntax                                                          | Shorter and more readable syntax                                       |
|------------------------------------------------------------------------|------------------------------------------------------------------------|
| `Session::get('cart')`                                                 | `session('cart')`                                                      |
| `$request->session()->get('cart')`                                     | `session('cart')`                                                      |
| `Session::put('cart', $data)`                                          | `session(['cart' => $data])`                                           |
| `$request->input('name'), Request::get('name')`                        | `$request->name, request('name')`                                      |
| `return Redirect::back()`                                              | `return back()`                                                        |
| `is_null($object->relation) ? null : $object->relation->id`            | `optional($object->relation)->id` (in PHP 8: `$object->relation?->id`) |
| `return view('index')->with('title', $title)->with('client', $client)` | `return view('index', compact('title', 'client'))`                     |
| `$request->has('value') ? $request->value : 'default';`                | `$request->get('value', 'default')`                                    |
| `Carbon::now(), Carbon::today()`                                       | `now(), today()`                                                       |
| `App::make('Class')`                                                   | `app('Class')`                                                         |
| `->where('column', '=', 1)`                                            | `->where('column', 1)`                                                 |
| `->orderBy('created_at', 'desc')`                                      | `->latest()`                                                           |
| `->orderBy('age', 'desc')`                                             | `->latest('age')`                                                      |
| `->orderBy('created_at', 'asc')`                                       | `->oldest()`                                                           |
| `->select('id', 'name')->get()`                                        | `->get(['id', 'name'])`                                                |
| `->first()->name`                                                      | `->value('name')`                                                      |

### **Use IoC / Service container instead of new Class**

new Class syntax creates tight coupling between classes and complicates testing. Use IoC container or facades instead.

Bad:

```php
$user = new User;
$user->create($request->validated());
```

Good:

```php
public function __construct(protected User $user) {}

...

$this->user->create($request->validated());
```


### **Do not get data from the `.env` file directly**

Pass the data to config files instead and then use the `config()` helper function to use the data in an application.

Bad:

```php
$apiKey = env('API_KEY');
```

Good:

```php
// config/api.php
'key' => env('API_KEY'),

// Use the data
$apiKey = config('api.key');
```


### **Store dates in the standard format. Use accessors and mutators to modify date format**

A date as a string is less reliable than an object instance, e.g. a Carbon-instance. It's recommended to pass Carbon objects between classes instead of date strings. Rendering should be done in the display layer (templates):

Bad:

```php
{{ Carbon::createFromFormat('Y-d-m H-i', $object->ordered_at)->toDateString() }}
{{ Carbon::createFromFormat('Y-d-m H-i', $object->ordered_at)->format('m-d') }}
```

Good:

```php
// Model
protected $casts = [
    'ordered_at' => 'datetime',
];

// Blade view
{{ $object->ordered_at->toDateString() }}
{{ $object->ordered_at->format('m-d') }}
```


### **Do not use DocBlocks**

DocBlocks reduce readability. Use a descriptive method name and modern PHP features like return type hints instead.

Bad:

```php
/**
 * The function checks if given string is a valid ASCII string
 *
 * @param string $string String we get from frontend which might contain
 *                       illegal characters. Returns True is the string
 *                       is valid.
 *
 * @return bool
 * @author  John Smith
 *
 * @license GPL
 */

public function checkString($string)
{
}
```

Good:

```php
public function isValidAsciiString(string $string): bool
{
}
```


### **Other good practices**

Avoid using patterns and tools that are alien to Laravel and similar frameworks (i.e. RoR, Django). If you like Symfony (or Spring) approach for building apps, it's a good idea to use these frameworks instead.

Never put any logic in routes files.

Minimize usage of vanilla PHP in Blade templates.

Use in-memory DB for testing.

Do not override standard framework features to avoid problems related to updating the framework version and many other issues.

Use modern PHP syntax where possible, but don't forget about readability.

Avoid using View Composers and similar tools unless you really know what you're doing. In most cases, there is a better way to solve the problem.

## Livewire Best Practices

This repository is a curated list of general recommendations on how to use [Laravel Livewire framework](https://github.com/livewire/livewire) to meet enterprise concerns regarding security, performance, and maintenance of Livewire components.

---
### 🌳 Always set up the root element
Livewire requires a root element (div) in each component. You should always write code inside `<div>Your Code Here</div>`. Omitting this structure will lead to a lot of problems with updating components.

### 🌳 Always set up root element

Bad:
```html
<h1>Component Name</h1>
<div class="content">Content</div>
```

Good:
```html
<div>
    <h1>Component Name</h1>
    <div class="content">Content</div>
</div>
```

---
### ✨ The Golden rule of performant Livewire
```html
Don't pass large objects to Livewire components!
```

Avoid passing objects to the component's public properties if possible. Use primitive types: strings, integers, arrays, etc. That's because Livewire serializes/deserializes your component's payload with each request to the server to share the state between the frontend & backend. If you need to work on objects, you can create them inside a method or computed property, and then return the result of the processing.

What to consider a large object?
- Any instance as large as the Eloquent model is big enough already for Livewire to slow down the component lifecycle, which may lead to poor performance on live updates. For example, if you have a component representing the user profile (email and username), pass these parameters to properties as strings.

Note: if you use [full-page components](https://livewire.laravel.com/docs/components#full-page-components), it's recommended to fetch objects in the full-page component itself, and then pass them downstairs to the nested ones as primitive types.

---
### 🧵 Keep component nesting level at 1
If you had a Livewire component (0) that includes another Livewire component (1), then you shouldn't nest it deeper (2+). Too much nesting can make a headache when dealing with DOM diffing issues.

Also, prefer the usage of Blade components when you use nesting, they will be able to communicate with the parent's Livewire component but won't have the overhead the Livewire adds.

### 🧵 Keep component nesting level at 1

Example:
```html
<div> <!–– level 0 ––>
    <h1>Component</h1>
    <livewire:profile :user="auth()->user()->uuid" /> <!–– level 1 ––>
</div> 
```

---
### 📝 Utilize the form objects
Livewire v3 introduced a new abstraction layer called `Form Objects`. Always use them because that makes your components more maintainable in the long run.

[Docs](https://livewire.laravel.com/docs/forms)

---
### 🕵️ Don't pass sensitive data to the components
Avoid situations that may lead to passing sensitive data to the Livewire components, because it can be easily accessed from the client-side by default. You can hide the properties from the frontend using `#[Locked]` attribute starting from Livewire version 3.

---
### ☔ Prefer to use event listeners over polling
Instead of constantly [polling](https://livewire.laravel.com/docs/polling) the page to refresh your data, you may use [event listeners](https://livewire.laravel.com/docs/events#listening-for-events) to perform the component update only after a specific task was initiated from another component.

### ☔ Prefer to use event listeners over polling

Bad:
```html
<div wire:poll>
    User Content
</div>
```

Good:

*Define the listener in the component using `On` attribute:*
```php
class Dashboard extends Component
{
    #[On('post-created')] 
    public function updatePostList($title)
    {
        // ...
    }
}
```

*Dispatch the event in every other component:*
```php
$this->dispatch('post-created'); 
```

---
### 📦 Use computed properties to access the database
You can use [computed properties](https://livewire.laravel.com/docs/computed-properties) to avoid unnecessary database queries. Computed properties are cached within the component's lifecycle and do not run multiple times in the component class or the blade view. Starting from Livewire v3, the result of computed properties can also be cached in the generic application-level cache (for example Redis), [see](https://livewire.laravel.com/docs/computed-properties#caching-between-requests).

### 📦 Use computed properties to access database

Bad:
```php
public function countries(): Collection
{
    return Country::select('name', 'code')
        ->orderBy('name')
        ->get();
}
```

Good:
```php
#[Computed]
public function countries(): Collection
{
    return Country::select('name', 'code')
        ->orderBy('name')
        ->get();
}
```

---
### 🗺️ Use Route Model Binding to fetch the model
Pass only an ID or UUID to the `mount` method, then map the model attributes to component properties. Remember: don't assign a whole model, but its attributes only. To avoid manually mapping model attributes, you can use the `fill` method.

### 🗺️ Use Route Model Binding to fetch the model

Example `mount`:

```php
public function mount(User $user): void
{
    $this->fill($user);
}
```

Bad:
```html
<livewire:profile :user="auth()->user()" /> 
```

Good:
```html
<livewire:profile :user="auth()->user()->uuid" /> 
```

---
### 💡 Avoid using *live* wire:model modifier where possible
Avoid using `live` wire:model modifier. This dramatically reduces unnecessary requests to the server.
In Livewire version 3, all the models are deferred by default (old: `defer` modifier), which is good.

### 💡 Avoid using *live* wire:model modifier where possible

Bad:
```html
<input wire:model.live="email">
```

Better:
```html
<input wire:model.live.debounce.500ms="email">
```

Even better:
```html
<input wire:model.blur="email">
```

Ideal:
```html
<input wire:model="email">
```

---
### 👨‍💻 Use Artisan commands to create, move, and rename components
Livewire has [built-in Artisan commands](https://livewire.laravel.com/docs/quickstart#create-a-livewire-component) to create, move, rename components, etc.
For example, instead of manually renaming files, which could be error-prone, you can use the following command:
- `php artisan livewire:move Old/Path/To/Component New/Path/To/Component`

---
### 💱 Always use loading states for better UX
You can use [loading states](https://livewire.laravel.com/docs/loading#basic-usage) to make UX better. It will indicate to the user that something is happening in the background if your process is running longer than expected. To avoid flickering, you can use the `delay` modifier.

### 💱 Always use loading states for better UX

Example:
```html
<div>
    <button wire:click="update">Update</button>

    <span wire:loading.delay wire:target="update">
        Loading...
    </span>
</div>
```

---
### 📈 Use lazy loading
Instead of blocking the page render until your data is ready, you can create a placeholder using the [lazy loading](https://livewire.laravel.com/docs/lazy) technique to make your UI more responsive.

📈 Use lazy loading

Example:

Add the `placeholder` method in the component class:
```php
public function placeholder(): string
{
    return <<<'HTML'
        <div>
            <!-- Loading spinner... -->
            <svg>...</svg>
        </div>
    HTML;
}
```

Add the component with the `lazy` attribute:
```html
<livewire:component-name lazy />
```

---
### 🔗 Entangle
Sync your data with the backend using [$wire.entangle](https://livewire.laravel.com/docs/alpine#sharing-state-using-wireentangle) directive. This way the model will be updated instantly on the frontend, and the data will persist server-side after the network request reaches the server. It dramatically improves the user experience on slow devices. This approach is called "Optimistic Response" or "Optimistic UI" in other frontend communities.

### 🔗 Entangle your live data

```html
<div x-data="{ count: $wire.entangle('count') }">
    <input x-model="count" type="number">
    <button @click="count++">+</button>
</div>
```

---
### 🌎 Use Form Request rules for validation
Livewire doesn't support [Form Requests](https://laravel.com/docs/9.x/validation#form-request-validation) internally, but instead of hardcoding the array of validation rules in the component, you may get it directly from Form Request.
This way you can reuse the same validation rules in different application layers, for example in API endpoints.

### 🌎 Use Form Request rules for validation

Bad:
```php
public function rules(): array
{
    return [
        'field1' => ['required', 'string'],
        'field2' => ['required', 'integer'],
        'field3' => ['required', 'boolean'],
    ];
}
```

Good:
```php
public function rules(): array
{
    return (new MyFormRequest)->rules();
}
```

---
### 🧪 Always write feature tests
Even simple tests can greatly help when you change something in the component.
Livewire has a straightforward yet powerful [testing API](https://livewire.laravel.com/docs/testing).
