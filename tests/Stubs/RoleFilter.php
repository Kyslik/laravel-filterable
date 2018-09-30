<?php

namespace Kyslik\LaravelFilterable\Test\Stubs;

use Kyslik\LaravelFilterable\Filter;
use Kyslik\LaravelFilterable\JoinSupport;

class RoleFilter extends Filter
{

    use JoinSupport;


    function filterMap(): array
    {
        return [
            'name'                 => ['name'],
            'email'                => ['email'],
            'name_left'            => ['left'],
            'name_right'           => ['right'],
            'permission'           => ['permission'],
            'permissionType'       => ['permission-type'],
            'permissionTypeActive' => ['permission-type-active'],
        ];
    }


    public function name($username)
    {
        return $this->setJoin('user', 'role.user_id', 'user.id')->where('user.username', $username);
    }


    public function email($email)
    {
        return $this->setJoin('user', 'role.user_id', 'user.id')->where('user.email', $email);
    }


    public function name_left($username)
    {
        return $this->setLeftJoin('user', 'role.user_id', 'user.id')->where('user.username', $username);
    }


    public function name_right($username)
    {
        return $this->setRightJoin('user', 'role.user_id', 'user.id')->where('user.username', $username);
    }


    public function permission($level)
    {
        return $this->setJoin('permission', 'role.id', 'permission.role_id')->where('permission.level', $level);
    }


    public function permissionType($type)
    {
        return $this->addJoin('permission', 'role.id', 'permission.role_id')
                    ->setJoin('permission_type', 'permission.id', 'permission_type.permission_id')
                    ->where('permission_type.type', $type);
    }


    public function permissionTypeActive($type)
    {
        return $this->addJoin('permission', 'role.id', 'permission.role_id')
                    ->setJoin('permission_type', 'permission.id', 'permission_type.permission_id')
                    ->where('permission_type.active', $type);
    }

}
