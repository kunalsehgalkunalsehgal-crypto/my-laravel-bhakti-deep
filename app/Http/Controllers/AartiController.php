<?php

namespace App\Http\Controllers;

use App\Models\Admin\Deity;

class AartiController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | AARTI LIST
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $deities = Deity::with(
                'aartiAudio'
            )
            ->where(
                'status',
                'active'
            )
            ->whereNotNull(
                'aarti_video'
            )
            ->orderBy(
                'name'
            )
            ->get();


        return view(
            'pages.aarti.index',
            compact('deities')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | AARTI SESSION
    |--------------------------------------------------------------------------
    */

    public function session(
        string $slug
    ) {

        $deity = Deity::with(
                'aartiAudio'
            )
            ->where(
                'slug',
                $slug
            )
            ->where(
                'status',
                'active'
            )
            ->whereNotNull(
                'aarti_video'
            )
            ->firstOrFail();


        return view(
            'pages.aarti.session',
            compact('deity')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | OLD URL REDIRECT
    |--------------------------------------------------------------------------
    */

    public function show(
        string $slug
    ) {

        return redirect()->route(
            'aarti.session',
            [
                'slug' => $slug
            ]
        );
    }
}