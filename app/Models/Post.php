<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['post_id', 'message_post', 'image_url', 'temps_creer_post', 'lien'];

    public function commentaires()
    {
        return $this->hasMany(Commentaire::class, 'post_id', 'post_id');
    }
}
