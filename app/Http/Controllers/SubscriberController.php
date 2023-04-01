<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use App\Helpers\MailerLite;

class SubscriberController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'length' => 'required|numeric|in:10,25,50,100',
        ]);
        //dd('sssss');
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error', 
                'code' => 422,
                'errors' => $validator->errors()
            ], 422);
        }else {
            $draw = $request->input('draw');
            $start = $request->input('start');
            $length = $request->input('length');
            $searchValue = $request->input('search.value');
            $subscribers_table = MailerLite::getSubscribersTable($searchValue);
            if (!is_numeric($subscribers_table) && $subscribers_table !== false) {
                return response()->json([
                    'draw' => $draw,
                    'recordsTotal' => $subscribers_table['total'],
                    'recordsFiltered' => count($subscribers_table['subscribers']),
                    'data' => MailerLite::getSubscribersPage($subscribers_table['subscribers'], [
                        'start' => $start,
                        'length' => $length,
                    ])
                ]);
            }
        }

        return response()->json([
            'status' => 'error', 
            'code' => $subscribers_table,
        ], $subscribers_table === false ? 500 : $subscribers_table);
        
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'name' => 'required',
            'country' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error', 
                'code' => 422,
                'errors' => $validator->errors()
            ]);
        }

        $response = Mailerlite::createSubscriber([
            //"email" => "cecilio.dev@gmail.com",
            "email" => $request->input('email'),
            "fields" =>  [
                "name" =>  $request->input('name'),
                "country" => $request->input('country'),
            ],
        ]);

        if (is_object($response)) {
            return response()->json([
                'status' => 'success',
                'subscriber_id' => $response->data->id
            ]);
        }
        
        return response()->json([
            'status' => 'error',
            'code' => $response
        ]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'country' => 'required',
            'id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error', 
                'code' => 422,
                'errors' => $validator->errors()
            ]);
        }

        $response = Mailerlite::updateSubscriber($request->input('id'), [
            "fields" =>  [
                "name" =>  $request->input('name'),
                "country" => $request->input('country'),
            ],
        ]);

        if ($response === true) {
            return response()->json([
                'status' => 'success',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'code' => $response
        ]);
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error', 
                'code' => 422,
                'errors' => $validator->errors()
            ]);
        }

        $response = Mailerlite::deleteSubscriber($request->input('id'));
        if ($response === true) {
            return response()->json([
                'status' => 'success',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'code' => $response
        ]);
    }

    public function emailCheck(Request $request, $email)
    {
        $response = MailerLite::checkIfEmailExists($email);
        if (!is_numeric($response)) {
            return response()->json([
                'status' => 'success', 
                'exists' => $response
            ]);
        }

        return response()->json([
            'status' => 'error', 
            'code' => $response
        ]);
    }
}
