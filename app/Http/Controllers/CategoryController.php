<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\http\Response;
use App\Category;

class CategoryController extends Controller
{
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
    
}
