<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
 
class UserVehicle extends Model
{
   protected $table = 'user_vehicles';
   protected $fillable = ['id_vehicle', 'id_user', 'total_vehicle', 'total_male', 'total_female'];
}
 
?>