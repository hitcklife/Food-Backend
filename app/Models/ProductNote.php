<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductNote extends Model
{
    use HasFactory;
    protected $table = 'transaction_notes';
    protected $guarded = ['id'];
    public $timestamps = false;
}
