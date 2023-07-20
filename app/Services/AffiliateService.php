<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;


class AffiliateService
{
    const ERROR_MESSAGE = "Something went wrong!";
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        // TODO: Complete this method
        try{
            $rules = [
                'email' => 'required|email',
                'name'  => 'required|string|max:25',
                'commissionRate' => 'required|numeric'
                ];
            $messages = [
                'email.required' => 'The email field is required.',
                'email.email' => 'The email field must be email.',
                'name.required' => 'The name field is required.',
                'name.string' => 'The name must be string.',
                'name.max' => 'The name field must be less than 25 characters.',
                'commissionRate.required' => 'The commission rate field is required.',
                'commissionRate.numeric' => 'The commission rate field must be numeric'
                ];

                $validate = Validator::make($email, $rules, $messages);

                if($validate->fails()){
                    return $validate->getMessageBag();
                }else{
                    $user = User::where('email',$email)->first();
                    $merchant = $merchant->where('display_name',$name)->first();
                    $createDiscountCode = $this->apiService->createDiscountCode($merchant);

                    if($user){
                        $affiliate = Affiliate::create([
                            'user_id' => $user->id,
                            'merchant_id' => $merchant->id,
                            'commission_rate' => $commissionRate,
                            'discount_code' =>  $createDiscountCode['code'],
                        ]);

                        Mail::to($user->email)->send(new AffiliateCreated($affiliate));

                        return $affiliate;
                    }else{
                        return self::ERROR_MESSAGE;
                    }
                }
        }catch(\Exception $error){
            throw new AffiliateCreateException($error);
        }

    }
}
