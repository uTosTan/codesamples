<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Topic;
use App\Post;
use App\User;
use JWTAuth;
use Validator;

class TopicController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth', ['except' => ['index', 'show', 'showPosts']]);

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $topics = Topic::with('user')->orderBy('created_at', 'desc')->paginate(10);
        $data = $topics->map(function($item, $key) {
            $item->postsCount;

            return $item;
        });

        return response()->json(['topics' => $data])->header('X-Page-Total', ceil($topics->total()/10))->header('X-Page', $topics->currentPage());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $token = JWTAuth::getToken();
        $user = JWTAuth::toUser($token);

        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'content' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $topic = new Topic;
        $topic->title = $request->title;
        $topic->user()->associate($user);
        $topic->save();

        $post = new Post;
        $post->content = $request->content;
        $post->topic()->associate($topic);
        $post->user()->associate($user);
        $post->save();

        return response()->json(['topic' => $topic, 'post' => $post], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $topic = Topic::find($id);

        return response()->json(['topic' => $topic]);
    }
    
    public function showPosts($id)
    {
        $topic = Topic::find($id);
        $posts = $topic->posts_paginated;
        $data = $posts->map(function($item, $key) {
            $item->user = User::find($item->user_id);
            $item->user->postsCount;

            return $item;
        });
        
        return response()->json(['posts' => $data])->header('X-Page-Total', ceil($posts->total()/10))->header('X-Page', $posts->currentPage());
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
