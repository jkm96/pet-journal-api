<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePaymentRequest;
use App\Http\Requests\CreatePetRequest;
use App\Services\Payments\PaymentsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentsController extends Controller
{
    /**
     * @var PaymentsService
     */
    private PaymentsService $_paymentsService;

    public function __construct(PaymentsService $paymentsService)
    {
        $this->_paymentsService = $paymentsService;
    }

    /**
     * @param CreatePaymentRequest $createPaymentRequest
     * @return JsonResponse
     */
    public function createPayment(CreatePaymentRequest $createPaymentRequest): JsonResponse
    {
        return $this->_paymentsService->createUserPayment($createPaymentRequest);
    }
}
