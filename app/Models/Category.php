<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;

class Category extends Model
{
    use ModelTree, AdminBuilder;

    public function children()
    {
        return $this->hasMany(static::class, 'parent_id');
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}
