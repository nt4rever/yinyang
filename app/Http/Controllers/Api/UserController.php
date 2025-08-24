<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserCreateRequest;
use App\Http\Requests\User\UserIndexRequest;
use App\Http\Requests\User\UserNameListRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserNameListCollection;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Services\UserService;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(UserIndexRequest $request)
    {
        $users = $this->userService->getByFilters($request->validated());

        return new UserCollection($users);
    }

    /**
     * Get users names by search phrase
     */
    public function names(UserNameListRequest $request)
    {
        $users = $this->userService->getBySearchPhrase(
            $request->input('search_phrase'),
            $request->integer('limit', config('eloquentfilter.paginate_limit'))
        );

        return new UserNameListCollection($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserCreateRequest $request)
    {
        $user = $this->userService->create($request->validated());

        return new UserResource($user);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserUpdateRequest $request, User $user)
    {
        $user = $this->userService->update($user, $request->validated());

        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->userService->delete($user);

        return response()->noContent();
    }
}
