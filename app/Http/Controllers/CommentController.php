<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CommentController extends Controller
{
    //
    public function store(Request $request)
    {
        # code...
        $this->validate($request, [
 
            'comment' => 'required',
      
            'reply_id' => 'filled',
      
            'page_id' => 'filled',
      
            'users_id' => 'required',
      
        ]);
      
        $comment = Comment::create($request->all());
    
        if($comment)
    
            return [ "status" => "true","commentId" => $comment->id ];
    }
    public function update(Request $request, $commentId, $type)
    {
        # code...
        if($type == "vote") {
 
            $this->validate($request, [
  
            'vote' => 'required',
  
            'users_id' => 'required',
  
            ]);
  
  
  
            $comments = Comment::find($commentId);
  
            $data = [
  
                "comment_id" => $commentId,
  
                'vote' => $request->vote,
  
                'user_id' => $request->users_id,
  
            ];
  
  
            if($request->vote == "up"){
  
                $comment = $comments->first();
  
                $vote = $comment->votes;
  
                $vote++;
  
                $comments->votes = $vote;
  
                $comments->save();
  
            }
  
  
            if($request->vote == "down"){
  
                $comment = $comments->first();
  
                $vote = $comment->votes;
  
                $vote--;
  
                $comments->votes = $vote;
  
                $comments->save();
  
            }
  
  
            if(CommentVote::create($data))
  
                return "true";
  
        }
  
        if($type == "spam") {
           
            $this->validate($request, [
  
                'users_id' => 'required',
  
            ]);
  
            $comments = Comment::find($commentId);
  
            $comment = $comments->first();
  
            $spam = $comment->spam;
  
            $spam++;
  
            $comments->spam = $spam;
  
            $comments->save();
  
            $data = [
  
                "comment_id" => $commentId,
  
                'user_id' => $request->users_id,
  
            ];
  
  
  
            if(CommentSpam::create($data))
  
                return "true";
  
        }
    }
    public function index()
    {
        # code...
        $comments = Comment::where('page_id',$pageId)->get();
        $commentsData = [];
        
        
        foreach ($comments as $key) {
            $user = User::find($key->users_id);
            $name = $user->name;
            $replies = $this->replies($key->id);
            $photo = $user->first()->photo_url;
            // dd($photo->photo_url);
            $reply = 0;
            $vote = 0;
            $voteStatus = 0;
            $spam = 0;
            if(Auth::user()){
                $voteByUser = CommentVote::where('comment_id',$key->id)->where('user_id',Auth::user()->id)->first();
                $spamComment = CommentSpam::where('comment_id',$key->id)->where('user_id',Auth::user()->id)->first();
                
                if($voteByUser){
                    $vote = 1;
                    $voteStatus = $voteByUser->vote;
                }
                if($spamComment){
                    $spam = 1;
                }
            }
            
            if(sizeof($replies) > 0){
                $reply = 1;
            }
            if(!$spam){
                array_push($commentsData,[
                    "name" => $name,
                    "photo_url" => (string)$photo,
                    "commentid" => $key->id,
                    "comment" => $key->comment,
                    "votes" => $key->votes,
                    "reply" => $reply,
                    "votedByUser" =>$vote,
                    "vote" =>$voteStatus,
                    "spam" => $spam,
                    "replies" => $replies,
                    "date" => $key->created_at->toDateTimeString()
                ]);
            }    
            
        }
        $collection = collect($commentsData);
        return $collection->sortBy('votes');
    }
}
