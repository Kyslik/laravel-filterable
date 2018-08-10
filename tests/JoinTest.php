<?php

namespace Kyslik\LaravelFilterable\Test;

use Carbon\Carbon;
use Kyslik\LaravelFilterable\Exceptions\InvalidArgumentException;
use Kyslik\LaravelFilterable\Generic\Templater;
use Orchestra\Testbench\TestCase as Orchestra;

class JoinTest extends TestCase
{

    function test_single_join()
    {
        $this->assertJoinQuery('select * inner join "user" on "role"."user_id" = "user"."id" where "user"."username" = \'name\'',
           ['name' => 'name']);
    }

    function test_join_and_other_filter(){
        $this->assertJoinQuery('select * inner join "user" on "role"."user_id" = "user"."id" where "user"."username" = \'name\' '.
            'and "user"."email" = \'email@example.com\'',
            ['name' => 'name', 'email' => "email@example.com"]);

    }

    function test_left_right_joins()
    {
        $this->assertJoinQuery('select * left join "user" on "role"."user_id" = "user"."id" where "user"."username" = \'name\'',
           ['left' => 'name']);
        $this->assertJoinQuery('select * right join "user" on "role"."user_id" = "user"."id" where "user"."username" = \'name\'',
           ['right' => 'name']);
    }

    function test_multiple_joins(){
        $this->assertJoinQuery('select * '.
            'inner join "user" on "role"."user_id" = "user"."id" '.
            'inner join "permission" on "role"."id" = "permission"."role_id" '.
            'where "user"."username" = \'name\' '.
            'and "permission"."level" = \'6\'',
            [ 'permission' => '6', "name" => "name"]);
    }

    function test_join_through_tables(){
        $this->assertJoinQuery('select * '.
            'inner join "permission" on "role"."id" = "permission"."role_id" '.
            'inner join "permissiontype" on "permission"."id" = "permissiontype"."permission_id" '.
            'where "permissiontype"."type" = \'admin\'',
            [ "permissiontype" => "admin"]);
    }

    function test_join_though_tables_and_other_filter(){
        $this->assertJoinQuery('select * '.
            'inner join "permission" on "role"."id" = "permission"."role_id" '.
            'inner join "permissiontype" on "permission"."id" = "permissiontype"."permission_id" '.
            'where "permissiontype"."type" = \'admin\' '.
            'and "permissiontype"."active" = \'1\'' ,
            [ "permissiontype" => "admin",'permissiontype_active' => 1]);
    }

    function test_join_though_multiple_tables_and_other_filter(){
        $this->assertJoinQuery('select * '.
            'inner join "user" on "role"."user_id" = "user"."id" '.
            'inner join "permission" on "role"."id" = "permission"."role_id" '.
            'inner join "permissiontype" on "permission"."id" = "permissiontype"."permission_id" '.
            'where "user"."username" = \'name\' '.
            'and "permissiontype"."type" = \'admin\' '.
            'and "permissiontype"."active" = \'1\'' ,
            ["name" => "name", "permissiontype" => "admin",'permissiontype_active' => 1]);
    }

    private function assertJoinQuery($expectedQuery, $builderQuery)
    {
        $filter = $this->buildCustomFilter(http_build_query($builderQuery));
        $this->assertEquals($expectedQuery,$this->dumpQuery($filter->apply($this->builder)));
        $this->resetBuilder();
    }


    private function assertQuery($expectedQuery, $params)
    {
        $filter = $this->buildFilter(http_build_query($params));
        $this->assertEquals($expectedQuery, $this->dumpQuery($filter->apply($this->builder)));
        $this->resetBuilder();
    }
}
