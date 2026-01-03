<?php
require 'vendor/autoload.php';

$val = "";
echo "is_numeric(''): " . (is_numeric($val) ? 'true' : 'false') . "\n";
echo "val is: '" . $val . "'\n";

try {
    // Replicating the exact call from stack trace
    \Carbon\Carbon::createFromDate('2026', $val, 1);
    echo "Carbon created successfully\n";
} catch (\Throwable $e) {
    echo "Carbon error: " . $e->getMessage() . "\n";
}
