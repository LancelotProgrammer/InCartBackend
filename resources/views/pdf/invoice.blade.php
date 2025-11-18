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
            border: none;
        }

        th,
        td {
            padding: 6px;
            text-align: right;
            border-bottom: 1px solid #ddd;
        }

        th {
            font-weight: bold;
        }

        .totals {
            margin-top: 15px;
        }

        .totals td {
            padding: 5px 0;
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <div class="flex-between">
            <div>
                <p>اسم العميل: {{ $customer['name'] }}</p>
                <p>رقم الجوال: {{ $customer['phone'] }}</p>
                <p>طريقة الدفع: {{ $order['payment_method_title_ar'] }} / {{ $order['payment_method_title_en'] }}</p>
                <p>العنوان: {{ $customer['address'] }}</p>
            </div>
        </div>
        <hr>
        <h3>تفاصيل الطلب / Order Details</h3>
        <div style="margin-bottom: 20px;">
            @foreach ($categories as $group)
                <h5>الصنف: {{ $group['category_ar'] }}</h5>
                <table>
                    <thead>
                        <tr>
                            <th>المنتج</th>
                            <th>الكمية</th>
                            <th>سعر الوحدة</th>
                            <th>الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($group['products'] as $product)
                            <tr>
                                <td>{{ $product['title_ar'] }}</td>
                                <td>{{ $product['quantity'] }}</td>
                                <td>{{ number_format($product['unit_price'], 2) }}</td>
                                <td>{{ number_format($product['total'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        </div>
        <hr>
        <h3>الإجماليات / Totals</h3>
        <table class="totals">
            <thead>
                <tr>
                    <th>البيان / Description</th>
                    <th>المبلغ / Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="label">المجموع الفرعي / Subtotal</td>
                    <td>{{ number_format($order['subtotal'], 2) }} ر.س / SAR </td>
                </tr>
                @if (number_format($order['discount'], 2) > 0)
                    <tr>
                        <td class="label">خصم / Discount</td>
                        <td>{{ number_format($order['discount'], 2) }} ر.س / SAR </td>
                    </tr>
                @endif
                <tr>
                    <td class="label">رسوم التوصيل / Delivery Fee</td>
                    <td>{{ number_format($order['delivery'], 2) + number_format($order['tax'], 2) + number_format($order['service_fee'], 2)}}
                        ر.س / SAR </td>
                </tr>
                <tr>
                    <td class="label"><strong>الإجمالي / Total (يشمل ضريبة القيمة المضافة / Vat included)</strong></td>
                    <td><strong>{{ number_format($order['total'], 2) }} ر.س / SAR </strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>