<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Bill - {{ $billingRecord->customer->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: white;
            color: black;
        }
        .bill-container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #333;
            padding: 30px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .company-address {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }
        .bill-title {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .customer-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        .info-section h3 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        .info-section p {
            margin: 5px 0;
            font-size: 14px;
        }
        .readings {
            margin-bottom: 30px;
        }
        .readings h3 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        .reading-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        .reading-item {
            text-align: center;
            border: 1px solid #ccc;
            padding: 15px;
            background: #f9f9f9;
        }
        .reading-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        .reading-value {
            font-size: 18px;
            font-weight: bold;
        }
        .charges {
            margin-bottom: 30px;
        }
        .charges h3 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        .charges-table {
            width: 100%;
            border-collapse: collapse;
        }
        .charges-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
        }
        .charges-table .label {
            text-align: left;
        }
        .charges-table .amount {
            text-align: right;
            font-weight: bold;
        }
        .total-row {
            border-top: 2px solid #333;
            font-size: 16px;
            font-weight: bold;
            background: #f0f0f0;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending {
            background: #fef3cd;
            color: #856404;
        }
        .status-paid {
            background: #d4edda;
            color: #155724;
        }
        .status-overdue {
            background: #f8d7da;
            color: #721c24;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 20px;
        }
        @media print {
            body { margin: 0; }
            .bill-container { border: none; }
        }
    </style>
</head>
<body>
    <div class="bill-container">
        <!-- Header -->
        <div class="header">
            <div style="text-align: center; margin-bottom: 20px;">
                <img src="{{ asset('images/mawasa-logo.png') }}" alt="MAWASA Logo" style="height: 80px; width: auto; margin-bottom: 10px;">
            </div>
            <div class="company-name">MANAMBULAN WATERWORKS AND SANITATION INC.</div>
            <div class="company-address">Brgy. Manambulan Tugbok District, Davao City</div>
            <div class="bill-title">Water Bill</div>
        </div>

        <!-- Customer Information -->
        <div class="customer-info">
            <div class="info-section">
                <h3>Customer Information</h3>
                <p><strong>Name:</strong> {{ $billingRecord->customer->name }}</p>
                <p><strong>Account No:</strong> {{ $billingRecord->account_no }}</p>
                <p><strong>Address:</strong> {{ $billingRecord->customer->address }}</p>
                <p><strong>Meter No:</strong> {{ $billingRecord->customer->meter_no }}</p>
                <p><strong>Meter Size:</strong> {{ $billingRecord->customer->meter_size }}</p>
            </div>
            
            <div class="info-section">
                <h3>Billing Information</h3>
                <p><strong>Billing Period:</strong> {{ $billingRecord->getBillingPeriod() }}</p>
                <p><strong>Bill Date:</strong> {{ $billingRecord->created_at->format('M d, Y') }}</p>
                <p><strong>Due Date:</strong> {{ $billingRecord->date_to ? $billingRecord->date_to->format('M d, Y') : 'N/A' }}</p>
                <p><strong>Status:</strong> 
                    <span class="status-badge status-{{ strtolower(str_replace(' ', '-', $billingRecord->bill_status)) }}">
                        {{ $billingRecord->bill_status }}
                    </span>
                </p>
            </div>
        </div>

        <!-- Reading Information -->
        <div class="readings">
            <h3>Reading Information</h3>
            <div class="reading-grid">
                <div class="reading-item">
                    <div class="reading-label">Previous Reading</div>
                    <div class="reading-value">{{ number_format($billingRecord->previous_reading, 2) }}</div>
                </div>
                <div class="reading-item">
                    <div class="reading-label">Current Reading</div>
                    <div class="reading-value">{{ number_format($billingRecord->current_reading, 2) }}</div>
                </div>
                <div class="reading-item">
                    <div class="reading-label">Consumption (m³)</div>
                    <div class="reading-value">{{ number_format($billingRecord->consumption_cu_m, 2) }}</div>
                </div>
            </div>
        </div>

        <!-- Charges Breakdown -->
        <div class="charges">
            <h3>Charges Breakdown</h3>
            <table class="charges-table">
                <tr>
                    <td class="label">Water Consumption ({{ number_format($billingRecord->consumption_cu_m, 2) }} m³ × ₱{{ number_format($billingRecord->base_rate, 2) }})</td>
                    <td class="amount">₱{{ number_format($billingRecord->consumption_cu_m * $billingRecord->base_rate, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">Maintenance Charge</td>
                    <td class="amount">₱{{ number_format($billingRecord->maintenance_charge, 2) }}</td>
                </tr>
                @if($billingRecord->advance_payment > 0)
                <tr style="color: green;">
                    <td class="label">Advance Payment (Credit)</td>
                    <td class="amount">-₱{{ number_format($billingRecord->advance_payment, 2) }}</td>
                </tr>
                @endif
                @if($billingRecord->overdue_penalty > 0)
                <tr style="color: red;">
                    <td class="label">Overdue Penalty</td>
                    <td class="amount">₱{{ number_format($billingRecord->overdue_penalty, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td class="label">TOTAL AMOUNT</td>
                    <td class="amount">₱{{ number_format($billingRecord->total_amount, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Notes -->
        @if($billingRecord->notes)
        <div style="margin-bottom: 30px;">
            <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 10px; border-bottom: 1px solid #ccc; padding-bottom: 5px;">Notes</h3>
            <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;">
                <p style="margin: 0; font-size: 14px;">{{ $billingRecord->notes }}</p>
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your payment!</p>
            <p>For inquiries, please contact us at our office.</p>
            <p>Printed on: {{ now()->format('M d, Y g:i A') }}</p>
        </div>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
