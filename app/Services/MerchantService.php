<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Error;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Validator;


class MerchantService
{
    const ERROR_MESSAGE = "Something went wrong!";
    const ERROR_MESSAGE_EMAIL = "Email did not found!";
    const SUCCESS_MESSAGE = "Operation perform successfully!";
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
    public function register(array $data): Merchant
    {
        // TODO: Complete this method
        $rules = [
            'domain' => 'required|string|max:100',
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'api_key' => 'required|string'
        ];

        $messages = [
            'domain.required' => 'The domain field is required.',
            'domain.string' => 'The domain must be string.',
            'domain.max' => 'The domain must not exceed 100 characters.',
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be string.',
            'email.required' => 'The email field is required.',
            'email.unique' => 'The email field already exist.',
            'email.email' => 'The email field must be valid email.',
            'api_key.required' => 'The api key field is required.',
            'api_key.string' => 'The api key must be string.'
        ];

        $validate = Validator::make($data, $rules, $messages);

        if($validate->fails()){
            return Response::allow($validate->getMessageBag(),422);
        }else{
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['api_key']
            ]);

            $merchant = Merchant::create([
                'domain' => $data['domain'],
                'display_name' => $data['name'],
                'user_id' => $user->id
            ]);

            return $merchant;
        }
    }

    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
    {
        // TODO: Complete this method
        $rules = [
            'domain' => 'required|string|max:100',
            'name' => 'required|string',
            'email' => 'required|email',
            'api_key' => 'required|string',
            'user_id' => 'required'
        ];

        $messages = [
            'domain.required' => 'The domain field is required.',
            'domain.string' => 'The domain must be string.',
            'domain.max' => 'The domain must not exceed 100 characters.',
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be string.',
            'email.required' => 'The email field is required.',
            'email.email' => 'The email field must be valid email.',
            'api_key.required' => 'The api key field is required.',
            'api_key.string' => 'The api key must be string.',
            'user_id.required' => 'The user id field is required.'
        ];

        $validate = Validator::make($data, $rules, $messages);
        if($validate->fails()){
            return Response::allow($validate->getMessageBag(),422);
        }else{
            $user = User::find($data['user_id']);
            if($user){
                $user->update([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => $data['api_key']
                ]);

                $user->merchant()->update([
                    'display_name' => $data['name']
                ]);

                return Response::allow(self::SUCCESS_MESSAGE, 201);
            }else{
                return Response::denyAsNotFound(self::ERROR_MESSAGE,404);
            }
        }

    }

    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {
        // TODO: Complete this method

        $rules = ['email' => 'required|email'];
        $messages = ['email.required' => 'The email field is required.'];
        $validate = Validator::make($email, $rules, $messages);

        if($validate->fails()){
            return $validate->getMessageBag();
        }else{
            $user = User::where('email', $email)->first();
            if($user){
                return $user->merchant();
            }else{
                return self::ERROR_MESSAGE_EMAIL;
            }
        }

    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    public function payout(Affiliate $affiliate)
    {
        // TODO: Complete this method
        $affiliate = $affiliate->with('orders')->get();

        foreach($affiliate as $affiliated){
            foreach($affiliated->orders as $orders){
                if($orders->payout_status === "unpaid"){
                    PayoutOrderJob::dispatch($orders);
                }
            }
        }
    }
}
