<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'document',
        'address',
        'city',
        'state',
        'postal_code',
        'contact_person',
        'notes',
        'active',
        'traccar_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Get the vehicles associated with the customer.
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    /**
     * Get the equipment assigned to this customer.
     * Each customer can access multiple equipment directly, in addition to
     * those assigned to their vehicles.
     */
    public function equipment(): BelongsToMany
    {
        return $this->belongsToMany(Equipment::class)->withTimestamps();
    }
}
