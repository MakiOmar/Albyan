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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
    public function paymentRequest2(Order $order)
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
    public function paymentRequest(Order $order)
    {
        $token = $this->password; // Replace with your actual token or fetch from config/env
        $merchantCode = $this->merchant_id; // Or from config
        $amount = number_format((float) $order->total_amount, 2, '.', '');
        $currency = 'AED'; // Or use $this->currency if set correctly

        $user = $order->user;

        // Build the payload as per your cURL example
        $payload = [
            "payment" => [
                "amount" => '100.00',
                "currency" => $currency,
                "description" => "Order #" . $order->id,
                "buyer" => [
                    "phone" => "+971500000001",
                    "email" => "otp.success@tabby.ai",
                    "name" => "Test User",
                    "dob" => "2000-01-20T00:00:00.000Z"
                ],
                "shipping_address" => [
                    "city" => "Dubai",
                    "address" => "Dubai",
                    "zip" => "1111"
                ],
                "order" => [
                    "tax_amount" => "0.00",
                    "shipping_amount" => "0.00",
                    "discount_amount" => "0.00",
                    "updated_at" => now()->toIso8601String(),
                    "reference_id" => (string)$order->id,
                    "items" => [
                        [
                            "title" => "Name of the product",
                            "description" => "Description of the product",
                            "quantity" => 1,
                            "unit_price" => '100.00',
                            "discount_amount" => "0.00",
                            "reference_id" => "SKU123",
                            "image_url" => "https://example.com/",
                            "product_url" => "https://example.com/",
                            "gender" => "Kids",
                            "category" => "Clothes",
                            "color" => "white",
                            "product_material" => "cotton",
                            "size_type" => "EU",
                            "size" => "M",
                            "brand" => "Name of the Brand",
                            "is_refundable" => true,
                            "barcode" => "12345678",
                            "ppn" => "MNXT2ZM/A",
                            "seller" => "Name of the Seller"
                        ]
                    ]
                ],
                // Add buyer_history, order_history, meta, attachment if needed
            ],
            "lang" => "ar",
            "merchant_code" => 'bvc',
            "merchant_urls" => [
                "success" => route('tabby.success'), // Use your actual route
                "cancel" => route('tabby.cancel'),
                "failure" => route('tabby.failure')
            ],
            "token" => null
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json'
        ])->post('https://api.tabby.ai/api/v2/checkout', $payload);
        $data = $response->json();
        Log::info('t', $data);
        if ($response->successful()) {
            $data = $response->json();
            // Tabby returns a payment_url or similar in the response
            $paymentUrl = $data['payment_url'] ?? $data['checkout_url'] ?? null;
            if ($paymentUrl) {
                return redirect($paymentUrl);
            } else {
                return response()->json(['error' => 'No payment URL returned from Tabby.'], 500);
            }
        } else {
            // Log or return the error for debugging
            return response()->json(['error' => $response->body()], $response->status());
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
