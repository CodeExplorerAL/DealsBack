<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ResetPassword;
use App\Models\UPda;
use App\Models\UpdateModels;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdateController extends Controller
{

    public function delete_cart(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',

        ]);

        $delete_item = User::select('name')->where('id', '=', $request->id)->get();
        User::where('id', '=', $request->id)->delete();

        return response()->json([
            'message' => 'User deleted successfully',
            'delete_item' => $delete_item
        ]);
    }


    public function update_password(Request $request)
    {

        $token = $request->token;

        $password = $request->password;
        $password_confirmation = $request->password_confirmation;
        $email = $request->email;

        $original_password = User::select('password')->where('email', '=', $email)->get();
        $updateData = [];

        if (($password != $original_password) &&  ($password == $password_confirmation)) {
            $updateData['password'] = Hash::make($request->password);
        }
        $user_email = ResetPassword::select('email')->where("token", "=", $token)->where("email", "=", $email)->get();
        User::where('email', "=", $user_email[0]->email)->update($updateData);

        return response()->json([
            'message' => 'Item updated successfully',
        ]);
    }
}
