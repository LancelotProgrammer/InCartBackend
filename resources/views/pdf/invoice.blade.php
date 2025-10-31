<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <title>فاتورة | Invoice #{{ $order['number'] }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 13px;
            color: #333;
            font-size: 12pt; 
            line-height: 1.2;
        }

        .header,
        .footer {
            text-align: center;
            margin-bottom: 15px;
        }

        .invoice-box {
            border: 1px solid #ddd;
            padding: 15px;
        }

        .flex-between {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 6px;
            text-align: right;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .totals {
            margin-top: 15px;
        }

        .totals td {
            border: none;
            padding: 5px 0;
        }

        .totals td.label {
            text-align: left;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <div class="header">
            <h2>فاتورة | Invoice</h2>
            <p>رقم الطلب / Order #: <strong>{{ $order['number'] }}</strong></p>
            <p>التاريخ / Date: {{ $order['created_at']->format('Y-m-d H:i') }}</p>
        </div>

        <div class="flex-between">
            <div>
                <h4>بيانات العميل / Customer Details</h4>
                <p>{{ $customer['name'] }}</p>
                <p>{{ $customer['address'] }}</p>
                <p>{{ $customer['phone'] }}</p>
            </div>
            <div style="text-align:left">
                <h4>بيانات المتجر / Company Details</h4>
                <p>{{ $app['name'] }}</p>
            </div>
        </div>

        <h4 style="margin-top:20px;">المنتجات / Products</h4>
        @foreach ($categories as $group)
            <h5>{{ $group['category_ar'] }} / {{ $group['category_en'] }}</h5>
            <table>
                <thead>
                    <tr>
                        <th>المنتج / Product</th>
                        <th>الكمية / Qty</th>
                        <th>سعر الوحدة / Unit Price</th>
                        <th>الإجمالي / Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($group['products'] as $product)
                        <tr>
                            <td>{{ $product['title_ar'] }} / {{ $product['title_en'] }}</td>
                            <td>{{ $product['quantity'] }}</td>
                            <td>{{ number_format($product['unit_price'], 2) }}</td>
                            <td>{{ number_format($product['total'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach

        <table class="totals">
            <tr>
                <td class="label">الإجمالي الفرعي / Subtotal:</td>
                <td>{{ number_format($order['subtotal'], 2) }}</td>
            </tr>
            <tr>
                <td class="label">خصم / Discount:</td>
                <td>-{{ number_format($order['discount'], 2) }}</td>
            </tr>
            <tr>
                <td class="label">رسوم التوصيل / Delivery Fee:</td>
                <td>{{ number_format($order['delivery'], 2) + number_format($order['tax'], 2) + number_format($order['service_fee'], 2)}}</td>
            </tr>
            <tr>
                <td class="label"><strong>الإجمالي الكلي / Total:</strong></td>
                <td><strong>{{ number_format($order['total'], 2) }}</strong></td>
            </tr>
        </table>

        <div class="footer">
            <p>شكراً لتسوقكم معنا! / Thank you for shopping with us!</p>
            <p>تم إنشاء الفاتورة في {{ now()->inApplicationTimezone()->format('Y-m-d H:i') }}</p> 
        </div>
    </div>
</body>

</html>