<?php

namespace App\Http\Controllers;

use CheckStaticFile;

class IndexController extends Controller
{
    public function index()
    {
        $clientVersion = CheckStaticFile\CheckStaticFile::getAssetFolder();
        return view($clientVersion . '.index');
    }
}
