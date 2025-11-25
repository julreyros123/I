<?php

namespace App\Services;

use App\Models\Customer;

class AccountNumberGenerator
{
    public function next(string $areaCode = null, string $routeSuffix = null): string
    {
        // Desired format: AA-XXXXXX-R (e.g., 22-054540-1)
        $area = $areaCode ?? str_pad((string) env('ACCOUNT_AREA_CODE', '22'), 2, '0', STR_PAD_LEFT);
        $route = $routeSuffix ?? substr(str_pad((string) env('ACCOUNT_ROUTE_SUFFIX', '1'), 1, '0', STR_PAD_LEFT), 0, 1);

        // Find the max existing account for this area and route, regardless of separators
        $likePrefix = $area . '%-' . $route; // e.g., 22%-1
        $max = Customer::query()
            ->where('account_no', 'like', $likePrefix)
            ->max('account_no');

        $lastSeq = 0;
        if (is_string($max)) {
            // Expect stored as AA-XXXXXX-R
            $parts = explode('-', $max);
            if (count($parts) === 3 && ctype_digit($parts[1])) {
                $lastSeq = (int) $parts[1];
            } else {
                // Fallback: if stored without separators (legacy 10-digit), try to parse middle 6
                $digits = preg_replace('/\D+/', '', $max);
                if (strlen($digits) >= 9) {
                    $lastSeq = (int) substr($digits, 2, 6);
                }
            }
        }

        $nextSeq = $lastSeq + 1;
        $seq = str_pad((string) $nextSeq, 6, '0', STR_PAD_LEFT);

        return sprintf('%s-%s-%s', $area, $seq, $route);
    }
}


