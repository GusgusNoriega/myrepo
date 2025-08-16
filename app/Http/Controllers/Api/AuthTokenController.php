<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Annotations as OA;

class AuthTokenController extends Controller
{
    /**
     * @OA\Post(
     *   path="/api/auth/login",
     *   tags={"Auth"},
     *   summary="Emitir un access token (Passport PAT) usando email/clave",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"email","password"},
     *       @OA\Property(property="email", type="string", format="email", example="gusgusnoriega@gmail.com"),
     *       @OA\Property(property="password", type="string", example="********"),
     *       @OA\Property(property="device_name", type="string", example="swagger")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="access_token", type="string"),
     *       @OA\Property(property="token_type", type="string", example="Bearer"),
     *       @OA\Property(property="expires_at", type="string", format="date-time", nullable=true),
     *       @OA\Property(
     *         property="user",
     *         type="object",
     *         @OA\Property(property="id", type="integer"),
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="email", type="string", format="email")
     *       )
     *     )
     *   ),
     *   @OA\Response(response=401, description="Credenciales inválidas"),
     *   @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'       => ['required','email'],
            'password'    => ['required','string'],
            'device_name' => ['nullable','string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        $tokenResult = $user->createToken($data['device_name'] ?? 'swagger');
        $tokenModel  = $tokenResult->token; // Laravel\Passport\Token

        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type'   => 'Bearer',
            'expires_at'   => optional($tokenModel->expires_at)->toIso8601String(),
            'user'         => ['id'=>$user->id,'name'=>$user->name,'email'=>$user->email],
        ]);
    }

    /**
     * @OA\Get(
     *   path="/api/auth/me",
     *   tags={"Auth"},
     *   summary="Retorna el usuario autenticado (guard api)",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(
     *     response=200, description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="id", type="integer"),
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="email", type="string", format="email")
     *     )
     *   ),
     *   @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function me(Request $request)
    {
        $u = $request->user();
        return response()->json(['id'=>$u->id,'name'=>$u->name,'email'=>$u->email]);
    }

    /**
     * @OA\Post(
     *   path="/api/auth/logout",
     *   tags={"Auth"},
     *   summary="Revoca el token actual",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=204, description="Sin contenido"),
     *   @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function logout(Request $request)
    {
        // Revoca el token de acceso usado en esta petición
        if ($request->user() && method_exists($request->user(), 'token')) {
            $request->user()->token()?->revoke();
        }

        return response()->json(null, 204);
    }
}
