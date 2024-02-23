<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Helpers\JwtAuthHelper;
class UserController extends Controller
{
    public function pruebas(): string
    {
        return 'accion de pruebas en user-controler';
    }

    public function register(Request $request): JsonResponse
    {
        $data = [
            'status' => 'Error',
            'code' => 404,
            'message' => 'El usuario NO se ha creado',
        ];
        //  recoger los datos por post
        $json = $request->input('json', null); // llega en una unica key "json"
        try {
            $params = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
        } // volcamos a un objeto
        try {
            $params_array = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
        } //asi a un array

        // limpiar datos
        $params_array = array_map('trim', $params_array);

        //  Validar datos
        $validate = \Validator::make($params_array,
            [
                'name' => 'required|alpha',
                'surname' => 'required|alpha',
                'email' => 'required|email|unique:users', // validacion unigue con referencia a la tabla
                'password' => 'required',

            ]);

        if ($validate->fails()) {
            //si la validacion a fallado
            $data = [
                'status' => 'Error',
                'code' => 400,
                'message' => 'Error validacion de campos',
                'errors' => $validate->errors(),
            ];
        } else {
            // si ha pasado la validacion
            //  cifrar contraseña
            $password_hash = password_hash($params_array['password'], PASSWORD_BCRYPT, ['cost' => 4]);

            // Crear usuario
            $user = new User;
            $user->name = $params_array['name'];
            $user->surname = $params_array['surname'];
            $user->email = $params_array['email'];
            $user->password = $password_hash;
            $user->role = 'ROLE_USER';
            // guardar usuario
            $user->save();

            $data = [
                'status' => 'Success',
                'code' => 200,
                'message' => 'El usuario se ha creado correctamente',
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function login(Request $request): string
    {
        $jwtAuth = new JwtAuthHelper();
        echo $jwtAuth->signup();

        return 'accion de login en user-controler';
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $page = User::query()->paginate();

        return response()->json(compact('page'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        $item = new User;
        $item->fill($request->validated());
        $item->save();

        return response()->json(compact('item'));
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $item = User::query()->findOrFail($id);

        return response()->json(compact('item'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $id, UpdateUserRequest $request): JsonResponse
    {
        $item = User::query()->findOrFail($id);
        $item->update($request->validated());

        return response()->json(compact('item'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        return response()->json('Error', 400);
    }
}
