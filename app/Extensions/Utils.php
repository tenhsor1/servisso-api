<?php
namespace App\Extensions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Contracts\Filesystem\Filesystem;

use GuzzleHttp\Client;
use GuzzleHttp\Exception;

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

    public static function requestJSON($url, $method='GET', $data = array(), $bodyJSON=true){
        try{
            $client = new Client();
            $body_data = array();
            if(count($data) > 0 ){
            	if($bodyJSON){
            		$body_data['json'] = $data;
            	}else{
            		$body_data['form_params'] = $data;
            	}
            }
            $res = $client->request($method, $url, $body_data);
            $status = $res->getStatusCode();
            $body = json_decode($res->getBody(), true);
            return ['status' => $status, 'body' => $body];
        }catch (Exception\ClientException $e) {
            $response = $e->getResponse();
            \Log::error('Error when requesting: '. $url . ' with method: ' . $method .
                        ' Status:' . $response->getStatusCode() . ' Response: '. $response->getBody());
            return ['status' => $response->getStatusCode(),
                    'body' => ['data' => json_decode($response->getBody())]];
        }
        catch (Exception\BadResponseException $e) {
            $response = $e->getResponse();
            \Log::error('Error when requesting: '. $url . ' with method: ' . $method .
                        ' Status:' . $response->getStatusCode() . ' Response: '. $response->getBody());
            return ['status' => $response->getStatusCode(),
                    'body' => ['data' => json_decode($response->getBody())]];
        }
        catch (Exception\ServerException $e) {
            $response = $e->getResponse();
            \Log::error('Error when requesting: '. $url . ' with method: ' . $method .
                        ' Status:' . $response->getStatusCode() . ' Response: '. $response->getBody());
            return ['status' => 500,
                    'body' => ['data' => 'Internal Server Error']];
        }catch (Exception\ServerException $e) {
            $response = $e->getResponse();
            \Log::error('Error when requesting: '. $url . ' with method: ' . $method .
                        ' Status:' . $response->getStatusCode() . ' Response: '. $response->getBody());
            return ['status' => 500,
                    'body' => ['data' => 'Internal Server Error']];
        }catch (Exception $e) {
            $response = $e->getResponse();
            \Log::error('Error when requesting: '. $url . ' with method: ' . $method .
                        '. Unexpected Error');
            return ['status' => 500,
                    'body' => ['data' => 'Internal Server Error']];
        }
    }

    public static function validateCaptcha($captcha, $ip){
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = ['secret' => \Config::get('app.recaptcha_secret'),
                'response' => $captcha,
                'remoteip' => $ip];

        $response = Utils::requestJSON($url, 'POST', $data, false);
        if($response['status'] >= 400){
            //if an error happened, then return a null and log the message from the google API
            \Log::error("Error getting the captcha status: ".$ip.
                        "failed.\nStatus: ".$response['status'].
                        "\nBody: ".json_encode($response['body']));
            return false;
        }
        try{
            $values = $response['body'];
            return $values['success'];

        }catch(\Exception $e){
            \Log::error("ErrorException getting the captcha status: ".$ip.
                        "failed.\nStatus: ".$response['status'].
                        "\nerror message: ".$e->getMessage().
                        "\nBody: ".json_encode($response['body']));
            return false;
        }
    }
	
	/**
		Se obtiene un parametro que funciona como bandera para saber si una url
		proviene de un email.
	*/
	public static function getFlagParameterEmail(){
		return 'svsemail='.substr(\Crypt::encrypt(microtime()),0,30);
	}
}