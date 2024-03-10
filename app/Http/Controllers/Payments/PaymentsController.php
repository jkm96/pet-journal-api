<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePaymentRequest;
use App\Services\Payments\PaymentsService;
use Illuminate\Http\JsonResponse;

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

    /**
     * @param $userEmail
     * @return JsonResponse
     */
    public function getBillingInfo($userEmail): JsonResponse
    {
        return $this->_paymentsService->getUserPayments($userEmail);
    }
}
