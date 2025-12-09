<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batch Water Bills</title>
    <style>
        :root {
            --primary: #0f172a;
            --muted: #475569;
        }
        * { box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; margin: 0; padding: 24px; background: #f5f7ff; color: var(--primary); }
        .bill { max-width: 880px; margin: 0 auto 48px; background: #ffffff; border-radius: 26px; overflow: hidden; box-shadow: 0 20px 40px -20px rgba(15, 23, 42, 0.25); page-break-after: always; }
        .bill:last-child { page-break-after: auto; }
        .header { background: #eef2ff; padding: 44px 56px; position: relative; }
        .header::before { content: ''; position: absolute; top: -140px; right: -120px; width: 320px; height: 320px; background: radial-gradient(circle at center, rgba(79, 70, 229, 0.18), rgba(79, 70, 229, 0)); transform: rotate(12deg); }
        .header-content { position: relative; display: grid; grid-template-columns: auto 1fr; gap: 32px; align-items: center; }
        .logo-badge { width: 110px; height: 110px; border-radius: 32px; background: linear-gradient(180deg, #ffffff 0%, #e2e8ff 100%); border: 1px solid rgba(79, 70, 229, 0.15); display: grid; place-items: center; box-shadow: 0 16px 32px -20px rgba(79, 70, 229, 0.5); }
        .logo-badge img { width: 78px; height: 78px; object-fit: contain; filter: drop-shadow(0 4px 10px rgba(79, 70, 229, 0.15)); }
        .company { color: var(--primary); }
        .company-name { font-size: 24px; font-weight: 700; letter-spacing: 0.05em; margin-bottom: 6px; }
        .company-address { font-size: 13px; color: rgba(15, 23, 42, 0.6); }
        .bill-meta { background: #ffffff; border-radius: 18px; padding: 20px 24px; box-shadow: 0 14px 35px -20px rgba(15, 23, 42, 0.35); border: 1px solid rgba(79, 70, 229, 0.12); font-size: 13px; }
        .bill-meta-row { display: flex; justify-content: space-between; gap: 36px; text-transform: uppercase; letter-spacing: 0.18em; color: rgba(15, 23, 42, 0.55); margin-bottom: 6px; }
        .bill-meta-value { font-size: 14px; font-weight: 700; color: #1d4ed8; letter-spacing: normal; text-transform: none; }
        .section { padding: 32px 56px; border-bottom: 1px solid rgba(15, 23, 42, 0.08); }
        .section:last-of-type { border-bottom: none; }
        .section-title { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.2em; color: var(--muted); margin-bottom: 14px; }
        .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 28px; }
        .section p { margin: 4px 0; font-size: 14px; color: var(--muted); }
        .section strong { color: var(--primary); }
        .reading-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 18px; }
        .reading-item { background: linear-gradient(180deg, rgba(248, 250, 252, 0.92), rgba(226, 232, 240, 0.6)); border: 1px solid rgba(148, 163, 184, 0.18); border-radius: 18px; padding: 18px; text-align: center; }
        .label { font-size: 12px; letter-spacing: 0.18em; text-transform: uppercase; color: var(--muted); }
        .value { font-size: 20px; font-weight: 600; color: var(--primary); margin-top: 8px; }
        .totals-wrap { display: grid; grid-template-columns: minmax(0, 1.6fr) minmax(0, 1fr); gap: 24px; align-items: start; }
        .totals-box { border: 1px solid rgba(148, 163, 184, 0.18); background: rgba(248, 250, 252, 0.6); border-radius: 18px; padding: 18px; }
        .totals-row { display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 10px; color: var(--muted); }
        .totals-row span:last-child { font-weight: 600; color: var(--primary); }
        .totals-row.total { border-top: 2px solid rgba(15, 23, 42, 0.15); padding-top: 12px; margin-top: 12px; font-size: 16px; }
        .highlight { border-radius: 20px; padding: 22px; background: linear-gradient(160deg, #1d4ed8, #3b82f6); color: white; display: flex; flex-direction: column; gap: 6px; }
        .highlight span.label { font-size: 11px; letter-spacing: 0.28em; text-transform: uppercase; color: rgba(226, 232, 240, 0.8); }
        .highlight span.value { font-size: 28px; font-weight: 600; }
        .usage-chart { margin-top: 20px; }
        .usage-chart h3 { font-size: 12px; letter-spacing: 0.18em; text-transform: uppercase; color: var(--muted); margin-bottom: 12px; }
        .bars { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; align-items: end; height: 120px; }
        .bar { background: linear-gradient(180deg, rgba(56, 189, 248, 0.85), rgba(14, 165, 233, 0.7)); position: relative; border-radius: 10px 10px 0 0; }
        .bar span { position: absolute; top: -22px; left: 50%; transform: translateX(-50%); font-size: 11px; font-weight: 600; color: var(--primary); }
        .bar .label { position: absolute; bottom: -18px; left: 50%; transform: translateX(-50%); font-size: 10px; color: var(--muted); white-space: nowrap; }
        .footer { margin-top: 24px; text-align: center; font-size: 11px; color: var(--muted); letter-spacing: 0.08em; padding-top: 18px; border-top: 1px solid rgba(15, 23, 42, 0.08); }
        @media print { body { margin: 0; background: white; } .bill { border-radius: 0; box-shadow: none; } }
    </style>
</head>
<body>
    @foreach($records as $billingRecord)
        <div class="bill">
            <div class="header">
                <div class="header-content">
                    <div class="logo-badge">
                        <img src="{{ asset('images/mawasa-logo.png') }}" alt="MAWASA Logo">
                    </div>
                    <div class="company">
                        <div class="company-name">MANAMBULAN WATERWORKS &amp; SANITATION INC.</div>
                        <div class="company-address">Brgy. Manambulan, Tugbok District, Davao City</div>
                        <div class="company-address">E-mail: — | Phone: —</div>
                    </div>
                    <div class="bill-meta">
                        <div class="bill-meta-row"><span>Invoice</span><span class="bill-meta-value">{{ $billingRecord->invoice_number ?? 'INV-' . str_pad($billingRecord->id, 4, '0', STR_PAD_LEFT) }}</span></div>
                        <div class="bill-meta-row"><span>Prepared</span><span class="bill-meta-value">{{ $billingRecord->prepared_by ?? '—' }}</span></div>
                        <div class="bill-meta-row" style="margin-bottom:0;"><span>Issued</span><span class="bill-meta-value">{{ optional($billingRecord->issued_at ?? $billingRecord->created_at)->format('M d, Y') }}</span></div>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="grid">
                    <div>
                        <div class="section-title">Customer</div>
                        <p><strong>Name:</strong> {{ $billingRecord->customer->name }}</p>
                        <p><strong>Account No:</strong> {{ $billingRecord->account_no }}</p>
                        <p><strong>Address:</strong> {{ $billingRecord->customer->address }}</p>
                    </div>
                    <div>
                        <div class="section-title">Billing</div>
                        <p><strong>Billing Period:</strong> {{ $billingRecord->getBillingPeriod() }}</p>
                        <p><strong>Due Date:</strong> {{ $billingRecord->date_to ? $billingRecord->date_to->format('M d, Y') : 'N/A' }}</p>
                        <p><strong>Status:</strong> {{ $billingRecord->bill_status }}</p>
                    </div>
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

            <div class="section">
                <div class="section-title">Readings</div>
                <div class="reading-grid">
                    <div class="reading-item">
                        <div class="label">Previous Reading</div>
                        <div class="value">{{ number_format($billingRecord->previous_reading, 2) }}</div>
                    </div>
                    <div class="reading-item">
                        <div class="label">Current Reading</div>
                        <div class="value">{{ number_format($billingRecord->current_reading, 2) }}</div>
                    </div>
                    <div class="reading-item" style="background: linear-gradient(180deg, rgba(56, 189, 248, 0.15), rgba(14, 165, 233, 0.08));">
                        <div class="label">Consumption</div>
                        <div class="value" style="color:#0369a1;">{{ number_format($billingRecord->consumption_cu_m, 2) }} m³</div>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Charges</div>
                <div class="totals-wrap">
                    <div class="totals-box">
                        <div class="totals-row"><span>Water Consumption ({{ number_format($billingRecord->consumption_cu_m, 2) }} m³ × ₱{{ number_format($billingRecord->base_rate, 2) }})</span><span>₱{{ number_format($consumptionCost, 2) }}</span></div>
                        <div class="totals-row"><span>Maintenance Charge</span><span>₱{{ number_format($billingRecord->maintenance_charge, 2) }}</span></div>
                        @if($billingRecord->overdue_penalty > 0)
                        <div class="totals-row" style="color:#dc2626;"><span>Overdue Penalty</span><span>₱{{ number_format($billingRecord->overdue_penalty, 2) }}</span></div>
                        @endif
                        @if($billingRecord->advance_payment > 0)
                        <div class="totals-row" style="color:#16a34a;"><span>Advance Payment</span><span>-₱{{ number_format($billingRecord->advance_payment, 2) }}</span></div>
                        @endif
                        <div class="totals-row" style="font-size:12px; color: rgba(15,23,42,0.55); margin-top:6px;">Rate: ₱{{ number_format($billingRecord->base_rate, 2) }}/m³ • Maintenance: ₱{{ number_format($billingRecord->maintenance_charge, 2) }}</div>
                    </div>
                    <div class="highlight">
                        <span class="label">Total Amount Due</span>
                        <span class="value">₱{{ number_format($total, 2) }}</span>
                        <span style="font-size:12px; letter-spacing:0.1em; text-transform:uppercase; color: rgba(226,232,240,0.8);">Status: {{ $billingRecord->bill_status }}</span>
                    </div>
                </div>
            </div>

            @php $series = $usageByAccount[$billingRecord->account_no] ?? []; $maxVal = !empty($series) ? max(array_map(fn($i)=>$i['value'],$series)) : 0; $maxVal = $maxVal > 0 ? $maxVal : 1; @endphp
            @if(!empty($series))
            <div class="section">
                <div class="section-title">Usage (Last 5 Months)</div>
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
            <div class="section">
                <div class="section-title">Notes</div>
                <div style="border-radius:16px; border:1px solid rgba(251,191,36,0.45); background: rgba(254,249,195,0.6); padding:16px; font-size:13px; color:#a16207;">
                    {{ $billingRecord->notes }}
                </div>
            </div>
            @endif

            <div class="footer">
                <p>Thank you for choosing MAWASA. Payment is due within ____ days.</p>
                <p>Printed on: {{ now()->format('M d, Y g:i A') }}</p>
            </div>
        </div>
    @endforeach

    <script>
        window.onload = function() { window.print(); };
    </script>
</body>
</html>
