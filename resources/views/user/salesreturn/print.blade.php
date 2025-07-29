<!DOCTYPE html>
<html>
<head>
    <title>Sales Return - #{{ $salesreturn->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .invoice-box { width: 100%; padding: 30px; border: 1px solid #eee; }
        table { width: 100%; line-height: inherit; text-align: left; border-collapse: collapse; }
        table td { padding: 5px; vertical-align: top; }
        table th { padding: 10px; border-bottom: 1px solid #eee; }
        .header { text-align: center; margin-bottom: 30px; }
        .customer-info { margin-bottom: 20px; }
        .summary { margin-top: 20px; padding: 15px; background-color: #f9f9f9; }
        .loss { color: #d9534f; font-weight: bold; }
        .gain { color: #5cb85c; font-weight: bold; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <h1>SALES RETURN</h1>
            <h3>Return #{{ $salesreturn->id }}</h3>
        </div>

        <div class="customer-info">
            <p><strong>Customer:</strong> {{ $salesreturn->customer->first_name }} {{ $salesreturn->customer->last_name }}</p>
            <p><strong>Address:</strong> {{ $salesreturn->customer->address }}</p>
            <p><strong>Phone:</strong> {{ $salesreturn->customer->phone }}</p>
            <p><strong>Original Order ID:</strong> {{ $salesreturn->order_id }}</p>
            <p><strong>Return Date:</strong> {{ $salesreturn->created_at->format('Y-m-d H:i:s') }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($salesreturn->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->qnty }}</td>
                        <td>{{ number_format($item->sell_price, 2) }}</td>
                        <td>{{ number_format($item->qnty * $item->sell_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <p><strong>Total Items Returned:</strong> {{ number_format($salesreturn->total_qnty) }}</p>
            <p><strong>Original Sale Total:</strong> {{ number_format($salesreturn->total_amount, 2) }}</p>
            <p><strong>Return Amount:</strong> {{ number_format($salesreturn->return_amount, 2) }}</p>
            <p><strong>Financial Impact:</strong>
                <span class="{{ $salesreturn->profit_amount < 0 ? 'loss' : 'gain' }}">
                    {{ number_format(abs($salesreturn->profit_amount), 2) }}
                    ({{ $salesreturn->profit_amount < 0 ? 'Loss' : 'Gain' }})
                </span>
            </p>
        </div>

        <div style="margin-top: 30px; text-align: center;">
            <p><strong>Authorized by:</strong> _________________</p>
            <p><strong>Date:</strong> {{ date('Y-m-d') }}</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
