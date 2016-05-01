<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use AWS;

class ServiceImage extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'service_images';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['image', 'thumbnail'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];

    public function service()
    {
        // 1 admin can have one country
        return $this->belongsTo('App\Service');
    }

    public static function getMessages(){
        $messages =
        [
            'image.required' => 'La ruta de la imagen es obligatoria',
            'thumbnail.required' => 'La ruta del thumbnail es obligatoria',
            'service_id.required' => 'El ID del servicio es requerido',
        ];
        return $messages;
    }

    public static function getRules(){
        $rules = array(
                'image' => ['required'],
                'thumbnail' => ['required'],
                'service_id' => ['required'],
            );

        return $rules;
    }

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
