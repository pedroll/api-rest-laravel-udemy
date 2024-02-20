<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $table = 'categories';

    /**
     * relacion uno a muchos
     *
     * @return HasMany
     */
    public function posts()
    {
        return $this->hasMany('App\Post');
    }
}
