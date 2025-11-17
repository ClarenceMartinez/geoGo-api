<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendances'; // o el nombre real de tu tabla

    protected $fillable = [
        'company_id',
        'branch_id',
        'user_id',
        'schedule_id',
        'check_in_at',
        'check_out_at',
        'check_in_lat',
        'check_in_lng',
        'check_out_lat',
        'check_out_lng',
        'status',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
