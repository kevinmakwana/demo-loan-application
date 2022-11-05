<?php
namespace App\Macros\Http;
use Illuminate\Support\Facades\Response as HttpResponse;
/**
 *
 */
class Response
{
    public static function registerMacros()
    {
        HttpResponse::macro('success', function ($message,$status,$data= [],) {
            return response()->json([
                'status' => $status,
                'message' => $message,
                'data' => is_null($data) ? (object)[]: $data
              ], $status);
        });

        HttpResponse::macro('error', function ($message,$status,$errorMessages=[]) {
            return response()->json([
                'status' => $status,
                'message' => $message,
                'data' => is_null($errorMessages) ? (object)[]: $errorMessages
              ], $status);
        });
    }
}

