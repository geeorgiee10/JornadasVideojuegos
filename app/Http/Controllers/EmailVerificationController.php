<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailVerificationController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function verify($token)
    {
        try {
            $response = $this->apiService->post('/v1/verify-email', [
                'token' => $token
            ]);

            return view('auth.verify-email', [
                'success' => isset($response['success']) && $response['success'],
                'message' => $response['message'] ?? null
            ]);

        } catch (\Exception $e) {
            Log::error('Error en verificación de email', [
                'error' => $e->getMessage(),
                'token' => $token
            ]);

            return view('auth.verify-email', [
                'success' => false,
                'message' => 'Hubo un problema al verificar tu correo electrónico. Por favor, intenta nuevamente o contacta a soporte.'
            ]);
        }
    }
}