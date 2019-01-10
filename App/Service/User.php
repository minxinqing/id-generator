<?php

namespace App\Service;

use App\Dao\User as UserDao;
use Framework\Core\Singleton;

class User
{
    use Singleton;

    public function getUserInfoById($id)
    {
        return UserDao::getInstance()->fetchById($id);
    }

    public function getUserInfoList()
    {
        return UserDao::getInstance()->fetchAll();
    }

    public function add(array $array)
    {
        return UserDao::getInstance()->add($array);
    }

    public function updateById(array $array, $id)
    {
        return UserDao::getInstance()->update($array, "id={$id}");
    }

}
