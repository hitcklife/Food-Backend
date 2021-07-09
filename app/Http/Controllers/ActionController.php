<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Validator;

class ActionController extends Controller
{

    //
    public function info(){
        $user = auth()->user();
        $info = $user->info;
        if ($user->type == 1){
            $company = $user->info->company;
        }

        return $user;
    }

    public function createProduct(Request $request){
        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'name' => 'required|string|between:2,100',
            'price' => 'required',
            'measuring' => 'required',
            'description' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $user = auth()->user();
        $info = $user->info;
        if ($user->type == 1){
            $company = $user->info->company;
        }

        $product = Product::create(array_merge(
            $validator->validated(),
            ['user_id' => $user->id]
        ));

        return $product;
    }

    public function getProduct($id){
        $product = Product::find($id);
        $image = $product->images;

        return $product;
    }

    public function uploadImages(Request $request){
        $user = auth()->user();
        $info = $user->info;
        if ($user->type == 1){
            $company = $user->info->company;
        }
        $product_id = Validator::make($request->all(), [
            'product_id' => 'required',
        ]);
        $file = Validator::make($request->all(), [
            'file' => 'mimes:jpg,bmp,png,jpeg|required',
        ]);

        if($product_id->fails()){
            return response()->json($product_id->errors(), 400);
        }
        if($file->fails()){
            return response()->json($file->errors(), 400);
        }

        $path = 'uploads/products/'.$user->id;
        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->makeDirectory($path);
        }

        $store = Storage::disk('public')->putFile($path, $request->file('file'));

        $newPhoto = new ProductImage;
        $newPhoto->product_id = $request->input('product_id');
        $newPhoto->path = $store;
        $newPhoto->save();

        return response()->json([
            'success' => 'file has been uploaded',
        ]);

    }
}
