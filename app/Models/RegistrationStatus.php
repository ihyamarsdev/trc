<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationStatus extends Model
{
    protected $fillable = ['registration_id','status_id','user_id',];

    public function registration() { return $this->belongsTo(RegistrationData::class, 'registration_id'); }
    public function status()       { return $this->belongsTo(Status::class); }
    public function user()         { return $this->belongsTo(User::class); }
}
