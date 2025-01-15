<?php

namespace Ssntpl\LaravelComments\Models;

use Illuminate\Database\Eloquent\Model;
use Ssntpl\LaravelFiles\Traits\HasFiles;

class Comment extends Model
{
    use HasFiles;

    Protected $fillable = [
        'date',
        'type',
        'text',
        'user_id'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'comments';

}