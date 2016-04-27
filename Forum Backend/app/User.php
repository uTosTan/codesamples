<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use DB;

class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function topics()
    {
        return $this->hasMany('App\Topic');
    }

    public function posts()
    {
        return $this->hasMany('App\Post');
    }

    public function postsCount()
    {
        return $this->hasOne('App\Post')->select(DB::raw('user_id, count(user_id) as aggregate'))->groupBy('user_id');
    }

    public function getPostsCountAttribute()
    {
        if (!$this->relationLoaded('postsCount'))
            $this->load('postsCount');

        $related = $this->getRelation('postsCount');

        return ($related) ? (int) $related->aggregate : 0;
    }
}
