<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
 
class Code extends Model
{
   protected $table = 'codes';
   protected $fillable = ['type', 'value', 'status'];
}
 
?>