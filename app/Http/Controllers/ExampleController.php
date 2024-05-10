<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExampleController extends Controller
{
    /**
    * @OA\Get(
    *     path="/test",
    *     description="Test page",
    *     @OA\Response(
    *         response=200,
    *         description="Success Message",
    *         @OA\JsonContent(ref="#/components/schemas/BookModel"),
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="an ""unexpected"" error",
    *         @OA\JsonContent(ref="#/components/schemas/ErrorModel"),
    *     )
    * )
    */
    public function test()
    {
        return $this->success(['message'=>'hello world']);
    }
}
