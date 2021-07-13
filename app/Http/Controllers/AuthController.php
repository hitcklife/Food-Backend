<?php

namespace App\Http\Controllers;
use App\Models\UserCompany;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Validator;


class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->createNewToken($token);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'lname' => 'required|string|between:2,100',
            'type' => 'required',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));

        $company_id = null;
        if ($user->type == 1){
            $company = new UserCompany;
            $company->save();
            $company_id = $company->id;
        }

        $info = new UserInfo;
        $info->user_id = $user->id;
        $info->company_id = $company_id;
        $info->save();

        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user,
            'access_token' => $token
        ], 201);
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        auth()->logout();

        return response()->json(['message' => 'User successfully signed out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh() {
        return $this->createNewToken(auth()->refresh());
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile() {
        return response()->json(auth()->user());
    }

    public function updateUser(Request $request){
        $user = auth()->user();
        $user_id = $user->id;
        $info_user = auth()->user()->info;
        $data = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'lname' => 'required|string|between:2,100',
        ]);
        $info = Validator::make($request->all(), [
            'street' => 'string|between:1,100',
            'apt' => 'string|between:1,100',
            'city' => 'string|between:1,100',
            'state' => 'string|between:1,100',
            'zip' => 'string|between:1,100',
            'phone' => 'string|between:1,15',
        ]);
        if($data->fails()){
            return response()->json($data->errors(), 400);
        }

        if($request->hasFile('avatar')){
            $avatar = Validator::make($request->all(), [
                'avatar' => 'mimes:jpg,bmp,png,jpeg|required',
            ]);
            if($avatar->fails()){
                return response()->json($avatar->errors(), 400);
            }

            $path = 'uploads/avatars/'.$user->id;
            if (! Storage::disk('public')->exists($path)) {
                Storage::disk('public')->makeDirectory($path);
            }

            $store = Storage::disk('public')->putFile($path, $request->file('avatar'));
            $user->info->update(['avatar' => $store]);
        }


        $user->update($data->validated());
        $user->info->update($info->validated());


        $user = auth()->user();

        return $user;

    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }

}
