<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * @OA\Info(
     *      version="1.0.0",
     *      title="Task Manager API Documentation",
     *      description="Task Manager API Documentation. This is a sample API for Task Manager.",
     *      @OA\Contact(
     *          email="demo@email.com"
     *      ),
     *      @OA\License(
     *          name="",
     *          url=""
     *      )
     * )
     *
     * @OA\Server(
     *      url=L5_SWAGGER_CONST_HOST,
     *      description="Task Manager API Server"
     * )

     *
     * @OA\Tag(
     *     name="Task Manager",
     *     description="API Endpoints of Task Manager"
     * )
     */
}
