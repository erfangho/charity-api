<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'organization_id',
        'quantity',
        'description',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(PackageItem::class, 'package_id');
    }

    public function packageAllocations()
    {
        return $this->hasMany(Package::class, 'package_id');
    }

    public function packageItems()
    {
        return $this->hasMany(PackageItem::class, 'package_id');
    }
}
