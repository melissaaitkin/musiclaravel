<?php

namespace App\Music\ClientToken;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ClientToken extends Model
{

    protected $table = 'client_token';

	/**
     * The primary key for the model.
     *
     * @var string
     */
    protected $client_id;

    /**
     * The token.
     *
     * @var string
     */
    protected $token;

    /**
     * Token expiry date.
     *
     * @var datetime
     */
    protected $expires;

    /**
     * Created date.
     *
     * @var datetime
     */
    protected $created_at;

    /**
     * Updated date.
     *
     * @var datetime
     */
    protected $updated_at;

	/**
	 * The attributes that aren't mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = ['expires'];

}