<?php 
namespace App\Extensions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Http\Requests;
/**
* Extension for create the new image of de company
*/
class Utils extends Model{
	 public static function StorageImage($imageName, $request, $path='/public/image/', $path_thumb='/public/thumb/'){   
		// Numero radom para evitar duplicidad de nombre de imagenes
		// $random = "img".rand(1,100);
		 $random = "img";
		//Ruta donde queremos guardar las imagenes
		$path = base_path().$path;
		$path_thumb = base_path().$path_thumb;
		//se obtiene el archivo
		$file = $request->file('image');
		//se crea un nombre para la imagen tama�o normal
		$imgName = $random.$imageName;
		//Creamos una instancia de la libreria instalada   
		$image = \Image::make($file);
	    // Guardar Original
	    $image->save($path.$imgName);
	    // Cambiar de tama�o
	    $image->resize(240,200);
	    // Guardar thumb
	    $image->save($path_thumb.'thumb_'.$imgName);
		
		return $img = array("image" => $path.$imgName,
							"thumbnail" => $path_thumb.$imgName);
    }
	

}