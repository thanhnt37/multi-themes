<?php

namespace App\Http\Controllers;

use CheckStaticFile;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index()
    {
        $clientVersion = CheckStaticFile\CheckStaticFile::getAssetFolder();
        
        return file_get_contents(resource_path('views' . '/' . $clientVersion . '/index.html'));
    }

    public function uploadPackage(Request $request)
    {
        $version = $request->get('version', '');
        if( ($version != '') && $request->hasFile('client_package') ) {
            $file = $request->file('client_package');

            $storagePath  = storage_path('packages');
            if (!is_dir($storagePath)) {
                mkdir($storagePath);
            }

            $packagePath  = $storagePath . DIRECTORY_SEPARATOR . $version . '.' . $file->extension();
            $resourcePath = resource_path('views' . DIRECTORY_SEPARATOR . $version);
            $publicPath   = public_path($version);

            move_uploaded_file( $file->getPathname(), $packagePath );

            $this->unZipFile($packagePath, $resourcePath);
            $this->makeSymlink($resourcePath, $publicPath);

            return $this->_response(200, '', ['path' => $packagePath]);
        }

        return $this->_response(400, 'Error, Parameter is invalid !!!', []);
    }


    private function makeSymlink($resourceFolder, $publicFolder)
    {
        if ( !file_exists($publicFolder) || (count(scandir($publicFolder)) <= 2) ) {
            $script = 'ln -s ' . $resourceFolder . ' ' . $publicFolder;
            shell_exec($script);

            return true;
        }
        return false;
    }

    private function unZipFile($path, $destination)
    {
        if( file_exists($path) ) {
            $zip = new \ZipArchive();

            if ($zip->open($path) === TRUE) {
                $zip->extractTo($destination);
                $zip->close();

                return true;
            }
        }

        return false;
    }

    private function _response($code = 200, $message = '', $data = null)
    {
        $response['code']    = $code;
        $response['message'] = $message;
        $response['data']    = $data;

        return response()->json($response)->setStatusCode($code)
            ->withHeaders([
                'Access-Control-Allow-Credentials'  => 'true',
                'Access-Control-Allow-Origin'  => 'http://cloud.dev:3000',
                'Access-Control-Allow-Methods' => 'GET, POST, PATCH, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'accept, content-type, x-xsrf-token, x-csrf-token'
            ]);
    }
}
