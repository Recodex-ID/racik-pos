<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'user_id',
        'transaction_number',
        'transaction_date',
        'subtotal',
        'discount_amount',
        'total_amount',
        'payment_method',
        'payment_amount',
        'change_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'subtotal' => 'decimal:12,2',
        'discount_amount' => 'decimal:12,2',
        'total_amount' => 'decimal:12,2',
        'payment_amount' => 'decimal:12,2',
        'change_amount' => 'decimal:12,2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function scopeByTenant(Builder $query, $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeByStatus(Builder $query, $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeDrafts(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING)->where('payment_amount', 0);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }
}
