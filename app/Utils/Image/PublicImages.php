<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-11
 * Time: 下午6:50
 */

namespace App\Utils\Image;


use YunInternet\CCMSCommon\Constants\Constants;

abstract class PublicImages
{
    public static function scan()
    {
        $imageList = [];

        foreach (self::scanImages() as $image) {
            $imageList[$image] = self::scanVersions($image);
        }

        return $imageList;
    }

    /**
     * @return array
     */
    public static function scanImages()
    {
        $images = [];

        if (!is_dir(Constants::PUBLIC_IMAGE_DIRECTORY))
            mkdir(Constants::PUBLIC_IMAGE_DIRECTORY, 0755, true);

        $imageDirectories = scandir(Constants::PUBLIC_IMAGE_DIRECTORY);
        foreach ($imageDirectories as $imageDirectory) {
            $fullPath = Constants::PUBLIC_IMAGE_DIRECTORY . "/" . $imageDirectory;
            if ($imageDirectory[0] === ".")
                continue;
            if (!is_dir($fullPath))
                continue;
            $images[] = $imageDirectory;
        }

        return $images;
    }

    /**
     * @param string $image
     * @return array
     */
    public static function scanVersions($image)
    {
        $versions = [];

        $imageDirectory = Constants::PUBLIC_IMAGE_DIRECTORY . "/" . $image;

        $imageVersions = scandir($imageDirectory);
        foreach ($imageVersions as $imageVersion) {
            $fullPath = $imageDirectory . "/" . $imageVersion;
            if ($imageVersion[0] === ".")
                continue;
            if (!is_file($fullPath))
                continue;
            if (!is_numeric($imageVersion))
                continue;
            $versions[$imageVersion] = realpath($fullPath);
        }

        return $versions;
    }
}