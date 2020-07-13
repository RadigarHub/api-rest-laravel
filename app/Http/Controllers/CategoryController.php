<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\http\Response;
use App\Category;

class CategoryController extends Controller
{
    public function __construct() {
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    public function pruebas(Request $request) {
        return "Acción de pruebas de CATEGORY-CONTROLLER";
    }

    public function index() {
        $categories = Category::all();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'categories' => $categories
        ]);
    }

    public function show($id) {
        $category = Category::find($id);

        if (is_object($category)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'category' => $category
            );
        } else {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'La categoría no existe'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function store(Request $request) {
        // Recoger los datos por post
        $json = $request->input('json', null);
        $params = json_decode($json, true);

        if (! empty($params)){
            // Validar los datos
            $validate = \Validator::make($params, [
                'name' => 'required|unique:categories'
            ]);

            // Guardar la categoría
            if ($validate->fails()) {
                $data = array (
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha guardado la categoría'
                );
            } else {
                $category = new Category();
                $category->name = $params['name'];
                $category->save();
                
                $data = array (
                    'code' => 200,
                    'status' => 'success',
                    'category' => $category
                );
            }
        } else {
            $data = array (
                'code' => 400,
                'status' => 'error',
                'message' => 'No has enviado ninguna categoría'
            );
        }

        // Devolver el resultado
        return response()->json($data, $data['code']);
    }

    public function update ($id, Request $request) {
        // Recoger los datos por post
        $json = $request->input('json', null);
        $params = json_decode($json, true);

        if (! empty($params)) {
            // Validar los datos
            $validate = \Validator::make($params, [
                'name' => 'required|unique:categories,name,'.$id // El validator comprueba que el nombre no se repita salvo para la misma categoría que se está actualizando
            ]);

            // Quitar los campos que no se quieren actualizar
            foreach ($params as $key => $val) {
                if (!in_array($key, ['name', 'updated_at'])) {
                    unset($params[$key]);
                }
            }

            // Actualizar categoría en la BD
            $category = Category::where('id', $id)->update($params);

            $data = array (
                'code' => 200,
                'status' => 'success',
                'category' => $params
            );

        } else {
            $data = array (
                'code' => 400,
                'status' => 'error',
                'message' => 'No has enviado ninguna categoría'
            );
        }

        // Devolver el resultado
        return response()->json($data, $data['code']);
    }
}
