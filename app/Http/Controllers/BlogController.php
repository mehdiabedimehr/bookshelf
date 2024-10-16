<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BlogController extends Controller
{
    /**
     * @OA\Get(
     *     path="/blog",
     *     tags={"Blog"},
     *     summary="listAllItem",
     *     description="list all Item",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             default="1"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success Message",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="current_page",
     *                 type="integer",
     *                 format="int32",
     *                 description="Current page number"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/BlogModel"),
     *                 description="List of item"
     *             ),
     *             @OA\Property(
     *                 property="first_page_url",
     *                 type="string",
     *                 format="uri",
     *                 description="First page URL"
     *             ),
     *             @OA\Property(
     *                 property="from",
     *                 type="integer",
     *                 format="int32",
     *                 description="First item number in the current page"
     *             ),
     *             @OA\Property(
     *                 property="last_page",
     *                 type="integer",
     *                 format="int32",
     *                 description="Last page number"
     *             ),
     *             @OA\Property(
     *                 property="links",
     *                 type="array",
     *                 @OA\Items(
     *                     oneOf={
     *                         @OA\Schema(ref="#/components/schemas/Previous"),
     *                         @OA\Schema(ref="#/components/schemas/Links"),
     *                         @OA\Schema(ref="#/components/schemas/Next")
     *                     }
     *                 ),
     *                 description="Links"
     *             ),
     *             @OA\Property(
     *                 property="last_page_url",
     *                 type="string",
     *                 format="uri",
     *                 description="Last page URL"
     *             ),
     *             @OA\Property(
     *                 property="next_page_url",
     *                 type="string",
     *                 format="uri",
     *                 description="Next page URL"
     *             ),
     *             @OA\Property(
     *                 property="path",
     *                 type="string",
     *                 description="Path"
     *             ),
     *             @OA\Property(
     *                 property="per_page",
     *                 type="integer",
     *                 format="int32",
     *                 description="Items per page"
     *             )
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="an ""unexpected"" error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel"),
     *     )
     * )
     * Display the specified resource.
     */
    public function index()
    {
        return $this->success(Blog::latest()->whereIsVerified(1)->paginate(20));
    }

    /**
     * @OA\Post(
     *     path="/blog",
     *     tags={"Blog"},
     *     summary="MakeOneItem",
     *     description="make one Item",
     *     @OA\RequestBody(
     *         description="tasks input",
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="title",
     *                 type="string",
     *                 description="title",
     *                 example="book title id"
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 description="description",
     *                 default="null",
     *                 example="description",
     *             ),
     *             @OA\Property(
     *                 property="article",
     *                 type="string",
     *                 description="article",
     *                 default="null",
     *                 example=1,
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success Message",
     *         @OA\JsonContent(ref="#/components/schemas/CommentModel"),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="an 'unexpected' error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel"),
     *     ),security={{"api_key": {}}}
     * )
     * Make a blog
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'          => 'required',
            'description'    => 'required',
            'article'        => 'required'
        ]);

        try {
            $book = Blog::create($request->all());
            return $this->success($book);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error(__('messages.blog.notCreated'));
        }
    }

    /**
     * @OA\Get(
     *     path="/blog/{id}",
     *     tags={"Blog"},
     *     summary="getOneItem",
     *     description="get One Item",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
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
     * Display the specified resource.
     */
    public function show(Int $id)
    {
        try {
            $content = Blog::findOrFail($id);
            if ($content->user_id !== auth()->id() && $content->verified == 0) {
                return $this->error(__('messages.Forbidden'), status:403);
            }
            return $this->success($content);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error(__('messages.blog.notFound'));
        }
    }

    /**
     * @OA\Put(
     *     path="/blog/{id}",
     *     tags={"Blog"},
     *     summary="EditOneItem",
     *     description="edit one Item",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="tasks input",
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="title",
     *                 type="string",
     *                 description="title",
     *                 example="Item name"
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 description="description",
     *                 default="null",
     *                 example="description Item",
     *             ),
     *             @OA\Property(
     *                 property="article",
     *                 type="string",
     *                 description="article",
     *                 default="null",
     *                 example="content Item",
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success Message",
     *         @OA\JsonContent(ref="#/components/schemas/BookModel"),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="an 'unexpected' error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel"),
     *     ),security={{"api_key": {}}}
     * )
     * Update the specified resource in storage.
     */
    public function update(Request $request, Int $id)
    {
        $request->validate([
            'title'        => 'required',
            'description'  => 'required',
            'article'      => 'required'
        ]);

        try {
            $book = Blog::findOrFail($id);
            if ($book->user_id !== auth()->id() || $book->verified == 1) {
                return $this->error(__('messages.forbidden'), status:403);
            }

            $book->update($request->all());
            return response()->json($book);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error(__('messages.blog.notUpdated'));
        }
    }
}
