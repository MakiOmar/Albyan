<?php

namespace App\PaymentChannels\Drivers\Tabby;

use App\Models\Order;
use App\Models\PaymentChannel;
use App\PaymentChannels\BasePaymentChannel;
use App\PaymentChannels\IChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Channel extends BasePaymentChannel implements IChannel
{
    protected $currency;
    protected $test_mode;
    protected $merchant_id;
    protected $password;
    protected $integerity_salt;
    protected $endpoint;
    protected $return_url;


    protected array $credentialItems = [
        "merchant_id",
        "password",
        "integerity_salt",
        "endpoint",
    ];


    /**
     * Channel constructor.
     * @param PaymentChannel $paymentChannel
     */
    public function __construct(PaymentChannel $paymentChannel)
    {
        $this->currency = currency();
        
    }

    private function handleConfigs()
    {
    }

    /**
     * @throws \Exception
     */
    public function paymentRequest(Order $order)
    {
        $this->handleConfigs();

    }

    private function makeCallbackUrl($order, $status)
    {

    }

    public function verify(Request $request)
    {
        $this->handleConfigs();

    }
}
