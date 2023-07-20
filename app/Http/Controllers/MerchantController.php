<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Order;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    ) {}

    /**
     * Useful order statistics for the merchant API.
     *
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        // TODO: Complete this method
        $validation = Validator::make($request->all(),[
            'from' => 'required|date',
            'to' => 'required|date'
        ]);

        if($validation->fails()){
            return new JsonResponse($validation->getMessageBag());
        }else{

            $from = Carbon::parse($request->from)->startOfDay();
            $to = Carbon::parse($request->to)->endOfDay();

            $orders = Order::whereBetween('created_at',[$from,$to])->get();

            $response = [
                'count' => $orders->count(),
                'commission_owed' => $orders->where('payout_status','unpaid')->sum('commission_owed'),
                'revenue' => $orders->where('payout_status','unpaid')->sum('subtotal')
            ];

            return new JsonResponse($response);
        }
    }
}
