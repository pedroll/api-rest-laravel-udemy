<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function pruebas(): string
    {
        return 'accion de pruebas en user-controler';
    }

    /**
     * @return JsonResponse
     */
    public function register(Request $request)
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
        $params_array = array_map('trim', $params_array);

        //  Validar datos
        $validate = Validator::make($params_array,
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

            // todo Crear usuario
            $user = new User;
            $user->name = $params_array['name'];
            $user->surname = $params_array['surname'];
            $user->email = $params_array['email'];
            $user->password = $password_hash;

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
     * @return JsonResponse
     */
    public function index()
    {
        $page = User::query()->paginate();

        return response()->json(compact('page'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return JsonResponse
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
     * @return JsonResponse
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
     * @return JsonResponse
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
     * @return JsonResponse
     */
    public function destroy($id)
    {
        return response()->json('Error', 400);
    }
}
