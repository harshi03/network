<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
 
class User extends Model
{
   protected $table = 'users';
   protected $fillable = ['name', 'mobile', 'email', 'password', 'age', 'gender', 'id_district', 'pan_card','id_vehicle', 'total_vehicle', 'total_male', 'total_female', 'type', 'guid', 'username'];
}
 
?>