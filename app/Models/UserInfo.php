<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInfo extends Model
{
    use HasFactory;
    protected $table = 'users_info';
    protected $guarded = [''];
    public $timestamps = false;

    public function company()
    {
        return $this->hasOne(UserCompany::class, 'id', 'company_id');
    }
}
