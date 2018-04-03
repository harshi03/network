<?php

namespace App\Middleware;

class Middleware extends Model
{
   protected $container;

   public function __construct($container)
   {
   		$this->container = $container;
   }
   
}
 
?>