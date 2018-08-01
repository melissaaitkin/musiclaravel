<?php

namespace MySounds;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Artist extends Model
{
	use SoftDeletes;

    protected $table = 'artists';

    protected $dates = ['deleted_at'];

}
