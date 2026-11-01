<?php
/**
 * Hebe Store — M-Pesa Daraja Helper (STK Push / C2B)
 *
 * Usage:
 *   require_once __DIR__ . '/../includes/mpesa.php';
 *
 *   // Initiate payment
 *   $result = mpesa_stk_push('0712345678', 1500, 'Order #HEBE-0001');
 *   if ($result['success']) {
 *       $checkoutRequestId = $result['CheckoutRequestID'];
 *       // Save to payments table and wait for callback
 *   }
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Get an OAuth access token from Daraja.
 *
 * @throws RuntimeException on cURL or API errors.
 */
function mpesa_get_token(): string
{
    $credentials = base64_encode(MPESA_CONSUMER_KEY . ':' . MPESA_CONSUMER_SECRET);
    $url = MPESA_BASE_URL . '/oauth/v1/generate?grant_type=client_credentials';

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Basic ' . $credentials],
        CURLOPT_SSL_VERIFYPEER => APP_ENV === 'production',
        CURLOPT_TIMEOUT        => 15,
    ]);

    $resp = curl_exec($ch);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($err) {
        throw new RuntimeException('M-Pesa token cURL error: ' . $err);
    }

    $data = json_decode($resp, true);
    if (empty($data['access_token'])) {
        throw new RuntimeException('M-Pesa token error: ' . ($resp ?: 'empty response'));
    }

    return $data['access_token'];
}

/**
 * Initiate an STK Push (Lipa Na M-Pesa Online).
 *
 * @param  string $phone     Kenyan number in 254xxxxxxxxx format (e.g. 254712345678)
 * @param  float  $amount    Amount in KES (must be ≥ 1)
 * @param  string $reference Short order reference shown on the customer's phone
 * @return array             ['success' => bool, ...Daraja response fields...]
 */
function mpesa_stk_push(string $phone, float $amount, string $reference): array
{
    $token     = mpesa_get_token();
    $timestamp = date('YmdHis');
    $password  = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);

    // Normalise phone — accept 07xxxxxxxx or +254xxxxxxxx
    $phone = preg_replace('/\D/', '', $phone);
    if (str_starts_with($phone, '0')) {
        $phone = '254' . substr($phone, 1);
    }
    if (str_starts_with($phone, '+')) {
        $phone = substr($phone, 1);
    }

    $payload = [
        'BusinessShortCode' => MPESA_SHORTCODE,
        'Password'          => $password,
        'Timestamp'         => $timestamp,
        'TransactionType'   => 'CustomerPayBillOnline',
        'Amount'            => (int) ceil($amount),     // Daraja rejects decimals
        'PartyA'            => $phone,
        'PartyB'            => MPESA_SHORTCODE,
        'PhoneNumber'       => $phone,
        'CallBackURL'       => MPESA_CALLBACK_URL,
        'AccountReference'  => substr($reference, 0, 12),
        'TransactionDesc'   => 'Hebe Store Payment',
    ];

    $url = MPESA_BASE_URL . '/mpesa/stkpush/v1/processrequest';
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ],
        CURLOPT_SSL_VERIFYPEER => APP_ENV === 'production',
        CURLOPT_TIMEOUT        => 30,
    ]);

    $resp = curl_exec($ch);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($err) {
        return ['success' => false, 'error' => 'cURL error: ' . $err];
    }

    $data = json_decode($resp, true);

    if (!isset($data['ResponseCode'])) {
        return ['success' => false, 'error' => 'Unexpected response: ' . $resp];
    }

    $data['success'] = ($data['ResponseCode'] === '0');
    return $data;
}
