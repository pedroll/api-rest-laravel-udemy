<?php

namespace App\Http\Middleware;

use App\Helpers\JwtAuthHelper;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 *
 */
class ApiAuthMiddleware
{

    /**
     * @param Request $request
     * @param Closure $next
     * @return Response|JsonResponse|RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $error = [
            'status' => 'Error',
            'code' => 401,
            'message' => 'No autorizado',
            'errors' => '', // para rellenar en cada caso error
        ];
        try {

            $jwtAuthHelper = new JwtAuthHelper();
            $checkToken = $jwtAuthHelper->checkToken();
            if (! $checkToken) {
                return response()->json($error, $error['code']);
            }
        } catch (\Exception $e) {
            $error['errors'] = $e->getMessage();
            return response()->json($error, $error['code']);
        }

        return $next($request);
    }
}
