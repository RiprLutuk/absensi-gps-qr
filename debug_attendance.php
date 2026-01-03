<?php

use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$attendance = Attendance::latest()->first();
echo "Latest Attendance:\n";
print_r($attendance->toArray());

echo "\nDashboard Query Test:\n";
$query = Attendance::where('date', now()->format('Y-m-d'))
    ->whereNotNull('time_in')
    ->whereNull('time_out')
    ->whereNotNull('latitude_in')
    ->whereNotNull('longitude_in');
    
echo "Count: " . $query->count() . "\n";
if($query->count() == 0) {
    echo "Query SQL: " . $query->toSql() . "\n";
    echo "Bindings: " . implode(', ', $query->getBindings()) . "\n";
}

echo "\nServer Date (date()): " . date('Y-m-d') . "\n";
echo "Carbon Date (now()): " . now()->format('Y-m-d') . "\n";
