<?php

namespace Ssntpl\LaravelComments\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class TestUser extends Model
{
    protected $table = 'users';

    public $timestamps = false;

    protected $fillable = ['name'];
}
