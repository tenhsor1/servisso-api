<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use AWS;
use App\TaskImageHidden;
class TaskImage extends TaskImageHidden
{
    public function getImageAttribute($value){
        $s3Client = AWS::createClient('s3');
        $cmd = $s3Client->getCommand('GetObject', [
            'Bucket' => env('S3_IMAGES_BUCKET'),
            'Key'    => $value
        ]);

        $request = $s3Client->createPresignedRequest($cmd, '+5 minutes');
        $presignedUrl = (string) $request->getUri();

        return $presignedUrl;
    }

    public function getThumbnailAttribute($value){
         $s3Client = AWS::createClient('s3');
        $cmd = $s3Client->getCommand('GetObject', [
            'Bucket' => env('S3_IMAGES_BUCKET'),
            'Key'    => $value
        ]);

        $request = $s3Client->createPresignedRequest($cmd, '+5 minutes');
        $presignedUrl = (string) $request->getUri();

        return $presignedUrl;
    }
}
