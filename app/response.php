<?php

namespace App;

trait response
{
    function success($message, $code = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message
        ], $code);
    }

    function error($message, $code = 400)
    {
        return response()->json([
            'status' => false,
            'message' => $message
        ], $code);
    }

    function validatorerror($data,$code = 400)
    {
        return response()->json([
            'status' => false,
            'error' => $data
        ], $code);
    }

    function successData($data)
    {
        return response()->json([
            'data' => $data]);
    }
}
