<?php

namespace App\Http\Controllers\API;

use App\Models\Image;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $file = $request->file('file');
        if(!$file->isValid())
            abort(403, "upload failed!");

        $ext = $file->getClientOriginalExtension();
        if(!isLegalExt($ext)) return [
            'status'    =>      false,
            'error'     =>      'invalid extension',
        ];
        
        $path = $file->store('public/tmp');
        $img = Storage::get($path);
        Storage::delete($path);

        $key = uniqid();
        cache([$key => $img], 30);
        return [
            'name' => $key,
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show($key)
    {
        return cache($key);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function destroy($key)
    {
        Cache::forget($key);
    }
}

function isLegalExt($ext) {
    $arr = [
        'jpg', 'jpeg', 'png',
    ];
    if(in_array($ext, $arr))
        return true;
    else return false;
}