<?php

namespace Kyslik\LaravelFilterable\Test\Stubs;

use Kyslik\LaravelFilterable\Filter;

class RoleFilter extends Filter
{

    protected $filterables = [
        'id',
        'user_id',
        'role',
        'created_at',
        'updated_at',
        'deleted_at',
        'active',
        'published',
    ];

    function filterMap(): array
    {
        return [
            'name' => ['name'],
            'email' => ['email'],
            'name_left' => ['left'],
            'name_right' => ['right'],
            'permission' => ['permission'],
            'permissiontype' => ['permissiontype'],
            'permissiontype_active' => ['permissiontype_active'],

        ];
    }

    public function name($username){
        return $this->setJoin("user","role.user_id","user.id")->where("user.username",$username);
    }

    public function email($email){
        return $this->setJoin("user","role.user_id","user.id")->where("user.email",$email);
    }

    public function name_left($username){
        return $this->setLeftJoin("user","role.user_id","user.id")->where("user.username",$username);
    }

    public function name_right($username){
        return $this->setRightJoin("user","role.user_id","user.id")->where("user.username",$username);
    }

    public function permission($level){
        return $this->setJoin("permission","role.id","permission.role_id")->where("permission.level",$level);
    }

    public function permissiontype($permission_type){
        return $this->addJoin("permission","role.id","permission.role_id")
        ->setJoin("permissiontype","permission.id","permissiontype.permission_id")
        ->where("permissiontype.type",$permission_type);
    }

    public function permissiontype_active($permission_type_active){
        return $this->addJoin("permission","role.id","permission.role_id")
        ->setJoin("permissiontype","permission.id","permissiontype.permission_id")
        ->where("permissiontype.active",$permission_type_active);
    }

}
