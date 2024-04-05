<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuthHelper;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
            $params = json_decode($json, false, 512, JSON_THROW_ON_ERROR); // volcamos a un objeto
            $params_array = json_decode($json, true, 512, JSON_THROW_ON_ERROR); // asi a un array
        } catch (\JsonException $e) {
            // Handle JSON decoding errors here
            $data = [
                'status' => 'Error',
                'code' => 400,
                'message' => $e->getMessage(),
                'errors' => $e,
            ];

            return response()->json($data, $data['code']);
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
            $data = [
                'status' => 'Error',
                'code' => 400,
                'message' => 'Error validacion de campos',
                'errors' => $validate->errors(),
            ];
        } else {
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
            // funciona con todas las propiedaes
            // $user = new User;
            // $user->setAttribute('name', $params_array['name'] );
            // $user->setAttribute('surname', $params_array['surname'] );
            // $user->setAttribute('email', 'pedrollongo16@gmail.com' );
            // $user->setAttribute('role', 'ROLE_USER' );
            // $user->setAttribute('password', $params_array['password'] );

            // solo funciona con visibles
            // $user->surname = $params_array['surname'];
            // $user->email = $params_array['email'];
            // $user->password = $password_hash;
            // $user->role = 'ROLE_USER';
            // $user->save();

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

        // recibir datos por post
        $json = $request->input('json', null); // llega
        // $en una unica key "json"
        try {
            $params = json_decode($json, false);
        } catch (\JsonException $e) {
            $data = [
                'status' => 'Error',
                'code' => 400,
                'message' => $e->getMessage(),
                'errors' => $e,
            ];

            return response()->json($data, $data['code']);
        }
        // volcamos a un objeto
        try {
            $params_array = json_decode($json, true);

        } catch (\JsonException $e) {
            $data = [
                'status' => 'Error',
                'code' => 400,
                'message' => $e->getMessage(),
                'errors' => $e,
            ];

            return response()->json($data, $data['code']);
        } //asi a un array

        // valdar datos
        $validate = \Validator::make($params_array,
            [
                'email' => 'required|email',
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

            return $data;
        }

        return response()->json($data, 200);
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
            $error = [
                'status' => 'Error',
                'code' => 400,
                'message' => 'Datos actualizacion incorrectos',
                'datos' => $json,
            ];

            return response()->json($error, $error['code']);
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
            $error = [
                'status' => 'Error',
                'code' => 400,
                'message' => 'Error validacion de campos',
                'errors' => $validate->errors(),
            ];

            return response()->json($error, $error['code']);
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
            $data = [
                'status' => 'Success',
                'code' => 200,
                'message' => 'Actualizado usuario correctamente',
                'user' => $user,
            ];

            return response()->json(compact('data'), $data['code']);

        } catch (\Exception $e) {
            // Handle update failure
            // Log the error or return an error response
            $error = [
                'status' => 'Error',
                'code' => 400,
                'message' => 'Error actualizando usuario',
                'errors' => $e->getMessage(),
            ];

            return response()->json($error, $error['code']);
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
            $validatedData = $request->validate(['file0' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048']);

            //            $validate = \Validator::make(
            //                $request->all(),
            //                ['file0' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',]);
            //            if ($validate->fails()){
            //
            //            }

            if ($request->hasFile('file0')) {
                $image = $request->file('file0');
                $image_name = time().'_'.$image->getClientOriginalName();

                if (\Storage::disk('users')->put($image_name, \File::get($image))) {
                    $data = [
                        'status' => 'Success',
                        'code' => 201, // HTTP status code for Created
                        'message' => 'Imagen subida correctamente',
                        'image_name' => $image_name,
                    ];
                } else {
                    throw new \Exception('Error al subir la imagen al disco');
                }

                return response()->json($data, $data['code']);
            } else {
                throw new \Exception('Archivo no encontrado en la solicitud');
            }
        } catch (\Exception $e) {
            $error = [
                'status' => 'Error',
                'code' => 400, // HTTP status code for Bad Request
                'message' => $e->getMessage(),
            ];

            return response()->json($error, $error['code']);
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
        $user = User::query()->findOrFail($id);
        $imagePath = $user->image_path;
        // Check if the image exists
        if ($imagePath) {
            $image = \Storage::disk('users')->get($imagePath);
            $headers = ['Content-Type' => 'image/png']; // Set the appropriate content type

            return response($image, 200, $headers);
        } else {
            $error = [
                'status' => 'Error',
                'code' => 404, // HTTP status code for Not Found
                'message' => 'Imagen no encontrada para este usuario',
            ];

            return response()->json($error, $error['code']);
        }
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

            return response()->json($error, $error['code']);
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
     * @param int $id The ID of the user whose details are being fetched.
     * @return JsonResponse Returns a JSON response containing the user's details.
     */
    public function getUserDetails(int $id): JsonResponse
    {
        $user = User::query()->findOrFail($id);
        $data = [
            'status' => 'Success',
            'code' => 200,
            'message' => 'User details retrieved successfully',
            'user' => $user,
        ];
        return response()->json(compact('data'), $data['code']);
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        return response()->json('Error', 400);
    }
}
