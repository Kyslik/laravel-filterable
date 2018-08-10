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
            'name_left' => ['left'],
            'name_right' => ['right'],
            'permission' => ['permission']
        ];
    }

    public function name($username){
        return $this->setJoin("user","role.user_id","user.id")->where("user.username",$username);
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

}
