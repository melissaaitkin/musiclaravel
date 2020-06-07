<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

use Storage;

class SettingsController extends Controller
{

    /**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		return view('settings', ['media_directory' => Redis::get('media_directory')]);
	}

	/**
	 * Save settings
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function settings(Request $request)
	{
		try {

			if (!empty($request->media_directory)) {
				if (Storage::disk(config('filesystems.partition'))->has($request->media_directory)) {
					Redis::set('media_directory', $request->media_directory);
				} else {
					return view('settings', ['media_directory' => $request->media_directory])
						->withErrors(['This is not a valid directory']);
				}
			}

		} catch (Exception $e) {
			return view('settings')->withErrors([$e->getMessage()]);
		}
		return view('settings', [
			'msg' => 'Settings have been saved',
			'media_directory' => Redis::get('media_directory')]);
	}


}