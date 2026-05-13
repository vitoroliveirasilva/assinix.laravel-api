<?php

namespace App\Models;

use App\Enums\CurrencyCode;
use App\Enums\RecurrenceType;
use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'payment_method_id',
        'name',
        'description',
        'amount',
        'currency',
        'amount_brl',
        'exchange_rate',
        'exchange_rate_date',
        'status',
        'recurrence',
        'interval',
        'interval_in_days',
        'starts_at',
        'next_billing_at',
        'ends_at',
        'last_charged_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'amount_brl' => 'decimal:2',
            'exchange_rate' => 'decimal:8',
            'currency' => CurrencyCode::class,
            'status' => SubscriptionStatus::class,
            'recurrence' => RecurrenceType::class,
            'interval' => 'integer',
            'interval_in_days' => 'integer',
            'starts_at' => 'date',
            'next_billing_at' => 'date',
            'ends_at' => 'date',
            'last_charged_at' => 'date',
            'exchange_rate_date' => 'date',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(SubscriptionHistory::class);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', SubscriptionStatus::Active);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query
            ->active()
            ->whereDate('next_billing_at', '>=', now()->toDateString());
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query
            ->active()
            ->whereDate('next_billing_at', '<', now()->toDateString());
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    public function isActive(): bool
    {
        return $this->status === SubscriptionStatus::Active;
    }

    public function isPaused(): bool
    {
        return $this->status === SubscriptionStatus::Paused;
    }

    public function isCanceled(): bool
    {
        return $this->status === SubscriptionStatus::Canceled;
    }

    public function isExpired(): bool
    {
        return $this->status === SubscriptionStatus::Expired;
    }
}