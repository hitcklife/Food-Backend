<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Item;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductNote;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Validator;
use Carbon\Carbon;

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

    public function getChats(){
        $user = auth()->user();

        $chats = Chat::where('sender_id', $user->id)->orWhere('receiver_id', $user->id)->get();

        return $chats;
    }

    public function getChat($id){
        $user = auth()->user();

        Chat::where('sender_id', $user->id)->where('receiver_id', $id)->orWhere('sender_id', $id)->where('receiver_id', $user->id)->update(['opened' => 1]);

        $chats = Chat::where('sender_id', $user->id)->where('receiver_id', $id)->orWhere('sender_id', $id)->where('receiver_id', $user->id)->get();

        return $chats;
    }

    public function sendChat(Request $request){
        $user = auth()->user();
        $receiver = $request->input('receiver_id');
        $text = $request->input('text');

        $chat = new Chat;
        $chat->sender_id = $user->id;
        $chat->receiver_id = $receiver;
        $chat->text = $text;
        $chat->save();

        return  response()->json([
            'success' => 'message sent!',
            'text' => $text
        ]);

    }

    function generateCode(){
        $number = rand(100000000,999999999);
        $checking = Transaction::where('number', $number)->first();
        if (isset($checking)){
            $this->generateCode();
        }else{
            return $number;
        }
    }

    public function newRequest(Request $request){
        $number = $this->generateCode();
        $price = null;
        $user = auth()->user();
        if ($request->header('Content-Type', 'application/json')) {
            $input = $request->all();
            $input = $input["data"];
            foreach ($input["order"] as $inp){
                (int)$price = (int)$price + (int)Product::find($inp["product_id"])->price*(int)$inp["quantity"];
            }
            $transaction = new Transaction;
            $transaction->number = $number;
            $transaction->client_id = $user->id;
            $transaction->requested_time = Carbon::parse($input["time"]);
            $transaction->price = $price;
            $final = $transaction->save();
            foreach ($input["order"] as $inp){
                $item = new Item;
                $item->transaction_id = $transaction->id;
                $item->quantity = $inp["quantity"];
                $item->save();
            }
            if ($input["note"] != NULL){
                $note = new ProductNote;
                $note->transaction_id = $transaction->id;
                $note->note = $input["note"];
                $note->save();
            }
            return $request;
        }else{
            return  response()->json([
                'error' => 'wrong datatype!',
            ], 400);
        }


    }

}
