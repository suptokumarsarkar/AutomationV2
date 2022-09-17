<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DcConversation extends Model
{
    public function messages(){
        return $this->hasMany(Chat::class,'conversation_id');
    }
}
