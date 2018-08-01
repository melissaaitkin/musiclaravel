<?php

namespace MySounds;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Song extends Model
{
	use SoftDeletes;

    protected $table = 'songs';

    protected $dates = ['deleted_at'];

}
