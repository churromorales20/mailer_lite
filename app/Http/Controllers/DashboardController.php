<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\MailerLite;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $authorized = MailerLite::validateKey();
        return view('dashboard', [
            'authorized' => $authorized === true,
        ]);
    }

    public function saveAPIKey(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'api_key' => 'required|string|min:50',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error', 
                'code' => 422,
                'errors' => $validator->errors()
            ]);
        }
        
        $result = MailerLite::saveKey($request->input('api_key'));
        if ( $result === true) {
            return response()->json([
                'status' => 'success', 
            ]);
        }
        return response()->json([
            'status' => 'error', 
            'code' =>  $result
        ]);
    }

    public function deleteAPIKey(Request $request)
    {
        MailerLite::deleteKey();
        return response()->json([
            'status' => 'success', 
        ]);
    }
}
