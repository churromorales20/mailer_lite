<?php 

namespace App\Helpers;

use App\Models\ApiKey;
use Carbon\Carbon;
use Ixudra\Curl\Facades\Curl;

class MailerLite
{

    private static $api_key = '';
    private static $api_url = 'https://connect.mailerlite.com/api/';

    public static function validateKey($key = '')
    {

        if (empty($key)) {
            if (!$key_record = ApiKey::orderBy('id')->first()) {
                return 3;
            }
            $key = $key_record->api_key;
        }
        self::$api_key = $key;
        $response = self::getAPi('subscribers', [
            'limit' => 0
        ]);
        return is_object($response) ? true : $response;
    }

    public static function getSubscribersPage($subscribers_map, $data)
    {
        return array_slice($subscribers_map, $data['start'], $data['length']);
    }   

    public static function updateSubscriber($id, $subscriber)
    {
        self::validateKey();
        return self::putAPi('subscribers/' . $id, $subscriber);
        //return is_object($response) ? true : $response;
    }   

    public static function createSubscriber($subscriber)
    {
        self::validateKey();
        return self::postAPi('subscribers/', $subscriber, true);
    }   

    public static function deleteSubscriber($id)
    {
        self::validateKey();
        return self::deleteAPi('subscribers/' . $id);
    }   

    public static function checkIfEmailExists($email)
    {
        self::validateKey();
        $response = self::getAPi('subscribers/' . $email);
        return is_object($response) ? true : ($response === 404 ? false : $response);

    }

    public static function getSubscribersTable($search_value)
    {
        self::validateKey();
        $total_subs = $response = self::totalSubscribers();

        if(!is_array($total_subs)){
            $last_cursor = '';
            $chunk_size = 1000;
            $subscribers = [];
            $filter_active = !empty($search_value);
            for ($i=0; $i < $total_subs; $i += $chunk_size) { 
                $response = self::getAPi('subscribers', [
                    'limit' => $chunk_size,
                    'cursor' => $last_cursor
                ]);

                if (!is_object($response)) {
                    return $response;
                }

                foreach ($response->data as $subs) {
                    if ($filter_active && !preg_match("/" . $search_value . "/i", $subs->email)) {
                        continue;
                    }

                    $subscribers[] = [
                        'name' => $subs->fields->name,
                        'id' => $subs->id,
                        'email' => $subs->email,
                        'subscribed_at' => $subs->subscribed_at,
                        'country' => $subs->fields->country,
                    ];
                }

                $last_cursor = $response->meta->next_cursor;
            }

            return [
                'total' => $total_subs,
                'subscribers' => $subscribers
            ];
        }
        
        return $total_subs['error_code'];
    }

    public static function deleteKey()
    {
        ApiKey::query()->delete();
    }

    public static function saveKey($key)
    {
        $is_valid = self::validateKey($key);
        if ($is_valid === true) {
            self::deleteKey();
            ApiKey::create([
                'api_key' => $key
            ]);
            return true;
        }
        return $is_valid;
    }

    private static function deleteAPi($end_point)
    {
        $response = Curl::to(self::$api_url . $end_point)
                    ->withBearer(self::$api_key)
                    ->asJsonResponse()
                    ->withTimeout(15)
                    ->withConnectTimeout(15)
                    ->returnResponseObject()
                    ->delete();
        if ($response->status === 204) {
            return true;
        }elseif ($response->status === 401) {
            self::deleteKey();
            return 401;
        }elseif ($response->status === 404) {
            return 404;
        }
        return 1;
    }

    private static function postAPi($end_point, $data, $create = false)
    {
        $response = Curl::to(self::$api_url . $end_point)
                    ->withData($data)
                    ->withBearer(self::$api_key)
                    ->asJsonResponse()
                    ->withTimeout(15)
                    ->withConnectTimeout(15)
                    ->returnResponseObject()
                    ->post();
        if ($response->status === 200 || $response->status === 201) {
            return $create === true && $response->status === 200 ? 400 : $response->content;
        }elseif ($response->status === 401) {
            self::deleteKey();
            return 401;
        }elseif ($response->status === 404) {
            return 404;
        }
        return 1;
    }

    private static function putAPi($end_point, $data)
    {
        $response = Curl::to(self::$api_url . $end_point)
                    ->withData($data)
                    ->withBearer(self::$api_key)
                    ->asJsonResponse()
                    ->withTimeout(15)
                    ->withConnectTimeout(15)
                    ->returnResponseObject()
                    ->put();
        if ($response->status === 200 ) {
            return true;
        }elseif ($response->status === 401) {
            self::deleteKey();
            return 401;
        }elseif ($response->status === 404) {
            return 404;
        }
        return 1;
    }

    private static function totalSubscribers()
    {
        $response = self::getAPi('subscribers', [
            'limit' => 0
        ]);
        return is_object($response) ? $response->total : [
            'error_code' => $response
        ];
    }

    private static function getAPi($end_point, $data = [])
    {
        //dd($end_point, self::$api_url);
        $response = Curl::to(self::$api_url . $end_point)
                    ->withData($data)
                    ->withBearer(self::$api_key)
                    ->asJsonResponse()
                    ->withTimeout(15)
                    ->withConnectTimeout(15)
                    ->returnResponseObject()
                    ->get();
        if ($response->status === 200) {
            return $response->content;
        } elseif ($response->status === 401) {
            self::deleteKey();
            return 401;
        } elseif ($response->status === 404) {
            return 404;
        }

        return 1;
    }

}