<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AidAllocation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'status',
    ];
    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id', 'id');
    }

    public function helpSeeker()
    {
        return $this->belongsTo(HelpSeeker::class, 'help_seeker_id', 'id');
    }

    public function peopleAid()
    {
        return $this->belongsTo(PeopleAid::class, 'people_aid_id', 'id');
    }
}
