<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Notification;
use App\Models\TransferReconnectAudit;
use App\Events\AccountReconnected;
use Illuminate\Support\Facades\Log;

class ReconnectService
{
    /**
     * Reconnect the service for a given account.
     * Updates the customer status to Active and returns the customer.
     *
     * @param string $accountNo
     * @param string|null $notes
     * @return Customer|null
     */
    public function reconnect(string $accountNo, ?string $notes = null)
    {
        $customer = Customer::where('account_no', $accountNo)->first();

        if (!$customer) {
            return null;
        }

        $customer->status = 'Active';
        $customer->reconnect_requested_at = null;
        $customer->reconnect_requested_by = null;
        $customer->save();

        try {
            // Audit record
            TransferReconnectAudit::create([
                'account_no' => $accountNo,
                'action' => 'reconnect',
                'old_value' => null,
                'new_value' => 'Active',
                'notes' => $notes,
                'performed_by' => auth()->id() ?? null,
                'performed_at' => now(),
            ]);

            if (class_exists(Notification::class)) {
                Notification::create([
                    'title' => 'Service Reconnected',
                    'message' => "Service reconnected for account {$accountNo}. " . ($notes ? $notes : ''),
                ]);
            }

            event(new AccountReconnected($accountNo, auth()->id() ?? null));
        } catch (\Exception $e) {
            Log::warning('ReconnectService: failed to create notification/audit - ' . $e->getMessage());
        }

        return $customer;
    }
}
