<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MessengerMessage extends Model
{
    protected $fillable = ['conversation_id', 'nom_expediteur', 'message', 'temps_envoi', 'type_message', 'statut'];

    public function conversation()
    {
        return $this->belongsTo(MessengerConversation::class, 'conversation_id', 'conversation_id');
    }
}
