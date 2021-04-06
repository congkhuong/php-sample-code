<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model as Base;
use App\Entities\Traits\HasModifiers;
use App\Entities\Traits\HasDeletedBy;
use App\Entities\Traits\IdNoAttribute;

class Model extends Base
{
    use HasModifiers;

    protected $appends = ['id_no'];

    public static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->updateModifiers();
        });
    }
}
