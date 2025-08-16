<?php

namespace App\Docs;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *   title="MyRepo API",
 *   version="1.0.0",
 *   description="Documentación generada con l5-swagger",
 *   @OA\Contact(email="soporte@midominio.com")
 * )
 * @OA\Server(
 *   url=L5_SWAGGER_CONST_HOST,
 *   description="Servidor local"
 * )
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="Bearer"
 * )
 */
class OpenApi {}