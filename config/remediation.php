<?php

return [
    'notify_emails' => array_filter(array_map('trim', explode(',', env('REMEDIATION_NOTIFY_EMAILS', '')))),

    'notify_sms_numbers' => array_filter(array_map('trim', explode(',', env('REMEDIATION_NOTIFY_SMS', '')))),
];
