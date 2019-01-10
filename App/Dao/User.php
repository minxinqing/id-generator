<?php

namespace App\Dao;

use Framework\MVC\Dao;
use Framework\Core\Singleton;

class User extends Dao
{
    use Singleton;

    public function __construct()
    {
        parent::__construct('App\Entity\User');
    }
}