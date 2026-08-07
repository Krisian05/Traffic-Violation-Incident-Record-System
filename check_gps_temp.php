<?php

use Illuminate\Support\Facades\DB;

// Update existing TVIRS-GIS seeded violations with corrected GPS coordinates
$corrections = [
    'Balamban Public Market, Transcentral Highway' => ['lat' => 10.5043, 'lng' => 123.7136],
    'Nivel Hills, Transcentral Highway, Balamban'  => ['lat' => 10.3390, 'lng' => 123.8860],
    'Nivel Hills, Transcentral Highway, Cebu City' => ['lat' => 10.3390, 'lng' => 123.8860],
    'Subangdaku Flyover, M.C. Briones St, Mandaue City' => ['lat' => 10.3228, 'lng' => 123.9244],
    'UN Avenue & M.C. Briones Junction, Mandaue City'   => ['lat' => 10.3312, 'lng' => 123.9352],
    'SRP Coastal Road, South Road Properties, Cebu City' => ['lat' => 10.2757, 'lng' => 123.8776],
    'Fuente Osmeña Circle, Cebu City'               => ['lat' => 10.3093, 'lng' => 123.8924],
    'Danao City Port Highway, Danao City'            => ['lat' => 10.5205, 'lng' => 124.0301],
    'Carcar Rotunda Highway, Carcar City'            => ['lat' => 10.0988, 'lng' => 123.6440],
    'Tabunok Flyover, CSCR Expressway, Talisay City' => ['lat' => 10.2636, 'lng' => 123.8398],
    'Tabunok Flyover, Natalio Bacalso Ave, Talisay City' => ['lat' => 10.2636, 'lng' => 123.8398],
    'Toledo City Port Highway, Toledo City'          => ['lat' => 10.3833, 'lng' => 123.6333],
];

$violationCount = 0;
$incidentCount = 0;

foreach ($corrections as $location => $coords) {
    $vc = DB::table('violations')
        ->where('location', $location)
        ->whereNotNull('gps_lat')
        ->update(['gps_lat' => $coords['lat'], 'gps_lng' => $coords['lng']]);
    $violationCount += $vc;

    $ic = DB::table('incidents')
        ->where('location', $location)
        ->whereNotNull('gps_lat')
        ->update(['gps_lat' => $coords['lat'], 'gps_lng' => $coords['lng']]);
    $incidentCount += $ic;
}

echo "Updated {$violationCount} violation records\n";
echo "Updated {$incidentCount} incident records\n";
echo "GPS corrections applied successfully.\n";
