<?php
namespace CheckStaticFile;
/**
 * Created by PhpStorm.
 * User: thanhnt
 * Date: 11/10/17
 * Time: 3:20 PM
 */

use App\User;

class CheckStaticFile
{
    public static function check()
    {
        $filePath = isset($_GET['file_path']) ? $_GET['file_path'] : '';

        $assetFolder = self::getAssetFolder();
        $attachmentLocation = public_path($assetFolder . DIRECTORY_SEPARATOR . $filePath);
        if (file_exists($attachmentLocation) && is_file($attachmentLocation))
        {
            header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
            header("Cache-Control: public"); // needed for internet explorer
            header("Content-Type: " . mime_content_type($attachmentLocation));
            header("Content-Length:" . filesize($attachmentLocation));
            readfile($attachmentLocation);
            die();
        }
    }

    public static function getAssetFolder()
    {
        $clientVersion = isset($_COOKIE['client_version']) ? $_COOKIE['client_version'] : config('view.client_version');

        $resourcePath = resource_path('views' . DIRECTORY_SEPARATOR . $clientVersion);
        $publicPath = public_path($clientVersion);

        // folder exist and not empty
        if ( !file_exists($resourcePath) || (count(scandir($resourcePath)) <= 2) || !file_exists($publicPath) || (count(scandir($publicPath)) <= 2) ) {
            $clientVersion = config('view.client_version');
        }

        self::setClientVersion('client_version', $clientVersion);

        return $clientVersion;
    }

    public static function setClientVersion($name, $value = "", $expire = (7 * 86400), $path = "/", $domain = "", $secure = false, $httpOnly = false)
    {
        $domain = ($domain != "") ? $domain : $_SERVER['SERVER_NAME'];
        $expire = time() + $expire;

        setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }

}