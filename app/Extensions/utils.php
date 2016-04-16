<?php 
namespace App\Extensions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Http\Requests;
/**
* Extension for create the new image of de company
*/
class Utils extends Model{
	 public static function StorageImage($id, $request, $path='\\public\image\\', $path_thumb='\\public\thumb\\'){   
		$carpeta = '../../servisso-api/public/image';
		if (!file_exists($carpeta)) {
		   mkdir($carpeta, 0777, true);
		} 
		$carpeta = '../../servisso-api/public/thumb';
		if (!file_exists($carpeta)) {
		   mkdir($carpeta, 0777, true);
		} 
		//Ruta donde queremos guardar las imagenes
		$path = base_path().$path;
		$path_thumb = base_path().$path_thumb;
		//se obtiene el archivo
		$file = $request->file('image');
		//Se obtiene la extension de la imagen
		//$ext = $file->getClientOriginalExtension();
		$ext = 'png';
		//se obtiene la fecha en formato entero
		$time = strtotime('now');
		//se crea un nombre para la imagen tama�o normal y la reducida
		$imgName = "img".$id."_".$time.".".$ext;
		$imgName_thumb = "img".$id."_thumb_".$time.".".$ext;
		//Creamos una instancia de la libreria instalada   
		$image = \Image::make($file);
	    // Guardar Original
	    $image->save($path.$imgName);
	    // Cambiar de tama�o
	    $image->resize(240,200);
	    // Guardar thumb
	    $image->save($path_thumb.$imgName_thumb);
		
		// return $img = array("image" => $path.$imgName,"thumbnail" => $path_thumb.$imgName_thumb);
		return $img = array("image" => $imgName,"thumbnail" => $imgName_thumb);
    }
	

}