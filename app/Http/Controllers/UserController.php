<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;

class UserController extends Controller
{
    public function pruebas(): string
    {
        return 'accion de pruebas en user-controler';
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(\Request $request)
    {
        $data = [
            'status' => 'Error',
            'code' => 404,
            'message' => 'El usuario NO se ha creado',
        ];
        //  recoger los datos por post
        $json = $request->input('json', null); // llega en una unica key "json"
        $params = json_decode($json); // volcamos a un objeto
        $params_array = json_decode($json, true); //asi a un array

        // limpiar datos
        $params_array= array_map('trim', $params_array);

        //  Validar datos
        $validate = \Validator::make($params_array,
            [
                'name' => 'required|alfa',
                'surname' => 'required|alfa',
                'email' => 'required|email',
                'password' => 'required',

            ]);

        if ($validate->fails()) {
            $data = [
                'status' => 'Error',
                'code' => 400,
                'message' => 'Error validacion de campos',
                'errors'=> $validate->errors()

            ];
        } else{
            $data = [
                'status' => 'Success',
                'code' => 200,
                'message' => 'El usuario se ha creado correctamente',
                'errors'=> $validate->errors()
            ];
        }

        // todo cifrar contraseña

        // todo comprobar si existe

        // todo Crear usuario
        return response()->json($data, $data['code']);
    }

    /**
     * @return string
     */
    public function login()
    {
        return 'accion de login en user-controler';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $page = User::query()->paginate();

        return response()->json(compact('page'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateUserRequest $request)
    {
        $item = new User;
        $item->fill($request->validated());
        $item->save();

        return response()->json(compact('item'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $item = User::query()->findOrFail($id);

        return response()->json(compact('item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, UpdateUserRequest $request)
    {
        $item = User::query()->findOrFail($id);
        $item->update($request->validated());

        return response()->json(compact('item'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        return response()->json('Error', 400);
    }
}
