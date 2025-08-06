<!DOCTYPE html>
<html>
<head>
    <title>Purchase Return - #{{ $purchase_return->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        .invoice-container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .invoice-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .invoice-header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 300;
        }

        .invoice-header .invoice-number {
            font-size: 1.2em;
            opacity: 0.9;
        }

        .invoice-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            padding: 30px;
            border-bottom: 1px solid #eee;
        }

        .info-section h3 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 1.1em;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .info-section p {
            margin-bottom: 8px;
            color: #666;
        }

        .info-section strong {
            color: #333;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }

        .items-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #dee2e6;
        }

        .items-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        .items-table tr:hover {
            background: #f8f9fa;
        }

        .product-name {
            font-weight: 500;
            color: #333;
        }

        .quantity {
            text-align: center;
            font-weight: 500;
        }

        .price {
            text-align: right;
            font-weight: 500;
        }

        .total {
            text-align: right;
            font-weight: 600;
            color: #667eea;
        }

        .invoice-summary {
            background: #f8f9fa;
            padding: 30px;
            border-top: 1px solid #eee;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
        }

        .summary-row.total-row {
            border-top: 2px solid #667eea;
            margin-top: 15px;
            padding-top: 15px;
            font-size: 1.2em;
            font-weight: 600;
            color: #667eea;
        }

        .invoice-footer {
            background: #333;
            color: white;
            padding: 20px 30px;
            text-align: center;
            font-size: 0.9em;
        }

        .company-logo {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .payment-status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 500;
            margin-top: 10px;
        }

        .status-paid {
            background: #28a745;
            color: white;
        }

        .status-pending {
            background: #ffc107;
            color: #333;
        }

        .loss {
            color: #dc3545;
            font-weight: bold;
        }

        .gain {
            color: #28a745;
            font-weight: bold;
        }

        @media print {
            body { background: white; }
            .invoice-container {
                box-shadow: none;
                margin: 0;
                border-radius: 0;
            }
            .invoice-header { background: #667eea !important; }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="company-logo">AOne POS</div>
            <h1>PURCHASE RETURN</h1>
            <div class="invoice-number">Return #{{ $purchase_return->id }}</div>
        </div>

        <!-- Invoice Info -->
        <div class="invoice-info">
            <div class="info-section">
                <h3>Supplier Information</h3>
                <p><strong>Name:</strong> {{ $purchase_return->supplier ? $purchase_return->supplier->first_name . ' ' . $purchase_return->supplier->last_name : 'N/A' }}</p>
                <p><strong>Address:</strong> {{ $purchase_return->supplier->address ?? 'N/A' }}</p>
                <p><strong>Phone:</strong> {{ $purchase_return->supplier->phone ?? 'N/A' }}</p>
            </div>
            <div class="info-section">
                <h3>Return Information</h3>
                <p><strong>Original Purchase ID:</strong> {{ $purchase_return->purchase ? $purchase_return->purchase->invoice_no : 'N/A' }}</p>
                <p><strong>Return Date:</strong> {{ $purchase_return->created_at->format('F j, Y') }}</p>
                <p><strong>Return Time:</strong> {{ $purchase_return->created_at->format('g:i A') }}</p>
                <p><strong>Items:</strong> {{ $purchase_return->items->count() }} products</p>
            </div>
        </div>

        <!-- Items Table -->
        <div style="padding: 0 30px;">
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 40%;">Product</th>
                        <th style="width: 15%; text-align: center;">Quantity</th>
                        <th style="width: 20%; text-align: right;">Unit Price</th>
                        <th style="width: 25%; text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchase_return->items as $item)
                        <tr>
                            <td class="product-name">{{ $item->product ? $item->product->name : 'N/A' }}</td>
                            <td class="quantity">{{ $item->qnty }}</td>
                            <td class="price">{{ number_format($item->purchase_price, 2) }}</td>
                            <td class="total">{{ number_format($item->qnty * $item->purchase_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Summary -->
        <div class="invoice-summary">
            <div class="summary-row">
                <span>Total Quantity:</span>
                <span>{{ number_format($purchase_return->total_qnty) }}</span>
            </div>
            <div class="summary-row">
                <span>Total Amount:</span>
                <span>{{ number_format($purchase_return->total_amount, 2) }}</span>
            </div>
            <div class="summary-row total-row">
                <span>Return Amount:</span>
                <span>{{ number_format($purchase_return->return_amount, 2) }}</span>
            </div>
            <div class="summary-row">
                <span>Financial Impact:</span>
                <span class="{{ $purchase_return->profit_amount < 0 ? 'loss' : 'gain' }}">
                    {{ number_format(abs($purchase_return->profit_amount), 2) }}
                    ({{ $purchase_return->profit_amount < 0 ? 'Loss' : 'Gain' }})
                </span>
            </div>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <p>Authorized by: _________________</p>
            <p>Date: {{ date('Y-m-d') }}</p>
            <p>Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
