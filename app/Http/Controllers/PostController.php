<?php

namespace App\Http\Controllers;

use App\Events\NewPost;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function index()
    {
        return view( 'posts');
    }

    public function get_posts()
    {
        $posts = Post::with('user')->get();
        foreach( $posts as $post ){
            $post -> setAttribute("added_at" , $post -> created_at -> diffForHumans() ); 
        } 
        return response() -> json([
            "status" => "success",
            "data" => $posts 
        ]);
    }
    
    public function store(Request $request)
    {
        $rules = [
            "body"     => " required | min:28 | max:500 " ,
        ];
        $validator = Validator::make(  $request->all() , $rules  ); 

        if( $validator -> fails()) { 
            
            return response() -> json([
                "status" => "error",
                "msg"    => "validation error",
                "errors" => $validator->errors()  // return errors validator in array 
            ]);
            
        }else{
               
            try{

                $post = new Post();
                $post->body    = $request->body ;
                $post->user_id = Auth::user()->id ;
                $post->save();


                // set $data var to save post info in it
                $data = [
                    "post_data"   => $post ,
                    'user_info' => Auth::user() ,   
                ];

                // send $data in __construct() event NewNotification 
                event( new NewPost($data) ); 

                return response() -> json([
                    "status" => "success",
                    "msg"    => "messege sent successfully",
                    "data"   => $data ,
                ]);
                
            }catch( Exception $e ){

                return response() -> json([
                    "status" => "error",
                    "msg"    => "insert opration failed",
                ]);

            }

        }
    }

}
