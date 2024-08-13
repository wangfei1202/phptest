<?php

declare (strict_types=1);

namespace App\Model;

class User extends Model
{

    protected $table = 'users';
    public $timestamps = false;
    protected $hidden = [
        'password', 'update_time'
    ];

}
