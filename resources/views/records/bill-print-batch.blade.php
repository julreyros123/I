<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batch Water Bills</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: white; color: black; }
        .bill { max-width: 800px; margin: 0 auto 40px; border: 2px solid #333; padding: 30px; page-break-after: always; position: relative; }
        .bill:last-child { page-break-after: auto; }
        .bill::before { content: ''; position: absolute; inset: 0; background: url('{{ asset('images/mawasa-logo.png') }}') center/60% no-repeat; opacity: .06; pointer-events: none; }
        .header { display: grid; grid-template-columns: auto 1fr; align-items: center; gap: 16px; border-bottom: 2px solid #333; padding-bottom: 14px; margin-bottom: 24px; }
        .logo { height: 70px; width: auto; }
        .company-name { font-size: 22px; font-weight: bold; margin-bottom: 2px; }
        .company-address { font-size: 14px; color: #666; margin-bottom: 4px; }
        .bill-title { font-size: 22px; font-weight: bold; text-transform: uppercase; text-align: right; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .section h3 { font-size: 16px; font-weight: bold; margin-bottom: 10px; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        .section p { margin: 5px 0; font-size: 14px; }
        .readings h3 { font-size: 16px; font-weight: bold; margin-bottom: 15px; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        .reading-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .reading-item { text-align: center; border: 1px solid #ccc; padding: 15px; background: #f9f9f9; }
        .label { font-size: 12px; color: #666; margin-bottom: 5px; }
        .value { font-size: 18px; font-weight: bold; }
        .totals-wrap { display: grid; grid-template-columns: 1fr 280px; gap: 20px; align-items: start; }
        .totals-box { border: 1px solid #ccc; background: #fafafa; border-radius: 6px; padding: 10px 14px; }
        .totals-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 14px; }
        .totals-row.total { border-top: 2px solid #333; margin-top: 8px; padding-top: 10px; font-weight: bold; font-size: 16px; }
        .footer { margin-top: 20px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #ccc; padding-top: 10px; }
        .usage-chart { margin-top: 12px; }
        .usage-chart h3 { font-size: 16px; font-weight: bold; margin: 0 0 8px; }
        .bars { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; align-items: end; height: 120px; }
        .bar { background: #3b82f6; position: relative; border-radius: 4px 4px 0 0; }
        .bar span { position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); font-size: 11px; color: #333; margin-bottom: 4px; }
        .bar .label { position: absolute; bottom: -18px; left: 50%; transform: translateX(-50%); font-size: 11px; color: #555; white-space: nowrap; }
        @media print { body { margin: 0; } .bill { border: none; } }
    </style>
</head>
<body>
    @foreach($records as $billingRecord)
        <div class="bill">
            <div class="header">
                <img src="{{ asset('images/mawasa-logo.png') }}" alt="MAWASA Logo" class="logo">
                <div>
                    <div class="company-name">MANAMBULAN WATERWORKS &amp; SANITATION ASSOCIATION, INC.</div>
                    <div class="company-address">Brgy. Manambulan, Tugbok District, Davao City</div>
                    <div class="company-address">E-mail: — | Phone: —</div>
                </div>
                <div class="bill-title">WATER BILL INVOICE</div>
            </div>

            <div class="grid">
                <div class="section">
                    <h3>Customer Information</h3>
                    <p><strong>Name:</strong> {{ $billingRecord->customer->name }}</p>
                    <p><strong>Account No:</strong> {{ $billingRecord->account_no }}</p>
                    <p><strong>Address:</strong> {{ $billingRecord->customer->address }}</p>
                    <p><strong>Meter No:</strong> {{ $billingRecord->customer->meter_no }}</p>
                    <p><strong>Meter Size:</strong> {{ $billingRecord->customer->meter_size }}</p>
                </div>
                <div class="section">
                    <h3>Billing Information</h3>
                    <p><strong>Billing Period:</strong> {{ $billingRecord->getBillingPeriod() }}</p>
                    <p><strong>Bill Date:</strong> {{ $billingRecord->created_at->format('M d, Y') }}</p>
                    <p><strong>Invoice #:</strong> {{ $billingRecord->id }}</p>
                    <p><strong>Due Date:</strong> {{ $billingRecord->date_to ? $billingRecord->date_to->format('M d, Y') : 'N/A' }}</p>
                </div>
            </div>

            @php
                $consumptionCost = $billingRecord->consumption_cu_m * $billingRecord->base_rate;
                $chargesPlusPenalty = $consumptionCost + ($billingRecord->maintenance_charge ?? 0) + ($billingRecord->overdue_penalty ?? 0);
                $discount = $billingRecord->advance_payment ?? 0; // treat advance payment as discount/credit
                $subtotal = $chargesPlusPenalty; // no VAT
                $tax = 0.00;
                $total = $billingRecord->total_amount;
            @endphp

            <div class="totals-wrap">
                <div class="readings">
                    <h3>Reading Information</h3>
                    <div class="reading-grid">
                        <div class="reading-item">
                            <div class="label">Previous Reading</div>
                            <div class="value">{{ number_format($billingRecord->previous_reading, 2) }}</div>
                        </div>
                        <div class="reading-item">
                            <div class="label">Current Reading</div>
                            <div class="value">{{ number_format($billingRecord->current_reading, 2) }}</div>
                        </div>
                        <div class="reading-item">
                            <div class="label">Consumption (m³)</div>
                            <div class="value">{{ number_format($billingRecord->consumption_cu_m, 2) }}</div>
                        </div>
                    </div>
                    <div style="font-size:12px;color:#666;margin-top:6px;">
                        Rate: ₱{{ number_format($billingRecord->base_rate, 2) }}/m³ • Maintenance: ₱{{ number_format($billingRecord->maintenance_charge, 2) }}
                    </div>
                </div>
                <div class="totals-box">
                    <div class="totals-row"><span>SUBTOTAL</span><span>₱{{ number_format($subtotal, 2) }}</span></div>
                    <div class="totals-row"><span>DISCOUNT (Advance)</span><span>-₱{{ number_format($discount, 2) }}</span></div>
                    <div class="totals-row"><span>TAX</span><span>₱{{ number_format($tax, 2) }}</span></div>
                    <div class="totals-row total"><span>TOTAL</span><span>₱{{ number_format($total, 2) }}</span></div>
                </div>
            </div>

            @php $series = $usageByAccount[$billingRecord->account_no] ?? []; $maxVal = !empty($series) ? max(array_map(fn($i)=>$i['value'],$series)) : 0; $maxVal = $maxVal > 0 ? $maxVal : 1; @endphp
            @if(!empty($series))
            <div class="usage-chart">
                <h3>Usage (Last 5 Months)</h3>
                <div class="bars">
                    @foreach($series as $pt)
                        @php $h = (int) round(($pt['value'] / $maxVal) * 100); @endphp
                        <div class="bar" style="height: {{ $h }}px">
                            <span>{{ number_format($pt['value'], 1) }}</span>
                            <div class="label">{{ $pt['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($billingRecord->notes)
            <div style="margin-bottom: 10px;">
                <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 10px; border-bottom: 1px solid #ccc; padding-bottom: 5px;">Notes</h3>
                <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;">
                    <p style="margin: 0; font-size: 14px;">{{ $billingRecord->notes }}</p>
                </div>
            </div>
            @endif

            <div class="footer">
                <p>Thank you for your business!</p>
                <p>Payment is due within ____ days. For inquiries, please contact our office.</p>
                <p>Printed on: {{ now()->format('M d, Y g:i A') }}</p>
            </div>
        </div>
    @endforeach

    <script>
        window.onload = function() { window.print(); };
    </script>
</body>
</html>
