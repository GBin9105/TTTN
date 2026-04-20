<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'address',
        'role',
        'status',
        'password',
        'email_verified_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    public function contactsReplied(): HasMany
    {
        return $this->hasMany(Contact::class, 'replied_by');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function reviewsApproved(): HasMany
    {
        return $this->hasMany(Review::class, 'approved_by');
    }

    public function stockReceiptsCreated(): HasMany
    {
        return $this->hasMany(StockReceipt::class, 'created_by');
    }

    public function inventoryLogsCreated(): HasMany
    {
        return $this->hasMany(InventoryLog::class, 'created_by');
    }

    public function pagesCreated(): HasMany
    {
        return $this->hasMany(Page::class, 'created_by');
    }

    public function pagesUpdated(): HasMany
    {
        return $this->hasMany(Page::class, 'updated_by');
    }

    public function orderStatusHistoriesChanged(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class, 'changed_by');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function isActive(): bool
    {
        return (bool) $this->status;
    }
}