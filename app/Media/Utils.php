<?php

namespace App\Media;

use Spatie\MediaLibrary\Models\Media;

class Utils
{
    public static function getMediaPath(Media $media, string $size = 'full')
    {
        $storagePath = \Storage::disk('local')
            ->getDriver()
            ->getAdapter()
            ->getPathPrefix();

        $mediaPath = $media->getPath($size);
        $mediaPath = substr($mediaPath, strlen($storagePath . 'public'));
        return '/storage' . $mediaPath;
    }

    public static function getMediaPaths(Media $media, array $sizes)
    {
    }
}
