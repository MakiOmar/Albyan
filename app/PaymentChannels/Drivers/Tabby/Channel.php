<?php

namespace App\PaymentChannels\Drivers\Tabby;

use App\Models\Order;
use App\Models\PaymentChannel;
use App\PaymentChannels\BasePaymentChannel;
use App\PaymentChannels\IChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tabby\Services\TabbyService;
use Tabby\Models\Buyer;
use Tabby\Models\Order as TabbyOrder;
use Tabby\Models\ShippingAddress;
use Tabby\Models\OrderItem;

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
        $this->setCredentialItems($paymentChannel);
    }

    private function handleConfigs()
    {
    }

    /**
     * @throws \Exception
     */
    public function paymentRequest(Order $order)
    {
        $user_id = $order->user_id;
        $price = $this->makeAmountByCurrency($order->total_amount, $this->currency);
        $price = number_format((float) $price, 2, '.', '');
        $generalSettings = getGeneralSettings();
        $currency = currency();
        $user = $order->user;
        $tabbyService = new TabbyService(
            merchantCode: $this->merchant_id,
            publicKey: $this->integerity_salt,
            secretKey: $this->password,
            currency: 'AED' // Optional, default is SAR
        );
        
        try {

            //$buyer = new Buyer(
            //    phone: !empty($user->mobile) ? preg_replace('/\s+/', '', $user->mobile) : $generalSettings['site_phone'],
            //    email: $user->email ?? $generalSettings['site_email'],
            //    name: $user->full_name,
            //    dob : ''
            //);
            $buyer = new Buyer(
                phone: '+971500000001',
                email: 'otp.success@tabby.ai',
                name: 'Test',
                dob: "2000-08-24"
            );
            
            $items = [];
            foreach ($order->orderItems as $item) {
                $items[] = new OrderItem(
                    title: $this->getItemTitle($item),
                    category: 'general',
                    unitPrice: (float) $item->amount,
                    quantity: (int) ($item->quantity ?? 1),
                    referenceId: 'order-item-' . $item->id,
                    description: $this->getItemDescription($item)
                );
            }
            
        
            $tabbyOrder = new TabbyOrder(
                referenceId: 'order-' . $order->id,
                items: $items
            );
            $shippingAddress = new ShippingAddress(
                city: 'Dubai',
                address: 'Albyan instiute',
                zip: '25314',
            );
        
            // Create a checkout session
            $checkoutSession = $tabbyService->createSession(
                amount: $price,
                buyer: $buyer,
                order: $tabbyOrder,
                shippingAddress: $shippingAddress,
                description: 'طلب رقم ' . $order->id,
                successCallback: 'https://example.com/success',
                cancelCallback: 'https://example.com/cancel',
                failureCallback: 'https://example.com/failure',
                // lang: 'ar',            // optional
                // buyerHistory: $buyerHistory,   // optional
                // orderHistory: $orderHistory,   // optional
            );
            
            // Fetch the payment url from the checkout session
            $paymentUrl = $checkoutSession->getPaymentUrl();
        
            // Redirect to the payment page
            return redirect($paymentUrl);
        } catch (Exception $e) {
            // Handle exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }


    }

    private function makeCallbackUrl($order, $status)
    {

    }

    public function verify(Request $request)
    {
        $this->handleConfigs();

    }
    private function getItemTitle($item)
    {
        if ($item->webinar) {
            return $item->webinar->title;
        } elseif ($item->product) {
            return $item->product->title;
        } elseif ($item->bundle) {
            return $item->bundle->title;
        } elseif ($item->registrationPackage) {
            return $item->registrationPackage->title;
        } elseif ($item->subscribe) {
            return $item->subscribe->title;
        }
    
        return 'عنصر بدون عنوان';
    }
    
    private function getItemDescription($item)
    {
        return 'طلب من نوع: ' . ($item->type ?? 'غير محدد') . ' - رقم: ' . $item->id;
    }

}
