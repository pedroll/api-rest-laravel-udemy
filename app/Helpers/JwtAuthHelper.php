<?php

namespace App\Helpers;

use App\Models\User;
use DateTimeImmutable;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;
use stdClass;

class JwtAuthHelper
{
    public string $key;

    public function __construct()
    {
        $this->key = env('JWT_KEY');
    }

    /**
     * Este código es un método de la clase JwtAuthHelper en PHP que se encarga de realizar el registro de un usuario y generar un token JWT para autenticación.
     *
     * @param  null  $getToken
     * @return stdClass|string|string[]
     */
    public function signup($email, $password, $getToken = null)
    {
        $signup = false;
        $data = [
            'status' => 'error',
            'responseCode' => 401,
            'message' => 'Login incorrecto',
        ];
        // buscar si existe
        $user = User::where(['email' => $email])->first();
        if ($user) {
            // Comprobar si es correcto
            // con laravel
            $signup = Hash::check($password, $user->getAttributeValue('password'));
        }
        //  generar el token usuario autentificado
        if ($signup) {
            $data = [
                'status' => 'Success',
                'responseCode' => 200,
                'message' => 'Login correcto',
            ];
            $token = [
                'sub' => $user->getAttributeValue('id'),
                'email' => $user->getAttributeValue('email'),
                'name' => $user->getAttributeValue('name'),
                'surname' => $user->getAttributeValue('surname'),
                'iat' => time(),
                'exp' => time() + (7 * 24 * 60 * 60),
            ];
            $jwt = JWT::encode($token, $this->key, 'HS256');
            // devolver datos codificados o token
            if (! $getToken) {
                $jwt = JWT::decode($jwt, new Key($this->key, 'HS256'));
            }

            return response()->json($jwt, $data['responseCode']);
        }

        return response()->json($data, $data['responseCode']);
    }

    /**
     *  function to  compare and check JWT token
     *
     * @return bool|stdClass Devuelve Auth bool o identidad si solicitada
     *
     * @throws AuthenticationException
     */
    public function checkToken($getidentity = false)
    {
        // check token is valid
        $auth = $this->getAndDecodeJWT();

        // si no se requiere identiad devolver auth true
        if (isset($auth->sub) && ! $getidentity) {
            $auth = true;
        }

        return $auth;
    }

    /**
     * get jwt info from header
     *
     * @return string
     *
     * @throws AuthenticationException
     */
    public function getRawJWT()
    {
        // check if header exists
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        if (! $authHeader) {
            throw new AuthenticationException('Authorization header not found');
        }
$authHeader =str_replace('"','',$authHeader);
        // check if bearer token exists
//        if (! preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
//            throw new AuthenticationException('Token not found');
//        }

        // extract token
        //$jwt = $matches[1];

//        if (! $jwt) {
//            throw new AuthenticationException('Could not extract token');
//        }
//
//        return $jwt;
        return $authHeader;
    }

    /**
     * Método para decodificar un JWT sin procesar utilizando una clave secreta.
     *
     * Utiliza la clave secreta proporcionada para decodificar el token JWT y devuelve el token decodificado.
     *
     * @param  string  $jwt  El JWT sin procesar a decodificar
     * @return mixed El token decodificado si es válido, de lo contrario, devuelve false
     *
     * @throws AuthenticationException Cuando la decodificación falla y se produce una excepción
     */
    public function decodeRawJWT($jwt)
    {
        // use secret key to decode token
        $secretKey = $this->key;
        try {
            $token = JWT::decode($jwt, new Key($secretKey, 'HS256'));
            $now = new DateTimeImmutable();
        } catch (Exception $e) {
            $token = false;
            throw new AuthenticationException('Unauthorized');
        }

        return $token;
    }

    /**
     * Método para obtener y decodificar un JWT.
     *
     * @return stdClass
     *
     * @throws AuthenticationException
     */
    public function getAndDecodeJWT()
    {
        $jwt = $this->getRawJWT();

        return $this->decodeRawJWT($jwt);
    }
}
// funcion update user
