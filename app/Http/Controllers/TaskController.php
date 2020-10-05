<?php

namespace App\Http\Controllers;

use App\task;
use App\User;
use App\imagen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Namshi\JOSE\JWT;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;

class TaskController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{

		$task = DB::table('users')
			->join('imagens', 'users.idImagen', '=', 'imagens.id')
			->get();

		if (count($task) > 0) {

			return response()->json([
				'success' => true,
				'code' => 200,
				'data' => $task
			], 200);
		} else {

			return response()->json([
				'success' => false,
				'code' => 404,
				'data' => []
			], 404);
		}
	}

	
	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{

		$task = new User();
		$imagen = new imagen();

		$validator = Validator::make($request->all(), [
			'name' => 'required|max:255',
			'email' => 'required|email|max:255',
			'password' => 'required|min:8|max:255',
			'imagen' => 'required'
		]);

		if ($validator->fails()) {
			return response()->json([
				'success' => false,
				'code' => 404,
				'data' => []
			], 404);
		}


		$image_avatar_b64 = $request->json("imagen");

		$img = $this->getB64Image($image_avatar_b64);

		$img_extension = $this->getB64Extension($image_avatar_b64);

		$img_name = 'user_avatar' . time() . '.' . $img_extension;

		Storage::disk('local')->put($img_name, $img);

		$task->name = $request->json('name');
		$task->email = $request->json('email');
		$task->password = Hash::make($request->json('password'));


		$imagen->imagen = $img_name;
		$imagen->save();

		$id_imagen = imagen::latest('id')->first();
		$task->idImagen = $id_imagen->id;

		$task->save();

		return response()->json([
			'success' => true,
			'code' => 201,
			'data' => $task
		], 200);
	}



	/**
	 * Display the specified resource.
	 *
	 * @param  \App\task  $task
	 * @return \Illuminate\Http\Response
	 */
	public function show(Request $request, User $user)
	{
		$user = DB::table('users')
			->select('*')
			->join('imagens', 'users.idImagen', '=', 'imagens.id')
			->where('users.email', '=', $request->email)
			->get();

		if (count($user) < 1) {

			return response()->json([
				'success' => false,
				'code' => 404,
				'data' => []
			], 404);
		} else {

			return response()->json([
				'success' => true,
				'code' => 200,
				'data' => $user
			], 200);
		}
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \App\task  $task
	 * @return \Illuminate\Http\Response
	 */
	public function edit(task $task)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\task  $task
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, task $task)
	{

		$validator = Validator::make($request->all(), [
			'id' => 'required',
			'email' => 'required|email|max:255',
		]);

		if ($validator->fails()) {
			return response()->json([
				'success' => false,
				'code' => 404,
				'data' => []
			], 404);
		}


		$imagen = new imagen();

		$task = DB::table('users')
			->select('*')
			->join('imagens', 'users.idImagen', '=', 'imagens.id')
			->where('users.id', '=', $request->json('id'))
			->first();


		$validator = Validator::make($request->all(), [
			'id' => 'required',
		]);

		if ($validator->fails() || empty($task)) {

			return response()->json([
				'success' => false,
				'code' => 404,
				'data' => []
			], 404);
		}

		$image_avatar_b64 = $request->json("imagen");

		$img = $this->getB64Image($image_avatar_b64);

		$img_extension = $this->getB64Extension($image_avatar_b64);

		$img_name = 'user_avatar' . time() . '.' . $img_extension;

		Storage::disk('local')->put($img_name, $img);

		$imagen_edit = $task->imagen;

		if (!empty($url) && !empty($imagen_edit)) {

			Storage::disk('local')->delete($task->imagen);
			imagen::destroy($task->idImagen);
		}


		$imagen->imagen = $img_name;
		$imagen->save();

		$id_imagen = imagen::latest('id')->first();

		DB::table('users')
			->where('id', $request->json('id'))
			->update([
				'name' => $request->json('name'),
				'email' => $request->json('email'),
				'password' => Hash::make($request->json('password')),
				'idImagen' => $id_imagen->id
			]);

		$userEditado = User::where('id', $request->json('id'))->first();

		return response()->json([
			'success' => true,
			'code' => 304,
			'data' => $userEditado
		], 200);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\task  $task
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Request $request, task $task)
	{

		$task = DB::table('users')
			->select('*')
			->join('imagens', 'users.idImagen', '=', 'imagens.id')
			->where('users.id', '=', $request->id)
			->first();

		if ($task === null) {

			return response()->json([
				'success' => false,
				'code' => 404,
				'data' => []
			], 404);
		}


		if (!empty($task->imagen)) {

			Storage::disk('local')->delete($task->imagen);
		}

		User::destroy($request->id);
		imagen::destroy($task->idImagen);


		return response()->json([
			'success' => true,
			'code' => 204,
			'data' => []
		], 200);
	}

	public function login(Request $request)
	{

		$credentials = $request->only("email","password");

		$validator = Validator::make($request->all(), [
			'email' => 'required|email|max:255',
			'password' => 'required|max:255',
		]);

		if ($validator->fails()) {
			return response()->json([
				'success' => false,
				'code' => 404,
				'data' => []
			], 404);
		}

		
		$user = DB::table('users')
			->select('*')
			->join('imagens', 'users.idImagen', '=', 'imagens.id')
			->where('users.email', '=', $request->json('email'))
			->first();
		

		if ($user === null) {

			return response()->json([
				'success' => false,
				'code' => 404,
				'data' => 'usuario no existe'
			], 404);
		} else if (Hash::check($request->json('password'), $user->password)) {

			$token = JWTAuth::attempt($credentials);

			return response()->json([
				'success' => true,
				'code' => 200,
				'data' =>  [
					'message' => 'Ingreso usuario con exito',
					'token' => $token
				]
			], 200);

		} else {

			return response()->json([
				'success' => false,
				'code' => 404,
				'data' => 'contraseña incorrecta'
			], 404);
		}
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
