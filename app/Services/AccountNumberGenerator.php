<?php

namespace App\Services;

use App\Models\Customer;

class AccountNumberGenerator
{
    public function next(string $areaCode = null, string $routeCode = null): string
    {
        $area = $areaCode ?? str_pad((string) env('ACCOUNT_AREA_CODE', '12'), 2, '0', STR_PAD_LEFT);
        $route = $routeCode ?? str_pad((string) env('ACCOUNT_ROUTE_CODE', '01'), 2, '0', STR_PAD_LEFT);
        $prefix = $area . $route; // 4-digit prefix

        $max = Customer::query()
            ->where('account_no', 'like', $prefix . '%')
            ->max('account_no');

        $lastSeq = 0;
        if (is_string($max) && strlen($max) >= 10) {
            $suffix = substr($max, -6);
            if (ctype_digit($suffix)) {
                $lastSeq = (int) $suffix;
            }
        }

        $nextSeq = $lastSeq + 1;
        $suffix = str_pad((string) $nextSeq, 6, '0', STR_PAD_LEFT);

        return $prefix . $suffix; // e.g., AARR + 6-digit sequence => 10 digits
    }
}


