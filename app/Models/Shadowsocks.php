<?php

namespace App\Models;

use App\Models\User;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shadowsocks extends Model
{
    use SoftDeletes;

    protected $table = 'shadowsocks';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
