<?php
namespace App\Extensions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Contracts\Filesystem\Filesystem;

/**
* Extension for create the new image of de company
*/
class Utils extends Model{
	 public static function StorageImage($id, $file, $path='/images/', $pathThumb='/thumbs/', $type='private'){
		//Ruta donde queremos guardar las imagenes

		$originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
		$originalName = str_replace(' ', '_', $originalName);
		$cleanName = preg_replace("/[^a-zA-Z0-9\-\_]/", "", $originalName);

		//Se obtiene la extension de la imagen
		$ext = $file->getClientOriginalExtension();
		//se obtiene la fecha en formato entero
		$time = strtotime('now');
		//se crea un nombre para la imagen tamaño normal y la reducida
		$imgName = $id."_".$cleanName."_".$time.".".$ext;

		$imgPath = $path.$imgName;
		$imgPathThumb = $pathThumb.$imgName;
		//Creamos una instancia de la libreria instalada

		$s3 = \Storage::disk('s3Images');

		//first, upload it to the bucket with the real size
		$image = \Image::make($file);
		$image->encode($ext);
        $s3->put($imgPath, $image->__toString(), $type);


	    // Guardar Original
	    //$image->save($path.$imgName);
	    // Cambiar de tamaño
	    $image->resize(240,200);
	    $image->encode($ext);
	    $s3->put($imgPathThumb, $image->__toString(), $type);

	    // Guardar thumb
	    //$image->save($pathThumb.$imgNameThumb);

		return $img = array("image" => $imgPath,"thumbnail" => $imgPathThumb);
    }


}