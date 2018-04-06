<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
 
class Signature extends Model
{
   protected $table = 'signatures';
   protected $fillable = ['id_district', 'file_name', 'from_', 'to_', 'total_sheet', 'contact_name', 'contact_mobile', 'total_college', 'total_student', 'total_teacher', 'total_general', 'total_representative', 'total_exoffice'];
}
 
?>