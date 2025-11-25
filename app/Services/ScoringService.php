<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerApplication;

class ScoringService
{
    public function score(CustomerApplication $app): array
    {
        $score = 0;
        $breakdown = [];
        $docs = $app->documents ?? [];

        // ID validity and image quality (30)
        $idPoints = 0;
        $hasFront = !empty($docs['id_front'] ?? null);
        $hasBack = !empty($docs['id_back'] ?? null);
        $hasSelfie = !empty($docs['selfie'] ?? null);
        $idPoints += $hasFront ? 10 : 0;
        $idPoints += $hasBack ? 10 : 0;
        $idPoints += $hasSelfie ? 10 : 0;
        $breakdown['id_validity'] = $idPoints;
        $score += $idPoints; // 0-30

        // Duplicate risk (25): subtract if duplicates detected
        $dupPoints = 25;
        $name = trim((string)($app->applicant_name ?? ''));
        $address = trim((string)($app->address ?? ''));
        $idno = trim((string)($docs['id_number'] ?? ''));

        // exact name+address match in customers
        $custDup = 0;
        if ($name && $address) {
            $custDup = Customer::query()
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                ->whereRaw('LOWER(address) = ?', [mb_strtolower($address)])
                ->exists() ? 1 : 0;
        }
        // id number match in applications
        $appDup = 0;
        if ($idno) {
            $appDup = CustomerApplication::query()
                ->where('documents->id_number', $idno)
                ->where('id', '!=', $app->id)
                ->exists() ? 1 : 0;
        }
        if ($custDup) { $dupPoints -= 15; }
        if ($appDup) { $dupPoints -= 10; }
        $dupPoints = max(0, $dupPoints);
        $breakdown['duplicate_risk'] = $dupPoints; // 0-25
        $score += $dupPoints;

        // Data completeness (20)
        $compPoints = 0;
        if ($name) $compPoints += 7;
        if ($address) $compPoints += 7;
        if ($app->contact_no) $compPoints += 6;
        $breakdown['data_completeness'] = $compPoints; // 0-20
        $score += $compPoints;

        // Address quality (15)
        $addrPoints = 0;
        // simple heuristic: address has 2 commas (barangay, city, province)
        if (substr_count($address, ',') >= 2) $addrPoints += 15;
        $breakdown['address_quality'] = $addrPoints; // 0-15
        $score += $addrPoints;

        // Manual risk flags (10) - if present, subtract
        $flags = $app->risk_flags ?? [];
        $flagPenalty = is_array($flags) ? min(10, count($flags) * 5) : 0; // 0,5,10
        $manualPoints = max(0, 10 - $flagPenalty);
        $breakdown['manual_risk'] = $manualPoints; // 0-10
        $score += $manualPoints;

        // Inspection boost (up to +5)
        $inspectPoints = 0;
        if (!empty($app->inspection_date)) {
            $inspectPoints = 5;
        }
        $breakdown['inspection'] = $inspectPoints; // 0-5
        $score += $inspectPoints;

        $score = max(0, min(100, (int) $score));
        $risk = $score >= 80 ? 'low' : ($score >= 60 ? 'medium' : 'high');

        return [
            'score' => $score,
            'breakdown' => $breakdown,
            'risk_level' => $risk,
        ];
    }
}
