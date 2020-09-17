<?php

namespace  App\Http\Controllers;

use App\Http\Requests\RegisterAuthRequest;
use App\User;
use Illuminate\Http\Request;
use  JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\imagen;
use Illuminate\Support\Facades\Storage;

class  AuthController extends  Controller
{
	public  $loginAfterSignUp = true;

	public function ingresar(){
		return "prueba mensaje";
	}

	public  function  register(Request  $request)
	{
		
		$imagen = new imagen();
		$user = new  User();

		$user->name = $request->json("name");
		$user->email = $request->json("email");
		$user->password = bcrypt($request->json("password"));

		$image_avatar_b64 = $request->json("imagen");

		$img = $this->getB64Image($image_avatar_b64);

		$img_extension = $this->getB64Extension($image_avatar_b64);

		$img_name = 'user_avatar' . time() . '.' . $img_extension;

		Storage::disk('local')->put($img_name, $img);

		$imagen->imagen = $img_name;
		$imagen->save();

		$id_imagen = imagen::latest('id')->first();
		$user->idImagen = $id_imagen->id;

		$user->save();

		/* if ($this->loginAfterSignUp) {
			return  $this->login($request);
		} */

		return  response()->json([
			'status' => 'ok',
			'data' => $user
		], 200);
	}

	public  function  login(Request  $request)
	{
		$input = $request->only('email', 'password');
		$jwt_token = null;
		if (!$jwt_token = JWTAuth::attempt($input)) {
			return  response()->json([
				'status' => 'invalid_credentials',
				'message' => 'Correo o contraseña no válidos.',
			], 401);
		}

		return  response()->json([
			'status' => 'ok',
			'token' => $jwt_token,
		]);
	}

	public  function  logout(Request  $request)
	{
		$this->validate($request, [
			'token' => 'required'
		]);

		try {
			JWTAuth::invalidate($request->token);
			return  response()->json([
				'status' => 'ok',
				'message' => 'Cierre de sesión exitoso.'
			]);
		} catch (JWTException  $exception) {
			return  response()->json([
				'status' => 'unknown_error',
				'message' => 'Al usuario no se le pudo cerrar la sesión.'
			], 500);
		}
	}

	public  function  getAuthUser(Request  $request)
	{
		$this->validate($request, [
			'token' => 'required'
		]);

		$user = JWTAuth::authenticate($request->token);
		return  response()->json(['user' => $user]);
	}

	public function getB64Image($base64_image)
	{

		$image_service_str = substr($base64_image, strpos($base64_image, ",") + 1);

		$image = base64_decode($image_service_str);

		return $image;
	}

	public function getB64Extension($base64_image, $full = null)
	{
		// Obtener mediante una expresión regular la extensión imagen y guardarla
		// en la variable "img_extension"        
		preg_match("/^data:image\/(.*);base64/i", $base64_image, $img_extension);
		// Dependiendo si se pide la extensión completa o no retornar el arreglo con
		// los datos de la extensión en la posición 0 - 1
		return ($full) ?  $img_extension[0] : $img_extension[1];
	}

	public function getImageB64($filename)
	{
		//Obtener la imagen del disco creado anteriormente de acuerdo al nombre de
		// la imagen solicitada
		$file = Storage::disk('images_base64')->get($filename);
		// Retornar una respuesta de tipo 200 con el archivo de la Imagen
		return new Response($file, 200);
	}
}
