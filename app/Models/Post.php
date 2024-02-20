<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    protected $table = 'users';

    /**
     * Relacion muchos a uno
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    /**
     * Relacion muchos a uno
     *
     * @return BelongsTo
     */
    public function ucategoryser()
    {
        return $this->belongsTo('App\Category', 'category_id');
    }
}
