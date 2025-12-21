<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Statement • {{ optional($billingRecord->customer)->name ?? 'Customer' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/print-billing.css') }}">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #e9f0fb;
            padding: 24px;
        }

        .print-billing {
            margin: 24px auto;
        }

        .totals .note {
            font-size: 12px;
            color: var(--print-muted);
            line-height: 1.6;
        }

        .contact strong {
            font-weight: 600;
            color: var(--print-primary);
        }

        .policy-callout {
            margin-top: 18px;
            padding: 16px 18px;
            border-radius: 12px;
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(22, 163, 74, 0.18);
            color: #065f46;
        }

        .policy-callout h4 {
            margin: 0 0 8px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .policy-callout p {
            margin: 0;
            font-size: 12px;
            line-height: 1.6;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .print-billing {
                margin: 0 auto;
                box-shadow: none;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
@php
    $customer = optional($billingRecord->customer);
    $invoiceNumber = $billingRecord->invoice_number ?? 'INV-' . str_pad($billingRecord->id, 4, '0', STR_PAD_LEFT);
    $issuedDate = optional($billingRecord->issued_at ?? $billingRecord->created_at)->format('M d, Y');
    $dueDate = $billingRecord->due_date ? $billingRecord->due_date->format('M d, Y') : '—';
    $billingPeriod = $billingRecord->getBillingPeriod();

    $consumptionCost = ($billingRecord->consumption_cu_m ?? 0) * ($billingRecord->base_rate ?? 0);
    $maintenanceCharge = $billingRecord->maintenance_charge ?? 0;
    $overduePenalty = $billingRecord->overdue_penalty ?? 0;
    $advancePayment = $billingRecord->advance_payment ?? 0;
    $subtotal = $consumptionCost + $maintenanceCharge;
    $totalDue = $billingRecord->total_amount ?? ($subtotal + $overduePenalty - $advancePayment);
    $overdueTotal = max(0, $overduePenalty);

    $previousReading = $billingRecord->previous_reading ?? 0;
    $currentReading = $billingRecord->current_reading ?? 0;
    $consumptionVolume = $billingRecord->consumption_cu_m ?? max(0, $currentReading - $previousReading);
    $baseRate = $billingRecord->base_rate ?? 0;
    $dueNote = $billingRecord->due_date ? 'Due ' . $dueDate : 'Due upon receipt';

    $usageSeries = $usageSeries ?? [];
    $graphCount = count($usageSeries);
    $graphWidth = 220;
    $graphHeight = 140;
    $paddingX = 20;
    $paddingY = 20;
    $usableWidth = $graphWidth - ($paddingX * 2);
    $usableHeight = $graphHeight - ($paddingY * 2);
    $maxUsage = $graphCount ? max(array_map(fn($row) => $row['value'] ?? 0, $usageSeries)) : 0;
    $maxUsage = $maxUsage > 0 ? $maxUsage : 1;
    $step = $graphCount > 1 ? $usableWidth / ($graphCount - 1) : 0;
    $polylinePoints = [];
    $pointMeta = [];

    foreach ($usageSeries as $index => $point) {
        $x = $paddingX + ($graphCount > 1 ? $step * $index : $usableWidth / 2);
        $value = $point['value'] ?? 0;
        $y = $paddingY + $usableHeight - ($value / $maxUsage) * $usableHeight;
        $polylinePoints[] = $x . ',' . $y;
        $pointMeta[] = [
            'x' => $x,
            'y' => $y,
            'value' => $value,
            'label' => $point['label'] ?? ''
        ];
    }

    $polylineString = implode(' ', $polylinePoints);
    $areaPolygonString = null;
    if ($graphCount > 1) {
        $first = explode(',', $polylinePoints[0]);
        $last = explode(',', $polylinePoints[count($polylinePoints) - 1]);
        $baselineY = $paddingY + $usableHeight;
        $areaPolygonString = $first[0] . ',' . $baselineY . ' ' . $polylineString . ' ' . $last[0] . ',' . $baselineY;
    }
@endphp

<section class="print-billing">
    <header class="invoice-header">
        <div class="brand">
            <div class="brand-logo">
                <img src="{{ asset('images/mawasa-logo.png') }}" alt="MAWASA logo">
            </div>
            <div class="brand-copy">
                <h1>MAWASA</h1>
                <p>Manambulan Waterworks &amp; Sanitation Inc.</p>
                <span class="brand-site">service@mawasa.ph</span>
            </div>
        </div>
        <div class="invoice-meta">
            <span class="label">Invoice No.</span>
            <span class="value">{{ $invoiceNumber }}</span>
            <span class="label">Issued</span>
            <span class="value">{{ $issuedDate }}</span>
        </div>
    </header>

    <div class="accent-bar"></div>

    <section class="invoice-info">
        <div class="info-block">
            <h2>Bill To</h2>
            <p class="name">{{ $customer->name ?? '—' }}</p>
            <p>{{ $customer->address ?? 'Address unavailable' }}</p>
            <p>{{ $billingRecord->account_no }}</p>
        </div>
        <div class="info-block">
            <h2>Billing Details</h2>
            <dl>
                <div><dt>Status</dt><dd>{{ $billingRecord->bill_status }}</dd></div>
                <div><dt>Billing Period</dt><dd>{{ $billingPeriod }}</dd></div>
                <div><dt>Due Date</dt><dd>{{ $dueDate }}</dd></div>
                <div><dt>Previous Reading</dt><dd>{{ number_format($previousReading, 0) }} m³</dd></div>
                <div><dt>Current Reading</dt><dd>{{ number_format($currentReading, 0) }} m³</dd></div>
                <div><dt>Prepared By</dt><dd>{{ $billingRecord->prepared_by ?? '—' }}</dd></div>
                <div><dt>Meter</dt><dd>{{ $customer->meter_no ?? '—' }} · {{ $customer->meter_size ?? '—' }}</dd></div>
            </dl>
        </div>
    </section>

    <div class="billing-content">
        <div class="billing-column">
            <table class="charge-table">
                <thead>
                    <tr>
                        <th class="text-left">Description</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Water Consumption</td>
                        <td class="text-right">₱{{ number_format($consumptionCost, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Maintenance Charge</td>
                        <td class="text-right">₱{{ number_format($maintenanceCharge, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Overdue Penalty</td>
                        <td class="text-right">₱{{ number_format($overduePenalty, 2) }}</td>
                    </tr>
                    @if($advancePayment > 0)
                    <tr>
                        <td>Advance Payment (Credit)</td>
                        <td class="text-right">-₱{{ number_format($advancePayment, 2) }}</td>
                    </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <td class="text-right label">Subtotal</td>
                        <td class="text-right">₱{{ number_format($subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-right label">Total Amount Due</td>
                        <td class="text-right total">₱{{ number_format($totalDue, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-right label">Overdue Total</td>
                        <td class="text-right overdue">₱{{ number_format($overdueTotal, 2) }}</td>
                    </tr>
                </tfoot>
            </table>

            <section class="signature">
                <div class="sign-line"></div>
                <p class="name">{{ $billingRecord->prepared_by ?? 'MAWASA Billing Administrator' }}</p>
                <p class="role">Authorized Signatory</p>
            </section>

            @if(!empty($usageSeries))
            <section class="usage-section">
                <h4>Usage (Last 5 Months)</h4>
                <div class="usage-graph">
                    <div class="usage-bars">
                        @foreach($pointMeta as $meta)
                            @php
                                $barHeight = $maxUsage > 0 ? max(4, round(($meta['value'] / $maxUsage) * 100)) : 4;
                            @endphp
                            <div class="bar">
                                <span class="bar-value">{{ number_format($meta['value'], 1) }}</span>
                                <div class="bar-track">
                                    <div class="bar-fill" style="height: {{ $barHeight }}%;"></div>
                                </div>
                                <span class="bar-label">{{ $meta['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
            @endif
        </div>
    </div>

    <footer class="footer-bar">
        <span>Thank you for choosing MAWASA</span>
        <span>See reverse for payment options &amp; terms</span>
    </footer>
</section>

<section class="print-back-section">
    <header class="back-header">
        <div>
            <h2>Company Policy</h2>
            <p>Please read and keep this page for your reference.</p>
        </div>
        <div class="back-hours">
            <span class="label">Important</span>
            <span class="value">Pay on or before the due date.</span>
            <span class="note">If you can't make to pay before due date, your water source will be cut.</span>
        </div>
    </header>

    <section class="back-terms">
        <h3>Reminders</h3>
        <p>Always present your account number when paying. Keep your receipts for verification and reconnection requests.</p>
        <p>For billing concerns, immediately coordinate with MAWASA office staff.</p>
        <div class="policy-callout">
            <h4>Disconnection Policy</h4>
            <p>Accounts that are not paid on or before the due date may be scheduled for service disconnection. Disconnection may be implemented after the due date if payment is not received.</p>
        </div>
    </section>

    <footer class="back-footer">
        <span>MANAMBULAN WATERWORKS AND SANITATION INC. (MAWASA)</span>
    </footer>
</section>

<script>
    @if(empty($disableAutoPrint))
        window.addEventListener('load', function () {
            window.print();
        });
    @endif
</script>
</body>
</html>
