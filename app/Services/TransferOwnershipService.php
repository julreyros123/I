<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Register;
use App\Models\Notification;
use App\Models\TransferReconnectAudit;
use App\Events\AccountTransferred;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TransferOwnershipService
{
    /**
     * Transfer ownership of an account to a new name.
     * Returns the updated customer model on success.
     *
     * @param string $accountNo
     * @param string $newName
     * @param string|null $notes
     * @return Customer|null
     */
    public function transfer(string $accountNo, string $newName, ?string $notes = null)
    {
        $customer = Customer::where('account_no', $accountNo)->first();

        if (!$customer) {
            return null;
        }

        // Update both Customer and Register records if present
        $oldName = $customer->name;
        $customer->name = $newName;
        $customer->save();

        try {
            $reg = Register::where('account_no', $accountNo)->first();
            if ($reg) {
                $reg->name = $newName;
                $reg->save();
            }

            // Audit record
            TransferReconnectAudit::create([
                'account_no' => $accountNo,
                'action' => 'transfer',
                'old_value' => $oldName,
                'new_value' => $newName,
                'notes' => $notes,
                'performed_by' => auth()->id() ?? null,
                'performed_at' => now(),
            ]);

            // Optionally create a notification record for audit/notifications
            if (class_exists(Notification::class)) {
                Notification::create([
                    'title' => 'Ownership Transferred',
                    'message' => "Account {$accountNo} ownership changed from {$oldName} to {$newName}. " . ($notes ? $notes : ''),
                ]);
            }

            // Dispatch broadcast event
            event(new AccountTransferred($accountNo, $oldName, $newName, auth()->id() ?? null));
        } catch (\Exception $e) {
            // log but don't break the transfer
            Log::warning('TransferOwnershipService: failed to update register/notify/audit - ' . $e->getMessage());
        }

        return $customer;
    }
}
