<?php

namespace App\Models;

use App\Traits\FilterableByDate;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    use FilterableByDate;
    public const COL_ID = 'id';
    public const COL_CREATED_AT = 'created_at';
    public const COL_UPDATED_AT = 'updated_at';

    // public $incrementing = false;
    // protected $keyType = "string";
    protected $primaryKey = self::COL_ID;

    protected static function boot(): void
    {
        parent::boot();

        // self::creating(static function ($model) {
        //     $model->{$model->getKeyName()} = Str::ulid();
        // });
    }

    public function getId(): string
    {
        return $this->getAttribute(self::COL_ID);
    }

    public function getIdColumn(): string
    {
        return self::COL_ID;
    }

    public function getCreatedAt(): Carbon
    {
        return $this->getAttribute(self::COL_CREATED_AT);
    }

    public function getUpdatedAt(): ?Carbon
    {
        return $this->getAttribute(self::COL_UPDATED_AT);
    }
}
