<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    public $table = 'Tournaments';
    
    public $primaryKey = 'id_tournament';
}
