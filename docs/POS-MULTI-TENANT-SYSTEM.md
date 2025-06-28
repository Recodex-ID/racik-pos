# Sistem POS Multi-Tenant

## Deskripsi Sistem

Sistem Point of Sale (POS) multi-tenant yang memungkinkan beberapa tenant (pemilik bisnis) untuk menggunakan aplikasi yang sama dengan data yang terpisah dan aman. Sistem ini dibangun menggunakan Laravel dengan arsitektur yang mengikuti prinsip-prinsip Laravel Best Practices.

## Arsitektur Multi-Tenant

### Konsep Multi-Tenancy
- **Super Admin**: Developer yang mengatur dan mengelola semua tenant
- **Admin**: Pemilik tenant yang mengelola toko/bisnis mereka
- **Isolasi Data**: Setiap tenant memiliki data yang terpisah dan tidak dapat mengakses data tenant lain

### Hierarki Sistem
```
Super Admin (Developer)
├── Tenant 1
│   ├── Store 1.1
│   ├── Store 1.2
│   └── Users (Admin + Staff)
├── Tenant 2
│   ├── Store 2.1
│   └── Users (Admin + Staff)
└── Tenant 3
    ├── Store 3.1
    ├── Store 3.2
    ├── Store 3.3
    └── Users (Admin + Staff)
```

## Model dan Struktur Database

### 1. Model Tenant (`app/Models/Tenant.php`)
**Tujuan**: Mengelola data tenant/pemilik bisnis

**Kolom Database**:
- `id`: Primary key
- `name`: Nama bisnis/tenant
- `email`: Email tenant (unique)
- `phone`: Nomor telepon
- `address`: Alamat bisnis
- `is_active`: Status aktif tenant
- `created_at`, `updated_at`: Timestamp
- `deleted_at`: Soft delete timestamp

**Relasi**:
- `hasMany(User::class)`: Tenant memiliki banyak user
- `hasMany(Store::class)`: Tenant memiliki banyak toko

**Scope**:
- `scopeActive()`: Filter tenant yang aktif

### 2. Model Store (`app/Models/Store.php`)
**Tujuan**: Mengelola data toko/cabang dalam satu tenant

**Kolom Database**:
- `id`: Primary key
- `tenant_id`: Foreign key ke tenant
- `name`: Nama toko/cabang
- `address`: Alamat toko
- `phone`: Nomor telepon toko
- `is_active`: Status aktif toko
- `created_at`, `updated_at`: Timestamp
- `deleted_at`: Soft delete timestamp

**Relasi**:
- `belongsTo(Tenant::class)`: Toko milik satu tenant
- `hasMany(User::class)`: Toko memiliki banyak user/staff
- `hasMany(Category::class)`: Toko memiliki banyak kategori produk
- `hasMany(Product::class)`: Toko memiliki banyak produk
- `hasMany(Customer::class)`: Toko memiliki banyak pelanggan
- `hasMany(Transaction::class)`: Toko memiliki banyak transaksi

**Scope**:
- `scopeActive()`: Filter toko yang aktif
- `scopeByTenant($tenantId)`: Filter toko berdasarkan tenant

### 3. Model Category (`app/Models/Category.php`)
**Tujuan**: Mengelola kategori produk per toko

**Kolom Database**:
- `id`: Primary key
- `store_id`: Foreign key ke store
- `name`: Nama kategori
- `description`: Deskripsi kategori
- `is_active`: Status aktif kategori
- `created_at`, `updated_at`: Timestamp
- `deleted_at`: Soft delete timestamp

### 4. Model Product (`app/Models/Product.php`)
**Tujuan**: Mengelola data produk per toko

**Kolom Database**:
- `id`: Primary key
- `store_id`: Foreign key ke store
- `category_id`: Foreign key ke category
- `name`: Nama produk
- `description`: Deskripsi produk
- `sku`: Stock Keeping Unit (unique per store)
- `price`: Harga jual
- `cost`: Harga beli/modal
- `stock`: Jumlah stok
- `min_stock`: Minimum stok untuk alert
- `is_active`: Status aktif produk
- `created_at`, `updated_at`: Timestamp
- `deleted_at`: Soft delete timestamp

### 5. Model Customer (`app/Models/Customer.php`)
**Tujuan**: Mengelola data pelanggan per toko

**Kolom Database**:
- `id`: Primary key
- `store_id`: Foreign key ke store
- `name`: Nama pelanggan
- `email`: Email pelanggan
- `phone`: Nomor telepon
- `address`: Alamat pelanggan
- `is_active`: Status aktif pelanggan
- `created_at`, `updated_at`: Timestamp
- `deleted_at`: Soft delete timestamp

### 6. Model Transaction (`app/Models/Transaction.php`)
**Tujuan**: Mengelola data transaksi penjualan

**Kolom Database**:
- `id`: Primary key
- `store_id`: Foreign key ke store
- `customer_id`: Foreign key ke customer (nullable)
- `user_id`: Foreign key ke user (kasir)
- `transaction_number`: Nomor transaksi (unique)
- `transaction_date`: Tanggal transaksi
- `subtotal`: Subtotal sebelum diskon dan pajak
- `discount_amount`: Jumlah diskon
- `tax_amount`: Jumlah pajak
- `total_amount`: Total akhir
- `payment_method`: Metode pembayaran
- `payment_amount`: Jumlah pembayaran
- `change_amount`: Jumlah kembalian
- `status`: Status transaksi
- `notes`: Catatan transaksi
- `created_at`, `updated_at`: Timestamp

### 7. Model TransactionItem (`app/Models/TransactionItem.php`)
**Tujuan**: Mengelola detail item dalam transaksi

**Kolom Database**:
- `id`: Primary key
- `transaction_id`: Foreign key ke transaction
- `product_id`: Foreign key ke product
- `quantity`: Jumlah item
- `unit_price`: Harga satuan saat transaksi
- `total_price`: Total harga item (quantity × unit_price)
- `created_at`, `updated_at`: Timestamp

## Seeders yang Dibuat

### 1. TenantSeeder
Membuat data tenant contoh:
- Toko Maju Jaya
- Warung Berkah
- Minimarket Sejahtera

### 2. StoreSeeder
Membuat toko untuk setiap tenant:
- Setiap tenant memiliki minimal 1 cabang utama
- Tenant pertama memiliki cabang tambahan di Kelapa Gading

### 3. CategorySeeder - ProductSeeder - CustomerSeeder
Akan dibuat untuk mengisi data contoh kategori, produk, dan pelanggan.

### 4. TransactionSeeder - TransactionItemSeeder
Akan dibuat untuk mengisi data contoh transaksi penjualan.

## Fitur Keamanan Multi-Tenant

### 1. Data Isolation
- Setiap data terikat pada `tenant_id` atau `store_id`
- Scope query otomatis berdasarkan tenant yang sedang login
- Middleware untuk memastikan user hanya mengakses data tenant mereka

### 2. Role-Based Access Control
- **Super Admin**: Akses penuh ke semua tenant dan toko
- **Admin**: Akses penuh ke tenant dan toko mereka sendiri
- **Staff**: Akses terbatas sesuai permission yang diberikan

### 3. Soft Deletes
Semua model menggunakan soft deletes untuk:
- Menjaga integritas data historis
- Memungkinkan restore data yang terhapus
- Audit trail yang lebih baik

## Konvensi Penamaan

Sistem ini mengikuti konvensi Laravel standard:
- **Model**: Singular (Tenant, Store, Product)
- **Table**: Plural (tenants, stores, products)
- **Controller**: Singular dengan suffix Controller
- **Migration**: Descriptive dengan timestamp
- **Seeder**: Singular dengan suffix Seeder

## Langkah Selanjutnya

1. **Update User Model**: Menambahkan relasi ke tenant dan store
2. **Middleware Multi-Tenant**: Membuat middleware untuk isolasi data
3. **Livewire Components**: Membuat komponen CRUD untuk setiap model
4. **Authorization Policies**: Membuat policy untuk setiap model
5. **API Endpoints**: Membuat API untuk sistem POS
6. **Dashboard Analytics**: Membuat dashboard dengan analytics per tenant/store

## File yang Telah Dibuat

### Models
- `app/Models/Tenant.php`
- `app/Models/Store.php`
- `app/Models/Category.php`
- `app/Models/Product.php`
- `app/Models/Customer.php`
- `app/Models/Transaction.php`
- `app/Models/TransactionItem.php`

### Migrations
- `database/migrations/xxxx_create_tenants_table.php`
- `database/migrations/xxxx_create_stores_table.php`
- `database/migrations/xxxx_create_categories_table.php`
- `database/migrations/xxxx_create_products_table.php`
- `database/migrations/xxxx_create_customers_table.php`
- `database/migrations/xxxx_create_transactions_table.php`
- `database/migrations/xxxx_create_transaction_items_table.php`

### Seeders
- `database/seeders/TenantSeeder.php`
- `database/seeders/StoreSeeder.php`
- `database/seeders/CategorySeeder.php`
- `database/seeders/ProductSeeder.php`
- `database/seeders/CustomerSeeder.php`
- `database/seeders/TransactionSeeder.php`
- `database/seeders/TransactionItemSeeder.php`

## Cara Menjalankan

```bash
# Jalankan migration
php artisan migrate

# Jalankan seeder
php artisan db:seed --class=TenantSeeder
php artisan db:seed --class=StoreSeeder

# Atau jalankan semua seeder sekaligus
php artisan db:seed
```

---

*Dokumentasi ini dibuat berdasarkan implementasi sistem POS multi-tenant menggunakan Laravel dengan mengikuti best practices dan konvensi Laravel.*