<?php

namespace App\Entity;

use Framework\MVC\Entity;

class User extends Entity
{
    const TABLE_NAME = 'users';

    const PK_ID = 'id';

    public $id;
    public $country_id;
    public $name;
    public $created_at;
    public $updated_at;
    public $deleted_at;

}