<?php

namespace App\Services;

use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class OrderService
{
    const ERROR_MESSAGE = "Order id is already exist";


    public function __construct(
        protected AffiliateService $affiliateService
    ) {}

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        // TODO: Complete this method

        $order = Order::where('id',$data['order_id'])->first();
        if($order){
            return self::ERROR_MESSAGE;
        }else{
            $affiated = User::with('affiliate')->where('email',$data['customer_email'])->first();
            $merchant = Merchant::where('domain',$data['merchant_domain'])->first();

            if($affiated->affiliate){
                $order = Order::create([
                    'merchant_id' => $affiated->affiliate->merchant_id,
                    'affiliate_id'=> $affiated->affiliate->id,
                    'subtotal' => $data['subtotal_price'],
                    'commission_owed' => $affiated->affiliate->commission_rate,
                    'payout_status' => "unpaid",
                    'customer_email' => $data['email'],
                ]);
                return $order;
            }else{
                $affiliate = Affiliate::create([
                    'user_id' => $affiated->id,
                    'merchant_id' , $merchant->id,
                    'commission_rate', $merchant->default_commission_rate,
                    'discount_code' => $data['discount_code']
                ]);

                if($affiliate){

                    Mail::to($data['customer_email'])->send(new AffiliateCreated($affiliate)); //I prefer to send email through job

                    $order = Order::create([
                        'merchant_id' => $affiliate->merchant_id,
                        'affiliate_id'=> $affiliate->id,
                        'subtotal' => $data['subtotal_price'],
                        'commission_owed' => $affiliate->commission_rate,
                        'payout_status' => "unpaid",
                        'customer_email' => $data['email'],
                    ]);

                    return $order;
                }
            }
        }

    }
}
