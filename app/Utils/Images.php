<?php

namespace App\Utils;

class Images {
  public static function uploadB64($imageRaw, $return = 'url', $filenamePrefix = '_') {
    if (!\Storage::exists($imageRaw)) {
      $imageRaw = str_replace(' ', '+', $imageRaw);
      
      $ext = [];
      preg_match('/^data:image\/(.{3,9});base64,/i', $imageRaw, $ext);
      
      if (count($ext) > 1) {
        $image = substr($imageRaw, strlen($ext[0]));
        $image = base64_decode($image);

        $imagePath = 'public/images/stores/' . $filenamePrefix . sha1($image) . '.' . $ext[1];
          \Storage::disk('local')->put($imagePath, $image);
          $imageUrl = \Storage::url($imagePath);

          return $imageUrl;
      }
    }
  }
}