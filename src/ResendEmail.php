<?php

namespace Omnitask\SendEmailRepository;

use App\User;
use Illuminate\Database\Eloquent\Model;

class ResendEmail extends Model
{
    protected $fillable = [
        'user_id',
        'model_name',
        'model_id',
        'mailable_class',
        'mailable_id',
        'exception'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

}
