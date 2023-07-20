<?php

namespace App\Http\Controllers;

use App\Services\AffiliateService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WebhookController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Pass the necessary data to the process order method
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        // TODO: Complete this method
        $validate = Validator::make($request->all(),[
            'order_id' => 'required|numeric',
            'subtotal_price' => 'required|numeric',
            'merchant_domain' => 'required|string',
            'discount_code' => 'required|string',
            'customer_email' => 'required|email',
            'customer_name' => 'required|string'
        ]);

        if($validate->fails()){
            return new JsonResponse($validate->getMessageBag());
        }else{
            $response = $this->orderService->processOrder($request->all());
            return new JsonResponse($response);
        }
    }
}
