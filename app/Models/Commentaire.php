<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Commentaire extends Model
{
    protected $fillable = ['post_id', 'id_commentaire', 'message_commentaire', 'nom_auteur', 'temps_creer'];

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id', 'post_id');
    }

    public function aiReplies()
    {
        return $this->hasMany(AiReply::class, 'id_commentaire', 'id_commentaire');
    }
}
