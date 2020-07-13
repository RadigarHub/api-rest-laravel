<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\http\Response;
use App\Post;

class PostController extends Controller
{
    public function pruebas(Request $request) {
        return "Acción de pruebas de POST-CONTROLLER";
    }

    public function __construct() {
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    public function index() {
        $posts = Post::all()->load('category'); // El método load adjunta un objeto a otro

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'posts' => $posts
        ], 200);
    }

    public function show($id) {
        $post = Post::find($id)->load('category');

        if (is_object($post)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'post' => $post
            );
        } else {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'La entrada no existe'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function store(Request $request) {
        // Recoger los datos por post
        $json = $request->input('json', null);
        $params = json_decode($json, true);

        if (! empty($params)){
            // Conseguir el usuario identificado
            $jwtAuth = new \JwtAuth();
            $token = $request->header('Authorization', null);
            $user = $jwtAuth->checkToken($token, true);

            // Validar los datos
            $validate = \Validator::make($params, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required',
                'image' => 'required'
            ]);

            if ($validate->fails()) {
                $data = array (
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha guardado el post, faltan datos'
                );
            } else {
                // Guardar la entrada
                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $params['category_id'];
                $post->title = $params['title'];
                $post->content = $params['content'];
                $post->image = $params['image'];
                $post->save();

                $data = array (
                    'code' => 200,
                    'status' => 'success',
                    'post' => $post
                );
            }

        } else {
            $data = array (
                'code' => 400,
                'status' => 'error',
                'message' => 'No se han enviado los datos correctamente'
            );
        }

        // Devolver el resultado
        return response()->json($data, $data['code']);
    }
}
