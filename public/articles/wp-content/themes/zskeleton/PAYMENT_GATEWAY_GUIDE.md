# Payment Gateway Integration Guide

## Overview

The ZSkeleton theme includes comprehensive payment gateway support for both Stripe and PayPal, with sandbox (testing) and live (production) modes for each gateway.

## Configuration

### Access Payment Settings

1. Go to **WordPress Admin → Appearance → ZSkeleton Settings**
2. Click on the **Payment** tab
3. Configure your preferred payment gateway

### Settings Available

#### Gateway Selection
- **Payment Gateway**: Choose between Stripe or PayPal
- **Currency**: Select your payment currency (USD, EUR, GBP, AUD, CAD, NZD, JPY)

#### Stripe Configuration
- **Mode Toggle**: Choose between Sandbox (Test) or Live (Production)
- **Sandbox Credentials**:
  - Publishable Key (starts with `pk_test_`)
  - Secret Key (starts with `sk_test_`)
- **Live Credentials**:
  - Publishable Key (starts with `pk_live_`)
  - Secret Key (starts with `sk_live_`)

#### PayPal Configuration
- **Mode Toggle**: Choose between Sandbox (Test) or Live (Production)
- **Sandbox Credentials**:
  - Client ID (from PayPal Developer Dashboard)
  - Secret (from PayPal Developer Dashboard)
- **Live Credentials**:
  - Client ID (from PayPal Dashboard)
  - Secret (from PayPal Dashboard)

### Visual Status Indicators

The settings page shows real-time status:
- ✅ **Green**: Gateway is configured and ready
- ⚠️ **Yellow**: Current mode needs configuration
- Active mode and currency displayed at the top

---

## Payment Gateway Class

### ZSkeleton_Payment_Gateway

Central class for handling payment operations.

#### Methods

##### `get_active_gateway()`
Get the currently selected payment gateway.

**Returns:** `string` - 'stripe' or 'paypal'

```php
$gateway = ZSkeleton_Payment_Gateway::get_active_gateway();
// Returns: 'stripe' or 'paypal'
```

##### `get_currency()`
Get the configured payment currency.

**Returns:** `string` - Currency code (USD, EUR, etc.)

```php
$currency = ZSkeleton_Payment_Gateway::get_currency();
// Returns: 'USD'
```

##### `is_stripe_configured()`
Check if Stripe is properly configured for current mode.

**Returns:** `bool` - True if configured

```php
if (ZSkeleton_Payment_Gateway::is_stripe_configured()) {
    // Stripe is ready
}
```

##### `is_paypal_configured()`
Check if PayPal is properly configured for current mode.

**Returns:** `bool` - True if configured

```php
if (ZSkeleton_Payment_Gateway::is_paypal_configured()) {
    // PayPal is ready
}
```

##### `is_payment_ready()`
Check if the active gateway is configured.

**Returns:** `bool` - True if ready

```php
if (ZSkeleton_Payment_Gateway::is_payment_ready()) {
    // Payment system is ready
}
```

##### `get_stripe_publishable_key()`
Get Stripe publishable key for current mode.

**Returns:** `string` - Publishable key

```php
$pub_key = ZSkeleton_Payment_Gateway::get_stripe_publishable_key();
// Returns: pk_test_... or pk_live_...
```

##### `get_stripe_secret_key()`
Get Stripe secret key for current mode.

**Returns:** `string` - Secret key

```php
$secret = ZSkeleton_Payment_Gateway::get_stripe_secret_key();
// Returns: sk_test_... or sk_live_...
```

##### `get_paypal_client_id()`
Get PayPal client ID for current mode.

**Returns:** `string` - Client ID

```php
$client_id = ZSkeleton_Payment_Gateway::get_paypal_client_id();
```

##### `get_paypal_secret()`
Get PayPal secret for current mode.

**Returns:** `string` - Secret

```php
$secret = ZSkeleton_Payment_Gateway::get_paypal_secret();
```

##### `get_paypal_api_url()`
Get PayPal API base URL for current mode.

**Returns:** `string` - API URL

```php
$api_url = ZSkeleton_Payment_Gateway::get_paypal_api_url();
// Returns: https://api.sandbox.paypal.com or https://api.paypal.com
```

##### `format_amount($amount, $currency)`
Format amount for payment gateway (convert to cents).

**Parameters:**
- `$amount` (float) - Amount in standard units
- `$currency` (string, optional) - Currency code

**Returns:** `int` - Amount in smallest currency unit

```php
$amount_cents = ZSkeleton_Payment_Gateway::format_amount(50.00);
// Returns: 5000 (for USD)

$amount_yen = ZSkeleton_Payment_Gateway::format_amount(5000, 'JPY');
// Returns: 5000 (JPY is zero-decimal)
```

##### `format_amount_display($amount, $currency)`
Format amount for display (convert from cents).

**Parameters:**
- `$amount` (int) - Amount in smallest currency unit
- `$currency` (string, optional) - Currency code

**Returns:** `float` - Amount in standard units

```php
$display_amount = ZSkeleton_Payment_Gateway::format_amount_display(5000);
// Returns: 50.00 (for USD)
```

##### `get_currency_symbol($currency)`
Get currency symbol.

**Parameters:**
- `$currency` (string, optional) - Currency code

**Returns:** `string` - Currency symbol

```php
$symbol = ZSkeleton_Payment_Gateway::get_currency_symbol('USD');
// Returns: '$'

$symbol = ZSkeleton_Payment_Gateway::get_currency_symbol('EUR');
// Returns: '€'
```

##### `log_transaction($data)`
Log payment transaction.

**Parameters:**
- `$data` (array) - Transaction data

```php
ZSkeleton_Payment_Gateway::log_transaction(array(
    'type'      => 'payment',
    'gateway'   => 'stripe',
    'amount'    => 50.00,
    'currency'  => 'USD',
    'status'    => 'completed',
    'reference' => 'ch_1234567890',
));
```

---

## Usage Examples

### Example 1: Check Payment Readiness

```php
if (ZSkeleton_Payment_Gateway::is_payment_ready()) {
    $gateway = ZSkeleton_Payment_Gateway::get_active_gateway();
    echo "Payment system ready using: " . $gateway;
} else {
    echo "Please configure payment gateway in theme settings";
}
```

### Example 2: Display Price with Currency

```php
$amount = 50.00; // Tool submission fee
$currency = ZSkeleton_Payment_Gateway::get_currency();
$symbol = ZSkeleton_Payment_Gateway::get_currency_symbol();

echo "Submission Fee: " . $symbol . number_format($amount, 2) . ' ' . $currency;
// Output: Submission Fee: $50.00 USD
```

### Example 3: Prepare Amount for Stripe

```php
$amount = 50.00; // $50.00
$amount_cents = ZSkeleton_Payment_Gateway::format_amount($amount);

// Use $amount_cents (5000) in Stripe API call
// Stripe expects amounts in cents
```

### Example 4: Conditional Gateway Integration

```php
$gateway = ZSkeleton_Payment_Gateway::get_active_gateway();

if ($gateway === 'stripe' && ZSkeleton_Payment_Gateway::is_stripe_configured()) {
    // Load Stripe integration
    $pub_key = ZSkeleton_Payment_Gateway::get_stripe_publishable_key();
    // Initialize Stripe checkout
    
} elseif ($gateway === 'paypal' && ZSkeleton_Payment_Gateway::is_paypal_configured()) {
    // Load PayPal integration
    $client_id = ZSkeleton_Payment_Gateway::get_paypal_client_id();
    // Initialize PayPal checkout
}
```

---

## Setup Instructions

### Stripe Setup

1. **Create Stripe Account**
   - Go to [stripe.com](https://stripe.com)
   - Sign up for an account

2. **Get API Keys**
   - Go to [Stripe Dashboard → API Keys](https://dashboard.stripe.com/apikeys)
   - Copy your Publishable key and Secret key
   - For testing, use test keys (pk_test_ and sk_test_)
   - For production, use live keys (pk_live_ and sk_live_)

3. **Configure in WordPress**
   - Go to Appearance → ZSkeleton Settings → Payment
   - Select "Stripe" as Payment Gateway
   - Choose mode (Sandbox for testing, Live for production)
   - Paste your keys in the appropriate fields
   - Save settings

### PayPal Setup

1. **Create PayPal Developer Account**
   - Go to [developer.paypal.com](https://developer.paypal.com)
   - Log in with your PayPal account

2. **Create App**
   - Go to Dashboard → My Apps & Credentials
   - Click "Create App"
   - Choose "Merchant" as app type
   - Copy Client ID and Secret
   - For testing, use Sandbox credentials
   - For production, use Live credentials

3. **Configure in WordPress**
   - Go to Appearance → ZSkeleton Settings → Payment
   - Select "PayPal" as Payment Gateway
   - Choose mode (Sandbox for testing, Live for production)
   - Paste your Client ID and Secret in the appropriate fields
   - Save settings

---

## Testing

### Sandbox Mode

**Always test in sandbox mode first!**

#### Stripe Test Cards
- **Successful payment**: 4242 4242 4242 4242
- **Declined payment**: 4000 0000 0000 0002
- **Requires authentication**: 4000 0025 0000 3155
- Any future expiry date, any 3-digit CVC

#### PayPal Sandbox
- Use PayPal sandbox accounts
- Test with fake money
- Full payment flow simulation

### Going Live

1. **Test thoroughly** in sandbox mode
2. Switch to **Live mode** in settings
3. Enter **live credentials**
4. **Test with small real payment**
5. Monitor transactions in gateway dashboard

---

## Security Best Practices

1. **Never commit API keys** to version control
2. **Use environment variables** for sensitive data (optional)
3. **Keep secret keys secret** - never expose in frontend code
4. **Use HTTPS** - required for payment processing
5. **Test in sandbox** before going live
6. **Monitor transactions** regularly
7. **Keep gateway accounts** secure with 2FA

---

## Supported Currencies

- **USD** - US Dollar ($)
- **EUR** - Euro (€)
- **GBP** - British Pound (£)
- **AUD** - Australian Dollar (A$)
- **CAD** - Canadian Dollar (C$)
- **NZD** - New Zealand Dollar (NZ$)
- **JPY** - Japanese Yen (¥) - Zero decimal currency

### Zero-Decimal Currencies

These currencies don't use cents/pence:
- JPY, KRW, VND, and others
- Amounts are in the base unit, not multiplied by 100

---

## Troubleshooting

### Payments Not Working?

1. **Check configuration**
   - Go to Payment settings
   - Verify status indicators show green checkmarks
   - Ensure correct mode is selected

2. **Check credentials**
   - Verify keys are correct
   - Ensure no extra spaces
   - Test keys should start with pk_test_/sk_test_
   - Live keys should start with pk_live_/sk_live_

3. **Check browser console**
   - Look for JavaScript errors
   - Verify gateway script loading

4. **Check error logs**
   - Review `wp-content/debug.log`
   - Look for payment-related errors

### Mode Confusion?

- **Sandbox/Test Mode**: For testing, uses test keys, no real money
- **Live/Production Mode**: For real payments, uses live keys, processes real money

Always use sandbox mode for development and testing!

---

## Next Steps

The payment infrastructure is now ready. Next implementations:

1. **Stripe Checkout Integration** - Add Stripe payment forms
2. **PayPal Checkout Integration** - Add PayPal payment buttons
3. **Payment Processing** - Handle successful payments
4. **Order Management** - Track payment status
5. **Email Notifications** - Send payment confirmations
6. **Receipt Generation** - Create payment receipts

---

## Support Resources

### Stripe
- Documentation: [stripe.com/docs](https://stripe.com/docs)
- API Reference: [stripe.com/docs/api](https://stripe.com/docs/api)
- Testing Guide: [stripe.com/docs/testing](https://stripe.com/docs/testing)

### PayPal
- Documentation: [developer.paypal.com/docs](https://developer.paypal.com/docs)
- REST API: [developer.paypal.com/docs/api](https://developer.paypal.com/docs/api)
- Sandbox Guide: [developer.paypal.com/docs/api-basics/sandbox](https://developer.paypal.com/docs/api-basics/sandbox)

