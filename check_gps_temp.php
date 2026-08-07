<?php

use Illuminate\Support\Facades\DB;

$rows = DB::table('violations')
    ->whereNotNull('gps_lat')
    ->whereNotNull('gps_lng')
    ->select('id', 'gps_lat', 'gps_lng', 'location')
    ->limit(20)
    ->get();

echo "=== VIOLATION GPS SAMPLES ===\n";
foreach ($rows as $r) {
    echo "ID:{$r->id} | lat:{$r->gps_lat} | lng:{$r->gps_lng} | loc:" . substr($r->location ?? '', 0, 40) . "\n";
}

$outOfRange = DB::table('violations')
    ->whereNotNull('gps_lat')
    ->whereNotNull('gps_lng')
    ->where(function($q){
        $q->where('gps_lat', '<', 9.0)
          ->orWhere('gps_lat', '>', 12.0)
          ->orWhere('gps_lng', '<', 122.0)
          ->orWhere('gps_lng', '>', 126.0);
    })
    ->count();

echo "\nOut-of-Cebu-range violations: {$outOfRange}\n";
echo "Total with GPS: " . DB::table('violations')->whereNotNull('gps_lat')->whereNotNull('gps_lng')->count() . "\n";

echo "\n=== INCIDENT GPS SAMPLES ===\n";
$irows = DB::table('incidents')
    ->whereNotNull('gps_lat')
    ->whereNotNull('gps_lng')
    ->select('id', 'gps_lat', 'gps_lng', 'location')
    ->limit(10)
    ->get();
foreach ($irows as $r) {
    echo "ID:{$r->id} | lat:{$r->gps_lat} | lng:{$r->gps_lng} | loc:" . substr($r->location ?? '', 0, 40) . "\n";
}
echo "Total incidents with GPS: " . DB::table('incidents')->whereNotNull('gps_lat')->whereNotNull('gps_lng')->count() . "\n";
