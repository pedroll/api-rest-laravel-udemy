<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuthHelper;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends ApiController
{
    public function pruebas(): string
    {
        return 'accion de pruebas en user-controler';
    }

    public function register(Request $request): JsonResponse
    {
        //  recoger los datos por post
        $json = $request->input('json', null); // llega en una unica key "json"
        try {
            $params = json_decode($json, false, 512, JSON_THROW_ON_ERROR); // volcamos a un objeto
            $params_array = json_decode($json, true, 512, JSON_THROW_ON_ERROR); // asi a un array
        } catch (\JsonException $e) {
            // Handle JSON decoding errors here
            $data = [
                'message' => $e->getMessage(),
                'errors' => $e,
            ];

            return $this->sendError('El usuario NO se ha creado', $data['errors']);
        }

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
            return $this->sendError('Error validacion de campos', $validate->errors(), 422);
        }

        // si ha pasado la validacion
        //  cifrar contraseña
        //$password_hash = password_hash($params_array['password'], PASSWORD_BCRYPT, ['cost' => 4]);
        //$password_hash = hash('sha256', $params_array['password']);
        // Crear usuario

        //$params_array['password'] = $password_hash;
        $params_array['role'] = 'ROLE_USER';

        // solo funciona con fillables
        $user = new User;
        $user->fill($params_array);
        $user->save();

        return $this->sendResponse(['user' => $user], 'El usuario se ha creado correctamente');
    }

    public function login(Request $request): string
    {
        $jwtAuth = new JwtAuthHelper();

        // recibir datos por post
        $json = $request->input('json', null); // llega
        // $en una unica key "json"
        try {
            $params = json_decode($json, false);
            // volcamos a un objeto
            $params_array = json_decode($json, true);
            // valdar datos
            $validate = \Validator::make($params_array,
                [
                    'email' => 'required|email',
                    'password' => 'required',
                ]);
            if ($validate->fails()) {
                //si la validacion a fallado
                return $this->sendError('Validation Error.', $validate->errors(), 422);
            }
            //cifrar la password
            //$password_hash = password_hash($params_array['password'], PASSWORD_BCRYPT, ['cost' => 4]);
            //             $password_hash = hash('sha256', $params_array['password']);            // devolver datos o token
            $testUser = new User;
            $testUser->fill($params_array);
            if (property_exists($params, 'getToken') and $params->getToken) {
                $data = $jwtAuth->signup($params->email, $params_array['password'], true);
                //$data['code'] = 200;
            } else {
                $data = $jwtAuth->signup($params->email, $params_array['password'], false);
            }
        } catch (\Exception $e) {
            return $this->sendError('Login Error.', $e->errors());
        }

        return $this->sendResponse(['token' => $data['token']], 'Login successfull');

    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $page = User::query()->paginate();

        return $this->sendResponse(['userspage' => $page], 'Listado de usuarios');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        $item = new User;
        $item->fill($request->validated());
        $item->save();

        return $this->sendResponse(['user' => $item], 'Usuario creado');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $item = User::query()->findOrFail($id);

        return $this->sendResponse(['user' => $item], 'Usuario mostrado');
    }

    /**
     * Update the specified resource in storage.
     *
     * @throws AuthenticationException
     */
    //public function update(int $id, UpdateUserRequest $request): JsonResponse
    public function update(UpdateUserRequest $request): JsonResponse
    {
        $error = [
            'status' => 'Error',
            'code' => 401,
            'message' => 'No autorizado',
            'errors' => '',
        ];
        // Comprobar que el usuario este autorizado
        // movido a middleware

        // recoger datos post
        $json = $request->input('json');
        $params_array = json_decode($json, true);
        if (! $params_array) {
            return $this->sendError('Datos actualizacion incorrectos jsondecode', ['json' => $json]);
        }
        // validar datos
        $validate = \Validator::make($params_array,
            [
                'name' => 'required|alpha',
                'surname' => 'required|alpha',
                'email' => 'required|email|unique:users,id,'.auth()->id(), // validacion unigue con excepcion de este id
                'descripcion' => 'alpha',
            ]);

        if ($validate->fails()) {
            return $this->sendError('Error validacion de campos', $validate->errors(), 422);
        }
        // quitar campos que no quiero actualizar
        unset(
            $params_array['email_verified_at'],
            $params_array['password'],
            $params_array['created_at'],
            $params_array['updated_at'],
            $params_array['remember_token']);

        // actualizar datos en bbdd

        try {
            $jwt = new JwtAuthHelper();
            $user = User::findOrFail($jwt->id());
            $user->update($params_array);
            // Handle update success

            return $this->sendResponse(['user' => $user], 'Actualizado usuario correctamente');

        } catch (\Exception $e) {
            // Handle update failure
            // Log the error or return an error response
            return $this->sendError('Error actualizando usuario', ['error' => $e->getMessage()]);
        }
        // devolver datos

    }

    /**
     * Uploads an avatar image from the request.
     *
     * @param  Request  $request  The HTTP request containing the image file.
     * @return JsonResponse Returns a JSON response indicating the status of the image upload.
     */
    public function uploadAvatar(Request $request)
    {
        try {
            // validator input file0
            $validator = \Validator::make($request->all(), ['file0' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048']);

            if ($validator->fails()) {
                return $this->sendError('Error validacion de campos', $validator->errors(), 422);
            }

            $image = $request->file('file0');
            $image_name = time().'_'.$image->getClientOriginalName();

            \Storage::disk('users')->put($image_name, \File::get($image));

            return $this->sendResponse(['imagen' => $image_name], 'Imagen subida correctamente', 201);
        } catch (\Exception $e) {
            $error = [
                'status' => 'Error',
                'code' => 400, // HTTP status code for Bad Request
                'message' => $e->getMessage(),
            ];

            return $this->sendError($e->getMessage(), $e->errors());
        }
    }

    /**
     * Fetches the image for the specified user.
     *
     * @param  int  $id  The ID of the user whose image is being retrieved.
     * @return JsonResponse Returns a JSON response containing the user's image data.
     */
    public function getImage2(int $id): JsonResponse
    {
        try {

            $user = User::query()->findOrFail($id);
            $imagePath = $user->image_path;
            // Check if the image exists

            $image = \Storage::disk('users')->get($imagePath);
            $headers = ['Content-Type' => 'image/png']; // Set the appropriate content type
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Usuario no encontrado', ['id' => $e->getIds()]);
        } catch (\Exception $e) {
            return $this->sendError('Error al encontrar imagen', $e->getTrace());
        }

        return $this->sendResponse(['image' => $image], 'imagen recuperada correctamente');

    }

    /**
     * Fetches the image for the specified filename.
     *
     * @param  string  $filename  The name of the file whose image is being retrieved.
     * @return JsonResponse|Response
     */
    public function getImage(string $filename)
    {
        // Check if the image exists
        if (! \Storage::disk('users')->exists($filename)) {
            $error = [
                'status' => 'Error',
                'code' => 404, // HTTP status code for Not Found
                'message' => 'Imagen no encontrada',
            ];

            return $this->sendResponse($error, $error['code']);
        }

        $image = \Storage::disk('users')->get($filename);
        // get mime from $image for response
        $image_mime = \Storage::disk('users')->mimeType($filename);
        $headers = ['Content-Type' => $image_mime]; // Set the appropriate content type

        return response($image, 200, $headers);
    }

    /**
     * Get the details of a specific user.
     *
     * @param  int  $id  The ID of the user whose details are being fetched.
     * @return JsonResponse Returns a JSON response containing the user's details.
     */
    public function getUserDetails(int $id): JsonResponse
    {
        $user = User::query()->findOrFail($id);

        return $this->sendResponse(['user' => $user], 'Detalles de usuario');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->sendError('Error funcion no implementada');
    }
}
