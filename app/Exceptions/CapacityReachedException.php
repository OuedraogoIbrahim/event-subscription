<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class CapacityReachedException extends Exception
{
    //
    public function render(): JsonResponse
    {
        return response()->json([
            'error'   => 'CAPACITY_REACHED',
            'message' => 'Cet événement est complet.',
        ], 422);
    }
}
