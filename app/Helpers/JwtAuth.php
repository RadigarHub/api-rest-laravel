<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;

class JwtAuth
{
    public $key;

    public function __construct() {
        $this->key = 'esto_es_una_clave_super_secreta-99887766';
    }

    public function signup($email, $password, $getData = null) {
        // Buscar si existe el usuario con sus credenciales
        $user = User::where([
            'email' => $email,
            'password' => $password
        ])->first();

        // Comprobar si son correctas (Si obtenemos objeto)
        $signup = false;
        if (is_object($user)) {
            $signup = true;
        }

        // Generar el token con los datos del usuario identificado
        if ($signup) {
            $token = array(
                'sub'       => $user->id,
                'email'     => $user->email,
                'name'      => $user->name,
                'surname'   => $user->surname,
                'iat'       => time(),  // Momento en que se creó el token
                'exp'       => time() + (7 * 24 * 60 * 60)  // Momento en el que el token expirará
            );

            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decode = JWT::decode($jwt, $this->key, array('HS256'));

            // Devolver los datos decodificados o el token, en función de un parámetro
            if (is_null($getData)) {
                $data = $jwt;
            } else {
                $data = $decode;
            }

        } else {
            $data = array(
                'status' => 'error',
                'message' => 'Login incorrecto'
            );
        }

        return $data;
    }
}