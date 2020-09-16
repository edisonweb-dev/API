<?php

namespace App\Http\Controllers;

use App\task;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class TaskController extends Controller{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(){

		$task = User::all();

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
	public function create(){}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request){

		$task = new Task();

		$validator = Validator::make($request->all(), [
			'name' => 'required',
			'description' => 'required',
			'content' => 'required',
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
		$task->description = $request->json('description');
		$task->content = $request->json('content');
		$task->imagen = $img_name;

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
	public function show(Request $request, User $user){

		$user = User::where('email', $request->email)->first();

		if ($user === null) {

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
	public function edit(task $task){
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\task  $task
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, task $task){

		$task = Task::find($request->json('id'));

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

			$name = explode("/", $imagen_edit);
			$img_edit = end($name);
			Storage::disk('local')->delete($img_edit);
		}

		$task->name = $request->json('name');
		$task->description = $request->json('description');
		$task->content = $request->json('content');
		$task->imagen = $img_name;

		$task->save();

		return response()->json([
			'success' => true,
			'code' => 304,
			'data' => $task
		], 200);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\task  $task
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Request $request, task $task){

		$task = Task::findOrFail($request->id);

		$imagen_edit = $task->imagen;

		if (!empty($imagen_edit)) {

			$name = explode("/", $imagen_edit);
			$img_name = end($name);
			Storage::disk('local')->delete($img_name);
		}

		$task = Task::destroy($request->id);

		return response()->json([
			'success' => true,
			'code' => 204,
			'data' => [$task]
		], 200);
	}


	public function getB64Image($base64_image){

		$image_service_str = substr($base64_image, strpos($base64_image, ",") + 1);

		$image = base64_decode($image_service_str);

		return $image;
	}

	public function getB64Extension($base64_image, $full = null){
		// Obtener mediante una expresión regular la extensión imagen y guardarla
		// en la variable "img_extension"        
		preg_match("/^data:image\/(.*);base64/i", $base64_image, $img_extension);
		// Dependiendo si se pide la extensión completa o no retornar el arreglo con
		// los datos de la extensión en la posición 0 - 1
		return ($full) ?  $img_extension[0] : $img_extension[1];
	}

	public function getImageB64($filename){
		//Obtener la imagen del disco creado anteriormente de acuerdo al nombre de
		// la imagen solicitada
		$file = Storage::disk('images_base64')->get($filename);
		// Retornar una respuesta de tipo 200 con el archivo de la Imagen
		return new Response($file, 200);
	}

}
