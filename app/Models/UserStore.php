<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserStore extends Model
{
    /** @use HasFactory<\Database\Factories\UserStoreFactory> */
    use HasFactory;

    public const TABLE_NAME = 'user_stores';

    public const COL_ID = 'id';

    public const COL_USER_ID = 'user_id';

    public const COL_STORE_ID = 'store_id';

    public function user()
    {
        return $this->belongsTo(User::class, self::COL_USER_ID, User::COL_ID);
    }

    public function store()
    {
        return $this->belongsTo(Store::class, self::COL_STORE_ID, Store::COL_ID);
    }
}
