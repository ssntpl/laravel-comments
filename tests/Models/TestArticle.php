<?php

namespace Ssntpl\LaravelComments\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Ssntpl\LaravelComments\Traits\HasComments;

class TestArticle extends Model
{
    use HasComments;

    protected $table = 'articles';

    public $timestamps = false;

    protected $fillable = ['title'];
}
