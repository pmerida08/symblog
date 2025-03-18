<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model as Eloquent;
use App\Models\Comment;

class Blog extends Eloquent
{
    protected $table = 'blog';
    protected $fillable = ['id','title','author','blog','image','tags','created_at', 'updated_at'];
   

    public function comment(){
        return $this->hasMany(Comment::class);
    }

    public function getComments() {
        $comments = [];
        foreach (Blog::find($this->id)->comment as $value2) {
            $comments[] = $value2;
        }
        return $comments;
    }

    public function numComments() {
        $num = 0;
        foreach (Blog::find($this->id)->comment as $value2) {
            $num++;
        }
        return $num;
    }
}