<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class DuplicateEmailException extends Exception
{
    //
    public function render(): JsonResponse
    {
        return response()->json([
            'error'   => 'DUPLICATE_EMAIL',
            'message' => 'Cette adresse email est déjà enregistrée pour cet événement.',
        ], 409);
    }
}
