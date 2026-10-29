<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'check_in_at',
        'check_out_at',
        'is_present',
        'status',
        'note',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'is_present' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
