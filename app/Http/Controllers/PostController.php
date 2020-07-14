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
            $user = $this->getIdentity($request);

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

    public function update($id, Request $request) {
        // Recoger los datos por post
        $json = $request->input('json', null);
        $params = json_decode($json, true);

        if (! empty($params)) {
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
                    'message' => 'No se ha actualizado la entrada'
                );
            } else {
                // Quitar los campos que no se quieren actualizar
                foreach ($params as $key => $val) {
                    if (!in_array($key, ['title', 'content', 'category_id', 'image', 'updated_at'])) {
                        unset($params[$key]);
                    }
                }

                // Conseguir el usuario identificado
                $user = $this->getIdentity($request);

                 // Buscar la entrada y ver si existe
                $post = Post::where('id', $id)
                            ->where('user_id', $user->sub)
                            ->first();

                if (!empty($post) && is_object($post)) {
                    // Actualizar entrada en la BD
                    $post->update($params);

                    /*
                    $where = array(
                        'id' => $id,
                        'user_id' => $user->sub
                    );
                    $post = Post::updateOrCreate($where, $params); // El método updateOrCreate actualiza o crea un objeto y lo devuelve como resultado
                    */

                    $data = array (
                        'code' => 200,
                        'status' => 'success',
                        'post' => $post,
                        'changes' => $params
                    );

                } else {
                    $data = array (
                        'code' => 400,
                        'status' => 'error',
                        'message' => 'La entrada no existe'
                    );
                }
            }
                
        } else {
            $data = array (
                'code' => 400,
                'status' => 'error',
                'message' => 'No has enviado ninguna entrada'
            );
        }
        
        // Devolver el resultado
        return response()->json($data, $data['code']);
    }

    public function destroy($id, Request $request) {
        // Conseguir el usuario identificado
        $user = $this->getIdentity($request);

        // Conseguir la entrada
        $post = Post::where('id', $id)
                    ->where('user_id', $user->sub)
                    ->first();

        // Comprobar que el objeto existe
        if (is_object($post)) {
            // Borrarla
            $post->delete();

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
        
        // Devolver el resultado
        return response()->json($data, $data['code']);
    }

    private function getIdentity(Request $request) {
        // Conseguir el usuario identificado
        $jwtAuth = new \JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);
        return $user;
    }
}
