<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Purchase - {{ $purchase->purchase_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .print-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            gap: 10px;
        }
        
        .print-controls button {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .print-controls button:hover {
            background: #0056b3;
        }
        
        .print-controls button.close-btn {
            background: #6c757d;
        }
        
        .print-controls button.close-btn:hover {
            background: #5a6268;
        }
        
        .invoice-container {
        max-width: 800px;
        margin: 0 auto;
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .header {
        text-align: center;
        border-bottom: 2px solid #007bff;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }

    .company-name {
        font-size: 24px;
        font-weight: bold;
        color: #007bff;
        margin-bottom: 8px;
    }

    .invoice-title {
        font-size: 20px;
        color: #333;
        margin-bottom: 4px;
    }

    .invoice-number {
        font-size: 16px;
        color: #666;
    }

    .invoice-details {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .details-section {
        flex: 1;
        margin-right: 15px;
    }

    .details-section:last-child {
        margin-right: 0;
    }

    .section-title {
        font-size: 14px;
        font-weight: bold;
        color: #333;
        margin-bottom: 8px;
        border-bottom: 1px solid #ddd;
        padding-bottom: 4px;
    }

    .detail-row {
        margin-bottom: 6px;
    }

    .detail-label {
        font-weight: bold;
        color: #555;
        display: inline-block;
        width: 120px;
    }

    .detail-value {
        color: #333;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    .items-table th,
    .items-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    .items-table th {
        background-color: #f8f9fa;
        font-weight: bold;
        color: #333;
        font-size: 12px;
    }

    .items-table tr:nth-child(even) {
        background-color: #f8f9fa;
    }

    .text-right {
        text-align: right;
    }

    .text-center {
        text-align: center;
    }

    .totals-section {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 2px solid #007bff;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        padding: 3px 0;
    }

    .total-label {
        font-weight: bold;
        color: #333;
    }

    .total-value {
        font-weight: bold;
        color: #007bff;
    }

    .grand-total {
        font-size: 18px;
        border-top: 2px solid #007bff;
        padding-top: 10px;
        margin-top: 10px;
    }

    .footer {
        margin-top: 40px;
        text-align: center;
        color: #666;
        border-top: 1px solid #ddd;
        padding-top: 20px;
    }

    .section-signature {
        margin-top: 40px;
        padding-top: 20px;
        border-top: 1px solid #ddd;
    }

    .signature-line {
        width: 100%;
        height: 1px;
        background-color: #333;
        margin: 10px 0;
    }

    @media print {

        /* Hide app navigation elements */
        .navbar,
        .navbar-brand,
        .navbar-nav,
        .sidebar,
        .layout-navbar,
        .layout-menu,
        .btn,
        .d-flex.justify-content-between,
        .card-header,
        .breadcrumb,
        .page-header {
            display: none !important;
        }

        /* Reset body for print */
        body {
            margin: 0 !important;
            padding: 0 !important;
            background: white !important;
            font-size: 12px !important;
            line-height: 1.4 !important;
        }

        /* Invoice container print styles */
        .invoice-container {
            box-shadow: none !important;
            margin: 0 !important;
            padding: 20px !important;
            max-width: none !important;
            width: 100% !important;
            background: white !important;
            border: none !important;
            border-radius: 0 !important;
        }

        /* Optimize table for print */
        .items-table {
            font-size: 11px !important;
            border-collapse: collapse !important;
        }

        .items-table th,
        .items-table td {
            padding: 6px !important;
            border: 1px solid #000 !important;
        }

        /* Ensure single page */
        .invoice-container {
            page-break-inside: avoid;
        }

        .items-table {
            page-break-inside: avoid;
        }

        /* Optimize spacing for print */
        .header {
            padding-bottom: 10px !important;
            margin-bottom: 15px !important;
        }

        .invoice-details {
            margin-bottom: 15px !important;
        }

        .items-table {
            margin-bottom: 15px !important;
        }

        .totals-section {
            margin-top: 10px !important;
            padding-top: 10px !important;
        }

        .footer {
            margin-top: 20px !important;
            padding-top: 15px !important;
        }

        .section-signature {
            margin-top: 30px !important;
            padding-top: 15px !important;
        }

        .signature-line {
            height: 1px !important;
            background-color: #000 !important;
            margin: 8px 0 !important;
        }

        /* Hide print controls when printing */
        .print-controls {
            display: none !important;
        }
    }
    </style>
</head>
<body>
    <div class="print-controls">
        <button onclick="window.print()">🖨️ Print</button>
        <button class="close-btn" onclick="window.close()">✕ Close</button>
    </div>

    <div class="invoice-container">
    <!-- Header -->
    <div class="header">
        <div class="company-name">TMS IIUC</div>
        <div class="invoice-title">Purchase Invoice</div>
        <div class="invoice-number">Invoice #: {{ $purchase->purchase_number }}</div>
    </div>

    <!-- Invoice Details -->
    <div class="invoice-details">
        <div class="details-section">
            <div class="section-title">Supplier Information</div>
            <div class="detail-row">
                <span class="detail-label">Name:</span>
                <span class="detail-value">{{ $purchase->supplier->supplier_name ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Mobile:</span>
                <span class="detail-value">{{ $purchase->supplier->mobile ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Email:</span>
                <span class="detail-value">{{ $purchase->supplier->email ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Address:</span>
                <span class="detail-value">{{ $purchase->supplier->address ?? 'N/A' }}</span>
            </div>
        </div>

        <div class="details-section">
            <div class="section-title">Invoice Details</div>
            <div class="detail-row">
                <span class="detail-label">Date:</span>
                <span class="detail-value">{{ \Carbon\Carbon::parse($purchase->date)->format('M d, Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Payment Method:</span>
                <span class="detail-value">{{ $purchase->paymentMethod->payment_method_name ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Paid Amount:</span>
                <span class="detail-value">৳{{ number_format($purchase->paid, 2) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Due Amount:</span>
                <span class="detail-value">৳{{ number_format($purchase->due, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th class="text-center">#</th>
                <th>Item Name</th>
                <th>Unit</th>
                <th class="text-center">Quantity</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Total Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchase->purchaseItems as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->item->item_name ?? 'N/A' }}</td>
                <td>{{ $item->unit->unit_name ?? 'N/A' }}</td>
                <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                <td class="text-right">৳{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">৳{{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals Section -->
    <div class="totals-section">
        <div class="total-row">
            <span class="total-label">Net Total:</span>
            <span class="total-value">৳{{ number_format($purchase->net_total, 2) }}</span>
        </div>
        <div class="total-row">
            <span class="total-label">Paid Amount:</span>
            <span class="total-value">৳{{ number_format($purchase->paid, 2) }}</span>
        </div>
        <div class="total-row">
            <span class="total-label">Due Amount:</span>
            <span class="total-value">৳{{ number_format($purchase->due, 2) }}</span>
        </div>
    </div>

    @if($purchase->remarks)
    <div class="section-title">Remarks</div>
    <div style="margin-top: 10px; padding: 10px; background-color: #f8f9fa; border-radius: 5px;">
        {{ $purchase->remarks }}
    </div>
    @endif
    <div class="section-signature">
        <div class="row">
            <div class="col-sm-4 mt-4">
                <p></p>
                <div class="signature-line"></div>
                <p>Authorized Signature</p>
            </div>
            <div class="col-sm-4  mt-4">
                <p></p>
                <div class="signature-line"></div>
                <p>Approved By</p>
            </div>
            <div class="col-sm-4">
                <p>{{ $purchase->user->name ?? 'N/A' }}</p>
                <div class="signature-line"></div>
                <p>Prepared By</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Thank you for your business!</p>
        <p>Generated on {{ \Carbon\Carbon::now()->format('M d, Y \a\t h:i A') }}</p>
    </div>
</div>

<script>
    // Auto-print when page loads (optional - commented out)
    // window.onload = function() {
    //     setTimeout(function() {
    //         window.print();
    //     }, 500);
    // };
</script>
</body>
</html>