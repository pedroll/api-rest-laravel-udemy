<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;

class UserController extends Controller
{
    /**
     * @return string
     */
    public function pruebas(): string
    {
        return 'accion de pruebas en user-controler';
    }

    /**
     * @return string
     */
    public function register()
    {
        return 'accion de register en user-controler';
    }

    /**
     * @return string
     */
    public function login()
    {
        return 'accion de login en user-controler';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $page = User::query()->paginate();

        return response()->json(compact('page'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateUserRequest $request)
    {
        $item = new User;
        $item->fill($request->validated());
        $item->save();

        return response()->json(compact('item'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $item = User::query()->findOrFail($id);

        return response()->json(compact('item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, UpdateUserRequest $request)
    {
        $item = User::query()->findOrFail($id);
        $item->update($request->validated());

        return response()->json(compact('item'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        return response()->json('Error', 400);
    }
}
