# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel Livewire CRUD starter kit built with enterprise-level security and performance in mind. The application uses **Flux UI components** and **Spatie Laravel Permission** for comprehensive role & permission management.

## Development Commands

### Development Environment
```bash
# Start full development environment (server, queue, logs, vite)
composer run dev

# Individual services
php artisan serve
php artisan queue:listen --tries=1
php artisan pail --timeout=0
npm run dev
```

### Testing
```bash
# Run all tests with config clear
composer run test

# Run specific test suites
php artisan test
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run single test file
php artisan test tests/Feature/DashboardTest.php
```

### Asset Building
```bash
# Development build with hot reloading
npm run dev

# Production build
npm run build
```

### Code Quality
```bash
# Format code using Laravel Pint
./vendor/bin/pint

# Run Pint with specific configuration
./vendor/bin/pint --config=pint.json
```

### Database & Seeding
```bash
# Fresh migration with seeding
php artisan migrate:fresh --seed

# Run specific seeder
php artisan db:seed --class=UserSeeder
```

## Architecture Overview

### Core Stack
- **Laravel 12.x** with **PHP 8.2+**
- **Livewire Volt 1.7** for single-file components
- **Flux UI 2.1** premium component library
- **Spatie Laravel Permission** for RBAC
- **PestPHP** for testing
- **Vite** with **Tailwind CSS 4.x**

### Multi-Tenancy Architecture

**Tenant Resolution Pattern** (3-layer fallback):
```php
private function getCurrentTenant()
{
    // 1. Try app service container
    try { return app('current_tenant'); }
    
    // 2. Try request attributes  
    $tenant = request()->attributes->get('current_tenant');
    if ($tenant) return $tenant;
    
    // 3. Fallback to user's tenant_id
    if ($user->tenant_id) return Tenant::find($user->tenant_id);
    
    throw new \Exception('No tenant context available');
}
```

**Data Isolation Patterns**:
- All models use `scopeByTenant()` methods
- Consistent `tenant_id` foreign key constraints
- Computed properties for tenant context: `#[Computed] currentTenant()`
- Super Admin bypass with cross-tenant access

### Component Organization Hierarchy

```
app/Livewire/
├── Actions/            # System-level actions (Logout)
├── Administrator/      # Super Admin components (cross-tenant)
│   ├── ManageUsers.php
│   ├── ManageRoles.php
│   └── ManageTenants.php
├── Tenant/            # Tenant-scoped CRUD operations
│   ├── ManageCategories.php
│   ├── ManageCustomers.php
│   ├── ManageProducts.php
│   └── ManageExpenses.php
├── Pos/               # Point-of-Sale system
│   └── Cashier.php    # Cart management & transaction processing
├── Reports/           # Analytics & reporting
│   ├── MonthlyTransaction.php
│   └── MonthlyExpense.php
└── Dashboard.php      # Multi-tenant analytics dashboard
```

### POS System Architecture

**Cart State Management**:
- Persistent cart keys: `product_{id}`
- Draft transaction system with database persistence
- Real-time calculations (subtotal, discount, change)
- Transaction number generation with tenant initials

**Transaction Workflow**:
```
Cart Building → Payment Processing → Transaction Completion
     ↓              ↓                    ↓
  Draft Save    Validation &         Receipt Generation
              Amount Calculation    & Stock Updates
```

**Features**:
- Multistep transaction flow with state persistence
- Quick customer creation within POS flow
- Real-time inventory checking and filtering
- Draft save/load for interrupted transactions

### Reporting Architecture

**Temporal Data Analysis**:
- Year/month selection with automatic pagination reset
- Daily chart generation with configurable date ranges
- Month-over-month comparison analytics
- Real-time statistical computations

**Multi-Dimensional Analytics**:
- Category-based expense/transaction breakdowns
- Payment method statistics and trends
- Top products and expenses analysis
- Chart.js integration with tenant-aware data

### Key Directories
```
app/Livewire/           # Livewire components
├── Actions/            # Action components (Logout)
├── Administrator/      # Admin CRUD components
├── Tenant/            # Tenant-scoped components
├── Pos/               # Point-of-Sale components
├── Reports/           # Analytics components
└── Dashboard.php       # Main dashboard with analytics

resources/views/
├── livewire/          # Component views
├── flux/              # Flux UI component overrides
└── components/        # Blade components

resources/js/          # JavaScript with Chart.js integration
├── app.js             # Main entry point
├── chart.js           # Dashboard chart implementations
└── bootstrap.js       # Axios configuration

resources/css/         # Tailwind CSS with Flux theming
```

### Database Structure
- **Users table** with roles & permissions (Spatie package)
- **Tenants table** with `is_active` status
- **Multi-tenant tables** with `tenant_id` foreign keys
- **Transactions/Products/Categories** with tenant isolation
- **Cache/Jobs/Sessions** using database driver
- **SQLite in-memory** for testing

### Authentication & Authorization
- **Spatie Laravel Permission** integration
- **Role-based middleware**: `role:Super Admin` 
- **Route protection** with `auth`, `verified` middleware
- **Blade directives**: `@role('Super Admin')` for template-level access
- **Tenant-aware authorization** in component methods

### Component Architecture Patterns

**Standardized CRUD Pattern**:
```php
// Standard properties
public $editingEntityId = null;
public $showModal = false;
public $search = '';

// Standard methods
public function create()     // Reset form & open modal
public function edit($id)    // Load entity & open modal  
public function save()       // Validate & save entity
public function delete($id)  // Delete with confirmation
public function resetForm()  // Clear form state
```

**File Upload Management**:
- Consistent use of `WithFileUploads` trait
- Storage management with cleanup on updates
- Image handling for products with validation
- Receipt file handling for expenses
- Existing file preservation during updates

**Search and Filtering**:
- Multi-field search implementations
- Category-based filtering with dropdowns
- Real-time filtering with computed properties
- Pagination integration with preserved search state

### Business Logic Patterns

**Transaction Processing**:
- Database transaction wrapping for data consistency
- Error handling with automatic rollback mechanisms
- Status-based workflow: `draft → pending → completed`
- Audit trail maintenance with user tracking

**Form Handling**:
- Dynamic form behavior (manual category input toggles)
- Conditional validation rules based on form state
- Real-time property updates with `updated()` method
- File upload with existing file preservation

## Development Notes
- use the flux(resources/views/flux) component in the created view
- always respond in Indonesian
- before generating code, study docs/LARAVEL-BEST-PRACTICES.md and docs/LIVEWIRE-BEST-PRACTICES.md and apply them to the generated code.
- the generated code must always be consistent with the existing code.

## Git Best Practices
- make sure to confirm first before pushing to the repo

## JavaScript + Livewire Integration Best Practices

### Event Listeners
Always use both events for Livewire compatibility:
```javascript
document.addEventListener('DOMContentLoaded', initFunction);
document.addEventListener('livewire:navigated', () => setTimeout(initFunction, 300));
```

### Timing & Retry Mechanism
- Use setTimeout with delay (100-300ms) to ensure DOM ready
- Implement retry mechanism with multiple delays:
```javascript
function retryInitialization() {
    [500, 1000, 2000].forEach(delay => {
        setTimeout(() => {
            if (!initialized) initFunction();
        }, delay);
    });
}
```

### Instance Management (Charts, Libraries, etc)
- Always destroy existing instances before creating new ones
- Store instances in window object for global access
- Check if instances already exist before retry:
```javascript
window.instances = { chart1: null, chart2: null };

// Destroy existing
if (window.instances.chart1) {
    window.instances.chart1.destroy();
}
```

### Data Extraction
- Use robust fallback: `dataset.attribute || textContent || defaultValue`
- Wrap in try-catch for error handling
- Parse JSON with fallback default:
```javascript
const data = JSON.parse(element.dataset.data || '{"default": "value"}');
```

### Element Detection
- Check existence of all required elements before initialization
- Return early if elements are not found:
```javascript
const el1 = document.getElementById('required1');
const el2 = document.getElementById('required2');
if (!el1 || !el2) return false;
```

### File Organization
- Separate logic into separate files (chart.js, modal.js, etc)
- Import through main app.js
- Modular approach for maintainability

### Template Pattern
```javascript
// Global instances
window.myInstances = { item1: null, item2: null };

// Init function
function initMyFeature() {
    // Check elements
    if (!requiredElements) return false;
    
    try {
        // Destroy existing
        if (window.myInstances.item1) {
            window.myInstances.item1.destroy();
        }
        
        // Initialize new
        window.myInstances.item1 = new Library(config);
        return true;
    } catch (error) {
        console.error('Init error:', error);
        return false;
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', initMyFeature);
document.addEventListener('livewire:navigated', () => setTimeout(initMyFeature, 300));

// Retry mechanism
function retryInit() {
    [500, 1000, 2000].forEach(delay => {
        setTimeout(() => {
            if (!window.myInstances.item1) initMyFeature();
        }, delay);
    });
}

document.addEventListener('DOMContentLoaded', retryInit);
document.addEventListener('livewire:navigated', retryInit);
```

## Performance Optimization Guidelines

### Livewire Performance
- **Computed Properties**: Use `#[Computed]` for database queries to cache within component lifecycle
- **Event Listeners**: Prefer over polling for real-time updates
- **Primitive Types**: Pass strings/integers instead of objects to components
- **Component Nesting**: Keep at maximum 1 level deep
- **Form Objects**: Always use Livewire v3 form abstraction

### Frontend Performance
- **Vite HMR**: Fast development with hot module replacement
- **Tailwind Purging**: Automatic unused CSS removal
- **Chart.js**: Proper instance management with destroy/recreate pattern
- **Asset Compilation**: Use `npm run build` for production

## Testing Strategy

### Test Structure
- **Feature Tests**: Full component functionality testing
- **Unit Tests**: Individual method/class testing  
- **Database**: In-memory SQLite for fast testing
- **Coverage**: Comprehensive test coverage for core features

### Test Categories
```bash
tests/Feature/
├── Auth/              # Authentication flow tests
├── Administrator/     # Admin CRUD functionality
├── Dashboard/         # Dashboard component tests
└── Settings/          # User settings tests
```

## Key Configuration Files

- **phpunit.xml**: Testing with in-memory SQLite
- **vite.config.js**: Build configuration with Tailwind plugin
- **composer.json**: Development scripts and dependencies
- **package.json**: Frontend build scripts
- **docs/**: Laravel and Livewire best practices documentation
