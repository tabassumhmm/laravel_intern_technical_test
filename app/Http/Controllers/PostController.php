<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class PostController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $posts = Post::query()
            ->with('user')
            ->latest('id')
            ->paginate();

        return PostResource::collection($posts);
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        $post = Post::query()->create($request->validated());

        return (new PostResource($post->load('user')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Post $post): PostResource
    {
        return new PostResource($post->load('user'));
    }

    public function update(UpdatePostRequest $request, Post $post): PostResource
    {
        $post->fill($request->validated())->save();

        return new PostResource($post->load('user'));
    }

    public function destroy(Post $post): Response
    {
        $post->delete();

        return response()->noContent();
    }
}
