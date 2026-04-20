<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiReply extends Model
{
    protected $fillable = ['id_commentaire', 'tone', 'reply', 'generated_by'];

    public function commentaire()
    {
        return $this->belongsTo(Commentaire::class, 'id_commentaire', 'id_commentaire');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
