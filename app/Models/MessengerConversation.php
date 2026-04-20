<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MessengerConversation extends Model
{
    protected $fillable = ['conversation_id', 'nom_expediteur', 'dernier_message', 'temps_dernier_message', 'nombre_messages', 'statut'];

    public function messages()
    {
        return $this->hasMany(MessengerMessage::class, 'conversation_id', 'conversation_id');
    }
}
