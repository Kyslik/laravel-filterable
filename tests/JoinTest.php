<?php

namespace Kyslik\LaravelFilterable\Test;

class JoinTest extends TestCase
{

    function test_single_join()
    {
        $this->assertJoinQuery('select * inner join "user" on "role"."user_id" = "user"."id" where "user"."username" = \'name\'',
            ['name' => 'name']);
    }


    function test_join_and_other_filter()
    {
        $this->assertJoinQuery('select * inner join "user" on "role"."user_id" = "user"."id" where "user"."username" = \'name\' and "user"."email" = \'email@example.com\'',
            ['name' => 'name', 'email' => 'email@example.com']);
    }


    function test_left_join()
    {
        $this->assertJoinQuery('select * left join "user" on "role"."user_id" = "user"."id" where "user"."username" = \'name\'',
            ['left' => 'name']);
    }


    function test_right_join()
    {
        $this->assertJoinQuery('select * right join "user" on "role"."user_id" = "user"."id" where "user"."username" = \'name\'',
            ['right' => 'name']);
    }


    function test_multiple_joins()
    {
        $this->assertJoinQuery('select * inner join "user" on "role"."user_id" = "user"."id" inner join "permission" on "role"."id" = "permission"."role_id" where "user"."username" = \'name\' and "permission"."level" = \'6\'',
            ['permission' => '6', 'name' => 'name']);
    }


    function test_join_through_tables()
    {
        $this->assertJoinQuery('select * inner join "permission" on "role"."id" = "permission"."role_id" inner join "permission_type" on "permission"."id" = "permission_type"."permission_id" where "permission_type"."type" = \'admin\'',
            ['permission-type' => 'admin']);
    }


    function test_join_though_tables_and_other_filter()
    {
        $this->assertJoinQuery('select * inner join "permission" on "role"."id" = "permission"."role_id" inner join "permission_type" on "permission"."id" = "permission_type"."permission_id" where "permission_type"."type" = \'admin\' and "permission_type"."active" = \'1\'',
            ['permission-type' => 'admin', 'permission-type-active' => 1]);
    }


    function test_join_though_multiple_tables_and_other_filter()
    {
        $this->assertJoinQuery('select * inner join "user" on "role"."user_id" = "user"."id" inner join "permission" on "role"."id" = "permission"."role_id" inner join "permission_type" on "permission"."id" = "permission_type"."permission_id" where "user"."username" = \'name\' and "permission_type"."type" = \'admin\' and "permission_type"."active" = \'1\'',
            ['name' => 'name', 'permission-type' => 'admin', 'permission-type-active' => 1]);
    }


    private function assertJoinQuery($expectedQuery, $builderQuery)
    {
        $filter = $this->buildCustomFilter(http_build_query($builderQuery));
        $this->assertEquals($expectedQuery, $this->dumpQuery($filter->apply($this->builder)));
        $this->resetBuilder();
    }
}
