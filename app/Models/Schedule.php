<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $table = 'schedules';

    protected $fillable = [
        'company_id',
        'branch_id',
        'user_id',
        'work_date',
        'start_time',
        'end_time',
        // otros campos que tengas...
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
