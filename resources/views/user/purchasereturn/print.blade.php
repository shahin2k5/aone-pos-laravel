<!DOCTYPE html>
<html>
<head>
    <title>Purchase Return - #{{ $purchase_return->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .invoice-box { width: 100%; padding: 30px; border: 1px solid #eee; }
        table { width: 100%; line-height: inherit; text-align: left; border-collapse: collapse; }
        table td { padding: 5px; vertical-align: top; }
        table th { padding: 10px; border-bottom: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <h2>Purchase Return</h2>
        <p><strong>Supplier:</strong> {{ $purchase_return->supplier ? $purchase_return->supplier->first_name . ' ' . $purchase_return->supplier->last_name : 'N/A' }}</p>
        <p><strong>Original Purchase ID:</strong> {{ $purchase_return->purchase ? $purchase_return->purchase->invoice_no : 'N/A' }}</p>
        <p><strong>Return Date:</strong> {{ $purchase_return->created_at->format('Y-m-d') }}</p>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Purchase Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchase_return->items as $item)
                    <tr>
                        <td>{{ $item->product ? $item->product->name : 'N/A' }}</td>
                        <td>{{ $item->qnty }}</td>
                        <td>{{ number_format($item->purchase_price, 2) }}</td>
                        <td>{{ number_format($item->qnty * $item->purchase_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p><strong>Total Quantity:</strong> {{ number_format($purchase_return->total_qnty) }}</p>
        <p><strong>Total Amount:</strong> {{ number_format($purchase_return->total_amount, 2) }}</p>
        <p><strong>Return Amount:</strong> {{ number_format($purchase_return->return_amount, 2) }}</p>
        <p><strong>Profit/Loss:</strong> {{ number_format($purchase_return->profit_amount, 2) }}</p>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
