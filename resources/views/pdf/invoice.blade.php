<!DOCTYPE html>
<html>

<head>
    <title>Invoice - Order #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            margin: 20px;
            color: #333;
        }

        .invoice-box {
            width: 100%;
            border: 1px solid #eee;
            padding: 5px;
            /* line-height: 1.6; */
        }

        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .company-details {
            text-align: right;
        }

        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #444;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: #f5f5f5;
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }

        .totals {
            margin-top: 20px;
            width: 100%;
        }

        .totals td {
            padding: 8px;
        }

        .totals .label {
            text-align: right;
            font-weight: bold;
        }

        .totals .amount {
            text-align: right;
        }

        .footer {
            margin-top: 30px;
            font-size: 12px;
            text-align: center;
            color: #777;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <!-- Header -->
        <div class="header">
            <div>
                <h2 class="invoice-title">Invoice</h2>
                <p>Order #: <strong>{{ $order->order_number }}</strong></p>
                <p>Date: {{ $order->created_at->format('d M Y') }}</p>
            </div>
            <div class="company-details">
                <h3>Supermarket</h3>
                <p>123 Market Street</p>
                <p>City, Country</p>
                <p>Email: support@supermarket.com</p>
            </div>
        </div>

        <!-- Customer -->
        <div>
            <h4>Customer Details</h4>
            <p><strong>{{ $order->customer->name }}</strong></p>
            <p>{{ $order->userAddress?->address ?? 'No address provided' }}</p>
            <p>Email: {{ $order->customer->email }}</p>
            <p>Phone: {{ $order->customer->phone ?? '-' }}</p>
        </div>

        <!-- Products -->
        <h4 style="margin-top:20px;">Order Items</h4>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th style="text-align:right;">Quantity</th>
                    <th style="text-align:right;">Unit Price</th>
                    <th style="text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->cartProducts as $cartProduct)
                    <tr>
                        {{-- <td>{{ $cartProduct->product->title }}</td> --}}
                        <td>title</td>
                        <td style="text-align:right;">{{ $cartProduct->quantity }}</td>
                        <td style="text-align:right;">
                            123
                            {{-- {{ number_format($cartProduct->product->branchProduct->price, 2) }} --}}
                        </td>
                        <td style="text-align:right;">
                            123
                            {{-- {{ number_format($cartProduct->quantity * $cartProduct->product->branchProduct->price, 2) }} --}}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <table class="totals">
            <tr>
                <td class="label">Subtotal:</td>
                <td class="amount">{{ number_format($order->subtotal_price, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Coupon Discount:</td>
                <td class="amount">-{{ number_format($order->coupon_discount, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Delivery Fee:</td>
                <td class="amount">{{ number_format($order->delivery_fee, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Service Fee:</td>
                <td class="amount">{{ number_format($order->service_fee, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Tax:</td>
                <td class="amount">{{ number_format($order->tax_amount, 2) }}</td>
            </tr>
            <tr>
                <td class="label"><strong>Total:</strong></td>
                <td class="amount"><strong>{{ number_format($order->total_price, 2) }}</strong></td>
            </tr>
        </table>

        <!-- Footer -->
        <div class="footer">
            Thank you for shopping with us!<br>
            This invoice was generated on {{ now()->format('d M Y H:i') }}
        </div>
    </div>
</body>

</html>