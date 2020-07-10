<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class UserController extends Controller
{
    public function pruebas(Request $request) {
        return "Acción de pruebas de USER-CONTROLLER";
    }

    public function register(Request $request) {
        // Recoger los datos del usuario por post. Ej: {"name":"Rafael", "surname":"Díaz", "email":"rafa@rafa.com", "password":"rafa"}
        $json = $request->input('json', null);
        $params = json_decode($json, true); // Con el true se devuelve un array en vez de un objeto

        // Comprobar si los datos se reciben en un formato correcto
        if (! empty($params)){
            
            // Limpiar los datos
            $params = array_map('trim', $params);

            // Validar los datos y comprobar si el usuario ya existe (duplicado)
            $validate = \Validator::make($params, [
                'name'      => 'required|alpha',
                'surname'   => 'required|alpha',
                'email'     => 'required|email|unique:users',
                'password'  => 'required'
            ]);

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'messsage' => 'El usuario no se ha creado',
                    'errors' => $validate->errors()
                );
            } else {
                // Cifrar la contraseña
                $pwd = hash('sha256', $params['password']);

                // Crear el usuario
                $user = new User();
                $user->name = $params['name'];
                $user->surname = $params['surname'];
                $user->email = $params['email'];
                $user->password = $pwd;
                $user->role = 'ROLE_USER';

                // Guardar el usuario en la BD
                $user->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'messsage' => 'El usuario se ha creado correctamente',
                    'user' => $user
                );
            }

        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'messsage' => 'Los datos enviados no son correctos'
            );
        }
        
        // Devolver un mensaje indicando el resultado
        return response()->json($data, $data['code']);
    }

    public function login(Request $request) {
        $jwtAuth = new \JwtAuth();

        // Recibir los datos por POST
        $json = $request->input('json', null);
        $params = json_decode($json, true);

        // Comprobar si los datos se reciben en un formato correcto
        if (! empty($params)){
            // Validar los datos
            $validate = \Validator::make($params, [
                'email'     => 'required|email',
                'password'  => 'required'
            ]);

            if ($validate->fails()) {
                $signup = array(
                    'status' => 'error',
                    'code' => 404,
                    'messsage' => 'El usuario no se ha podido identificar',
                    'errors' => $validate->errors()
                );
                $code = 404;
            } else {
                // Cifrar la password
                $pwd = hash('sha256', $params['password']);

                // Devolver token o datos
                $signup = $jwtAuth->signup($params['email'], $pwd);
                
                if (!empty($params['getIdentity'])) {
                    $signup = $jwtAuth->signup($params['email'], $pwd, true);
                }
                $code = 200;
            }
        } else {
            $signup = array(
                'status' => 'error',
                'code' => 404,
                'messsage' => 'Los datos enviados no son correctos'
            );
            $code = 404;
        }

        // Devolver una respuesta
        return response()->json($signup, $code);
    }

    public function update(Request $request) {
        // Comprobar si el usuario está identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        // Recoger los datos por POST
        $json = $request->input('json', null);
        $params = json_decode($json, true);

        if ($checkToken && !empty($params)) {
            // Obtener el usuario identificado
            $user = $jwtAuth->checkToken($token, true);

            // Validar los datos
            $validate = \Validator::make($params, [
                'name'      => 'required|alpha',
                'surname'   => 'required|alpha',
                'email'     => 'required|email|unique:users,email,'.$user->sub // El validator comprueba que el email no se repita salvo para el mismo usuario que se está actualizando
            ]);

            if ($validate->fails()) {
                // Devolver mensaje de error
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'El usuario no está identificado'
                );
            } else {
                // Quitar los campos que no se quieren actualizar
                foreach ($params as $key => $val) {
                    if (!in_array($key, ['name', 'surname', 'email', 'description', 'image', 'updated_at'])) {
                        unset($params[$key]);
                    }
                }

                // Actualizar usuario en la BD
                $user_update = User::where('id', $user->sub)->update($params);

                // Devolver el resultado
                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'user' => $user,
                    'changes' => $params
                );
            }

        } else {
            // Devolver mensaje de error
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'El usuario no está identificado'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function upload(Request $request) {
        //Recoger los datos de la petición
        $image = $request->file('file0');

        // Validar la imagen
        $validate = \Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        // Guardar la imagen
        if (!$image || $validate->fails()) {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir imagen'
            );
        } else {
            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image));

            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            );
        }

        // Devolver respuesta
        return response()->json($data, $data['code']);
    }
}
