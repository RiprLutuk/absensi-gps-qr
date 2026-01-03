<?php
// Verification logic mimicking the fix in ScheduleComponent.php

function testMonth($val) {
    echo "Testing value: " . var_export($val, true) . "\n";
    $month = (filter_var($val, FILTER_VALIDATE_INT) !== false && $val >= 1 && $val <= 12) ? $val : date('m');
    echo "Result month: '$month'\n";
    
    try {
        \Carbon\Carbon::createFromDate(2026, $month, 1);
        echo "Carbon creation: SUCCESS\n";
    } catch (\Throwable $e) {
        echo "Carbon creation: FAILED (" . $e->getMessage() . ")\n";
    }
    echo "-------------------\n";
}

require 'vendor/autoload.php';

testMonth("");
testMonth("0");
testMonth("13");
testMonth("5");
testMonth(null);
