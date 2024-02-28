<?php

namespace App\Helpers;

use App\Models\User;
use DateTimeImmutable;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

class JwtAuthHelper
{
    public $key;
    public function __construct()
    {
        $this->key = env('JWT_KEY',null);
    }

    public function signup($email, $password, $getToken = null)
    {
        // todo buscar si existe

        $user = User::where([
            'email' => $email,
            // 'password' =>$testUser->getAttributeValue('password'),
        ])->first();

        // todo Comprobar si es correcto
        //$signup =password_verify($password, $user->getAttributeValue('password'));
        // con laravel
        $signup = Hash::check($password, $user->getAttributeValue('password'));
//         var_dump($signup);
        // todo generar el token usuario autentificado
        if ($signup) {
            $token = [
                'sub' => $user->getAttributeValue('id'),
                'email' => $user->getAttributeValue('email'),
                'name' => $user->getAttributeValue('name'),
                'surname' => $user->getAttributeValue('surname'),
                'iat' => time(),
                'exp' => time() + (7 * 24 * 60 * 60),
            ];
            $jwt = JWT::encode($token, $this->key, 'HS256');

            // todo devolver datos codificados o token
            if (is_null($getToken)) {
                $data = $jwt;
                $decoded = JWT::decode($jwt, new Key($this->key, 'HS256'));

                $data = $decoded;
            } else {
                $data = $jwt;

            }
        } else {
            $data = [
                'estatus' => 'error',
                'message ' => 'Login incorrecto ' . $email . '<br>' . $password
            ];
        }

        return $data;
    }

    // get jwt info from header
    public function getRawJWT()
    {
        // check if header exists
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        if (!$authHeader) {
            throw new AuthenticationException('Authorization header not found');
        }

        // check if bearer token exists
        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            throw new AuthenticationException('Token not found');
        }

        // extract token
        $jwt = $matches[1];

        if (!$jwt) {
            throw new AuthenticationException('Could not extract token');
        }

        return $jwt;
    }

    public function decodeRawJWT($jwt)
    {
        // use secret key to decode token
        $secretKey = $this->key;
        try {
            $token = JWT::decode($jwt, new Key($secretKey, 'HS512'));
            $now = new DateTimeImmutable();
        } catch (Exception $e) {
            throw new AuthenticationException('Unauthorized');
        }

        return $token;
    }

    public function getAndDecodeJWT()
    {
        $jwt = $this->getRawJWT();
        $token = $this->decodeRawJWT($jwt);

        return $token;
    }
}
