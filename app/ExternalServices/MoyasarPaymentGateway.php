<?php

namespace App\ExternalServices;

use App\Models\Order;
use App\Services\BasePaymentGateway;
use App\Services\PaymentGatewayInterface;
use App\Services\PaymentGatewayResult;
use App\Traits\CanValidatePaymentGatewayData;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Throwable;

class MoyasarPaymentGateway extends BasePaymentGateway implements PaymentGatewayInterface
{
    use CanValidatePaymentGatewayData;

    private Client $client;
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.moyasar.secret');
        $this->baseUrl = config('services.moyasar.baseUrl');
        $this->client = new Client(['base_uri' => $this->baseUrl, 'auth' => [$this->apiKey, '']]);
    }

    public function pay(Order $order, array $payload): PaymentGatewayResult
    {
        Log::channel('app_log')->info('ExternalServices(MoyasarPaymentGateway): Order payment started', [
            'order_id' => $order->id,
            'payload' => $payload,
        ]);

        try {
            $body = [
                'amount' => (int) ($order->total_price * 100), // Moyasar uses SAR * 100
                'currency' => 'SAR',
                'description' => "Order #{$order->order_number}",
                'callback_url' => route('moyasar.pay.callback'),
                'source' => $this->buildSourcePayload($payload),
            ];
            $response = $this->client->post('/v1/payments', [
                'json' => $body,
            ]);

            if ($response->getStatusCode() === 400) {
                Log::channel('app_log')->warning('ExternalServices(MoyasarPaymentGateway): Order payment validation error', [
                    'error' => $response->getBody()->getContents()
                ]);
                throw ValidationException::withMessages([
                    'error' => $response->getBody()->getContents()
                ]);
            }
            if ($response->getStatusCode() === 401) {
                Log::channel('app_log')->critical('ExternalServices(MoyasarPaymentGateway): Order payment authentication error', [
                    'error' => $response->getBody()->getContents()
                ]);
                return new PaymentGatewayResult(false, 'Order payment error', null);
            }
            if ($response->getStatusCode() === 403) {
                Log::channel('app_log')->critical('ExternalServices(MoyasarPaymentGateway): Order payment authorization error', [
                    'error' => $response->getBody()->getContents()
                ]);
                return new PaymentGatewayResult(false, 'Order payment error', null);
            }
            if ($response->getStatusCode() === 500) {
                Log::channel('app_log')->critical('ExternalServices(MoyasarPaymentGateway): Order payment server error', [
                    'error' => $response->getBody()->getContents()
                ]);
                return new PaymentGatewayResult(false, 'Order payment error', null);
            }
            if ($response->getStatusCode() <= 200 || $response->getStatusCode() > 299) {
                Log::channel('app_log')->critical('ExternalServices(MoyasarPaymentGateway): Order payment unknown error', [
                    'error' => $response->getBody()->getContents()
                ]);
                return new PaymentGatewayResult(false, 'Order payment error', null);
            }

            $payment = json_decode($response->getBody()->getContents(), true);

            if ($payment['status'] === 'initiated') {
                $this->createOrderPaymentToken($order, $payment['id']);
                Log::channel('app_log')->notice('ExternalServices(MoyasarPaymentGateway): Order payment marked as initiated', [
                    'payment_id' => $payment
                ]);
            }
            if ($payment['status'] === 'paid') {
                $this->payOrder($order);
                Log::channel('app_log')->notice('ExternalServices(MoyasarPaymentGateway): Order payment marked as paid', [
                    'payment_id' => $payment
                ]);
            }
            if ($payment['status'] === 'failed') {
                Log::channel('app_log')->warning('ExternalServices(MoyasarPaymentGateway): Order payment marked as failed', [
                    'payment_id' => $payment
                ]);
                return new PaymentGatewayResult(false, 'Order payment failed', null);
            }

            Log::channel('app_log')->info('ExternalServices(MoyasarPaymentGateway): Order payment finished successfully', [
                'payment_id' => $payment
            ]);

            return new PaymentGatewayResult(true, 'Order payment finished successfully', $payment);
        } catch (Throwable $e) {
            Log::channel('app_log')->error('ExternalServices(MoyasarPaymentGateway): Order payment Exception', [
                'error' => $e->getMessage()
            ]);
            return new PaymentGatewayResult(false, 'Order payment error', null);
        }
    }

    private function buildSourcePayload(array $payload): array
    {
        if (!isset($payload['type'])) {
            throw new InvalidArgumentException("Payment type is missing.");
        }

        $type = $payload['type'];

        return match ($type) {
            'applepay' => [
                ...$this->validateWalletPayment($payload)
            ],
            'googlepay' => [
                ...$this->validateWalletPayment($payload)
            ],
            'stcpay' => [
                ...$this->validateStcPay($payload),
            ],
            'card' => [
                ...$this->validateCardPayment($payload)
            ],
            default => throw new InvalidArgumentException("Unsupported payment type: {$type}"),
        };
    }

    public function payCallback(Request $request): PaymentGatewayResult
    {
        $paymentId = $request->input('id');
        Log::channel('app_log')->info('ExternalServices(MoyasarPaymentGateway): Order payment callback started', [
            'payment_id' => $paymentId
        ]);

        try {
            $order = Order::where('payment_token', '=', $paymentId)->first();
            if (!$order) {
                Log::channel('app_log')->warning("ExternalServices(MoyasarPaymentGateway): Order payment callback not found");
                throw new InvalidArgumentException("Unknown payment callback");
            }
            $this->payOrder($order);
            Log::channel('app_log')->info('ExternalServices(MoyasarPaymentGateway): Order payment callback finished successfully');
            return new PaymentGatewayResult(true);
        } catch (Throwable $e) {
            Log::channel('app_log')->error('ExternalServices(MoyasarPaymentGateway): Order payment callback exception', [
                'error' => $e->getMessage()
            ]);
            return new PaymentGatewayResult(false, 'Order payment callback error', null);
        }
    }

    public function refund(Order $order, string $paymentToken): PaymentGatewayResult
    {
        Log::channel('app_log')->info('ExternalServices(MoyasarPaymentGateway): Refund order started', [
            'order_id' => $order->id,
            'payment_token' => $paymentToken
        ]);

        try {
            $this->client->post("/v1/payments/{$paymentToken}/refund");
            $this->refundOrder($order);
            Log::channel('app_log')->info('ExternalServices(MoyasarPaymentGateway): Refund order finished successfully');
            return new PaymentGatewayResult(true);
        } catch (Throwable $e) {
            Log::channel('app_log')->error('ExternalServices(MoyasarPaymentGateway): Refund order exception', [
                'error' => $e->getMessage()
            ]);
            return new PaymentGatewayResult(false, 'Refund order error', null);
        }
    }

    public function refundCallback(Request $request): PaymentGatewayResult
    {
        throw new Exception('Refund order callback is not implemented for moyasar payment gateway');
    }
}
