<?php

return [
    // List of email addresses that will receive the monthly company delta report
    // Configure as comma-separated list in .env, e.g.: REPORT_RECIPIENTS="user1@example.com,user2@example.com"
    'recipients' => array_values(array_filter(array_map('trim', explode(',', env('REPORT_RECIPIENTS', ''))))),
];
