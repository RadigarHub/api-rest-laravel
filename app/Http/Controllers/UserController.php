<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'messsage' => 'El usuario se ha creado correctamente'
                );

                // Cifrar la constraseña

                // Crear el usuario

                // Guardar el usuario en la BD
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
        return 'Acción de login de usuario';
    }
}
