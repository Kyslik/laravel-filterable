<?php

namespace Kyslik\LaravelFilterable\Test;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Kyslik\LaravelFilterable\Exceptions\InvalidSettingsException;
use Kyslik\LaravelFilterable\Filterable;
use Kyslik\LaravelFilterable\Generic\Filter;

class RelationalGenericFilterTest extends GenericTestCase
{

    private $prefix = 'f-';


    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }


    public function setUp(): void
    {
        parent::setUp();

        config()->set('filterable.prefix', $this->prefix);
        $this->createSchema();
        $this->seedRecords();
    }


    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username');
            $table->string('email');
        });

        Schema::create('profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('bio');
            $table->boolean('verified')->default(false);
        });

        Schema::create('avatars', function (Blueprint $table) {
            $table->increments('id');
            $table->string('imageable_type');
            $table->unsignedInteger('imageable_id');
            $table->string('disk');
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('author_id');
            $table->string('title');
            $table->boolean('published')->default(false);
        });
    }


    private function seedRecords(): void
    {
        $u1 = FilterableUser::create(['username' => 'sentinel', 'email' => 'sentinel@example.test']);
        FilterableProfile::create(['user_id' => $u1->id, 'bio' => 'backend architect', 'verified' => true]);
        FilterableAvatar::create(['imageable_type' => FilterableUser::class, 'imageable_id' => $u1->id, 'disk' => 's3']);

        $u2 = FilterableUser::create(['username' => 'probe', 'email' => 'probe@example.test']);
        FilterableProfile::create(['user_id' => $u2->id, 'bio' => 'qa runner', 'verified' => false]);
        FilterableAvatar::create(['imageable_type' => FilterableUser::class, 'imageable_id' => $u2->id, 'disk' => 'local']);

        FilterablePost::create(['author_id' => $u1->id, 'title' => 'Alpha release', 'published' => true]);
        FilterablePost::create(['author_id' => $u1->id, 'title' => 'Draft notes', 'published' => false]);
        FilterablePost::create(['author_id' => $u2->id, 'title' => 'Beta probe', 'published' => true]);
    }


    function test_has_one_generic_filter_with_dot_notation_and_boolean_operator()
    {
        $filter = $this->buildFilter(
            UserRelationFilter::class,
            'f-profile.bio=~architect&f-profile.verified=b=true'
        );

        $query = FilterableUser::query()->filter($filter);
        $results = $query->pluck('username')->all();

        $this->assertSame(['sentinel'], $results);
        $this->assertSame(1, substr_count(strtolower($query->toSql()), 'exists'));
    }


    function test_morph_one_generic_filter()
    {
        $filter = $this->buildFilter(UserRelationFilter::class, 'f-avatar.disk=s3');

        $results = FilterableUser::query()->filter($filter)->pluck('username')->all();

        $this->assertSame(['sentinel'], $results);
    }


    function test_belongs_to_generic_filter_with_dot_key_and_alias_key()
    {
        $byDot = $this->buildFilter(PostRelationFilter::class, 'f-author.username=sentinel&f-published=b=true');
        $this->assertSame(
            ['Alpha release'],
            FilterablePost::query()->filter($byDot)->pluck('title')->all()
        );

        $byAlias = $this->buildFilter(PostRelationFilter::class, 'f-author-email=~probe@');
        $this->assertSame(
            ['Beta probe'],
            FilterablePost::query()->filter($byAlias)->pluck('title')->all()
        );
    }


    function test_nested_belongs_to_and_has_one_chain()
    {
        $filter = $this->buildFilter(PostRelationFilter::class, 'f-author.profile.bio=~runner');

        $results = FilterablePost::query()->filter($filter)->pluck('title')->all();

        $this->assertSame(['Beta probe'], $results);
    }


    function test_or_grouping_operator_across_direct_and_belongs_to_filters()
    {
        $filter = $this->buildFilter(
            PostRelationFilter::class,
            'f-title=Draft%20notes&f-author.username=probe&grouping-operator=or'
        );

        $results = FilterablePost::query()->filter($filter)->orderBy('id')->pluck('title')->all();

        $this->assertSame(['Draft notes', 'Beta probe'], $results);
    }


    function test_column_specific_settings_work_for_relational_filterables()
    {
        $allowed = $this->buildFilter(RestrictedRelationFilter::class, 'f-author.username=~sent');
        $this->assertCount(2, FilterablePost::query()->filter($allowed)->get());

        $disallowedLikeTreatedAsLiteralEquals = $this->buildFilter(
            RestrictedRelationFilter::class,
            'f-author-email=~sentinel@example.test'
        );
        $this->assertCount(0, FilterablePost::query()->filter($disallowedLikeTreatedAsLiteralEquals)->get());
    }


    function test_route_support_and_has_any_filter_handle_dot_notation_keys()
    {
        $support = $this->getSupportClass(UserRelationFilter::class, 'page=1&f-profile.bio=qa');

        $this->assertTrue($support->has('f-profile.bio'));
        $this->assertTrue(resolve(\Illuminate\Http\Request::class)->hasAnyFilter(
            $this->buildFilter(UserRelationFilter::class, 'f-profile.bio=qa')
        ));
        $this->assertEquals('http://test.dev/?page=1', $support->remove('f-profile.bio'));
    }


    function test_has_many_relation_is_rejected_with_invalid_settings_exception()
    {
        $this->expectException(InvalidSettingsException::class);

        $filter = $this->buildFilter(UnsupportedHasManyFilter::class, 'f-posts.title=Alpha');
        FilterableUser::query()->filter($filter)->get();
    }
}

class FilterableUser extends Model
{
    use Filterable;

    protected $table = 'users';
    public $timestamps = false;
    protected $guarded = [];

    public function profile()
    {
        return $this->hasOne(FilterableProfile::class, 'user_id');
    }

    public function avatar()
    {
        return $this->morphOne(FilterableAvatar::class, 'imageable');
    }

    public function posts()
    {
        return $this->hasMany(FilterablePost::class, 'author_id');
    }
}

class FilterableProfile extends Model
{
    protected $table = 'profiles';
    public $timestamps = false;
    protected $guarded = [];
}

class FilterableAvatar extends Model
{
    protected $table = 'avatars';
    public $timestamps = false;
    protected $guarded = [];
}

class FilterablePost extends Model
{
    use Filterable;

    protected $table = 'posts';
    public $timestamps = false;
    protected $guarded = [];

    public function author()
    {
        return $this->belongsTo(FilterableUser::class, 'author_id');
    }
}

class UserRelationFilter extends Filter
{
    protected $filterables = [
        'username',
        'profile.bio',
        'profile.verified',
        'avatar.disk',
    ];
}

class PostRelationFilter extends Filter
{
    protected $filterables = [
        'title',
        'published',
        'author.username',
        'author-email' => 'author.email',
        'author.profile.bio',
    ];
}

class RestrictedRelationFilter extends Filter
{
    protected $filterables = [
        'author.username',
        'author-email' => 'author.email',
    ];

    protected function settings()
    {
        $this->for(['author.username'])->only(['=', '~']);
        $this->for(['author-email'])->except(['~']);
    }
}

class UnsupportedHasManyFilter extends Filter
{
    protected $filterables = [
        'posts.title',
    ];
}
