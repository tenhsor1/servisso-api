<?php
namespace App\Extensions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Http\Requests;
/**
* Extension for create the new image of de company
*/
class Utils extends Model{
	 public static function StorageImage($id, $file, $path='/public/images/', $pathThumb='/public/thumbs/'){
		//Ruta donde queremos guardar las imagenes
		$path = base_path().$path;
		$pathThumb = base_path().$pathThumb;
		if (!file_exists($path)){
		   mkdir($path, 0775, true);
		}
		if (!file_exists($pathThumb)){
		   mkdir($pathThumb, 0775, true);
		}
		$originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
		$originalName = str_replace(' ', '_', $originalName);
		$cleanName = preg_replace("/[^a-zA-Z0-9\-\_]/", "", $originalName);

		//Se obtiene la extension de la imagen
		$ext = $file->getClientOriginalExtension();
		//se obtiene la fecha en formato entero
		$time = strtotime('now');
		//se crea un nombre para la imagen tamaño normal y la reducida
		$imgName = $id."_".$cleanName."_".$time.".".$ext;
		$imgNameThumb = $imgName;
		//Creamos una instancia de la libreria instalada
		$image = \Image::make($file);
	    // Guardar Original
	    $image->save($path.$imgName);
	    // Cambiar de tamaño
	    $image->resize(240,200);
	    // Guardar thumb
	    $image->save($pathThumb.$imgNameThumb);

		return $img = array("image" => $imgName,"thumbnail" => $imgNameThumb);
    }


}