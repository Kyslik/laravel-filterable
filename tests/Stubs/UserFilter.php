<?php

namespace Kyslik\LaravelFilterable\Test\Stubs;

use Kyslik\LaravelFilterable\Generic\Filter;

class UserFilter extends Filter
{

    protected $filterables = [
        'id',
        'username',
        'email',
        'created_at',
        'updated_at',
        'deleted_at',
        'active',
        'published',
    ];


    /**Testing helper function.
     *
     * @return string
     */
    public function getGroupingOperator()
    {
        return $this->groupingOperator;
    }

    // public function role($role){
    //     return $this->setJoin("roles","user.id","roles.user_id")->where("roles.role",$role);
    // }
}
