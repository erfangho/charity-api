<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeopleAid extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'product_id',
        'helper_id',
        'quantity',
        'description',
    ];

    public function aidAllocations()
    {
        return $this->hasMany(AidAllocation::class, 'people_aid_id');
    }
}
