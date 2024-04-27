<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErorLog extends Model
{
    use HasFactory;
    protected $table = 'erorlog';

    protected $fillable = [
        'eror_code', 'eror_desc'
    ];
}
