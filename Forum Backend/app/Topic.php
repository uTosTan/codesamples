<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    public function posts()
    {
    	return $this->hasMany('App\Post');
    }

    public function user()
    {
    	return $this->belongsTo('App\User');
    }

    public function postsCount()
    {
        return $this->hasOne('App\Post')->select(DB::raw('topic_id, count(topic_id) as aggregate'))->groupBy('topic_id');
    }

    public function getPostsCountAttribute()
    {
        if (!$this->relationLoaded('postsCount'))
            $this->load('postsCount');

        $related = $this->getRelation('postsCount');

        return ($related) ? (int) $related->aggregate : 0;
    }

    public function getPostsPaginatedAttribute()
    {
        return $this->posts()->paginate(10);
    }
}
