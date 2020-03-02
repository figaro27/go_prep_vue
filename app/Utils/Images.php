<?php

namespace App\Utils;
use Illuminate\Support\Facades\Storage;

class Images
{
    public static function uploadB64(
        $imageRaw,
        $return = 'url',
        $filenamePrefix = '_'
    ) {
        if (!\Storage::exists($imageRaw) && $imageRaw[0] !== '/') {
            $image = self::decodeB64($imageRaw);

            if ($image) {
                $ext = [];
                preg_match('/^data:image\/(.{3,9});base64,/i', $imageRaw, $ext);

                $imagePath =
                    'images/' . $filenamePrefix . sha1($image) . '.' . $ext[1];
                \Storage::disk('public')->put($imagePath, $image);

                if ($return === 'url') {
                    $imageUrl = \Storage::url($imagePath);
                    return $imageUrl;
                } elseif ($return === 'path') {
                    return $imagePath;
                }
            }
        }
    }

    public static function decodeB64($imageRaw)
    {
        $imageRaw = str_replace(' ', '+', $imageRaw);

        $ext = [];
        preg_match('/^data:image\/(.{3,9});base64,/i', $imageRaw, $ext);

        if (count($ext) > 1) {
            $image = substr($imageRaw, strlen($ext[0]));
            $image = base64_decode($image);
            return $image;
        }
        return null;
    }

    public static function encodeB64($path)
    {
        if ($path === null) {
            return;
        }
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }
}
