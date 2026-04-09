<?php

namespace App\Http\Controllers;

use App\Models\IncidentMotorist;
use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use App\Models\Violator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::with(['violator', 'photos', 'firstViolationPhoto', 'allViolationPhotos'])
            ->withCount('violations');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $nameSearch = fn($vq) => $vq->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%");

                $q->where('plate_number', 'like', "%{$search}%")
                  ->orWhere('make', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('color', 'like', "%{$search}%")
                  // owner
                  ->orWhereHas('violator', $nameSearch)
                  // anyone who received a violation while using this vehicle
                  ->orWhereHas('violations.violator', $nameSearch)
                  // anyone listed as a motorist in an incident involving this vehicle
                  ->orWhereHas('incidentMotorists', fn($vq) => $vq
                      ->where('motorist_name', 'like', "%{$search}%")
                      ->orWhere('motorist_license', 'like', "%{$search}%")
                      ->orWhereHas('violator', $nameSearch)
                  );
            });
        }

        if ($type = $request->input('type')) {
            $query->where('vehicle_type', $type);
        }

        $vehicles = $query->orderBy('plate_number')->paginate(20)->withQueryString();

        return view('vehicles.index', compact('vehicles'));
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load([
            'violator.violations',
            'photos',
            'violations' => fn($q) => $q->with(['violationType', 'violator'])->orderByDesc('date_of_violation'),
        ]);

        $incidentMotorists = IncidentMotorist::with(['violator', 'incident'])
            ->where('vehicle_id', $vehicle->id)
            ->get();

        $vehicleUsers = collect();

        $rememberUser = function (string $key, array $payload) use (&$vehicleUsers): void {
            $base = [
                'key' => $key,
                'name' => '',
                'license_number' => null,
                'contact_number' => null,
                'address' => null,
                'photo' => null,
                'is_registered' => false,
                'is_owner' => false,
                'violator' => null,
                'violations_count' => 0,
                'incidents_count' => 0,
                'last_activity_at' => 0,
                'last_activity_label' => null,
            ];

            $current = $vehicleUsers->get($key, $base);

            foreach (['name', 'license_number', 'contact_number', 'address', 'photo', 'last_activity_label'] as $field) {
                if (empty($current[$field]) && !empty($payload[$field])) {
                    $current[$field] = $payload[$field];
                }
            }

            if (empty($current['violator']) && !empty($payload['violator'])) {
                $current['violator'] = $payload['violator'];
            }

            $current['is_registered'] = $current['is_registered'] || ($payload['is_registered'] ?? false);
            $current['is_owner'] = $current['is_owner'] || ($payload['is_owner'] ?? false);
            $current['violations_count'] += $payload['violations_count'] ?? 0;
            $current['incidents_count'] += $payload['incidents_count'] ?? 0;

            if (($payload['last_activity_at'] ?? 0) >= ($current['last_activity_at'] ?? 0)) {
                $current['last_activity_at'] = $payload['last_activity_at'] ?? $current['last_activity_at'];
                $current['last_activity_label'] = $payload['last_activity_label'] ?? $current['last_activity_label'];
            }

            $vehicleUsers->put($key, $current);
        };

        if ($vehicle->violator) {
            $rememberUser('violator:' . $vehicle->violator->id, [
                'name' => $vehicle->violator->full_name,
                'license_number' => $vehicle->violator->license_number,
                'contact_number' => $vehicle->violator->contact_number,
                'address' => $vehicle->violator->temporary_address ?: $vehicle->violator->permanent_address,
                'photo' => $vehicle->violator->photo,
                'is_registered' => true,
                'is_owner' => true,
                'violator' => $vehicle->violator,
            ]);
        }

        foreach ($vehicle->violations as $violation) {
            if (!$violation->violator) {
                continue;
            }

            $rememberUser('violator:' . $violation->violator->id, [
                'name' => $violation->violator->full_name,
                'license_number' => $violation->violator->license_number,
                'contact_number' => $violation->violator->contact_number,
                'address' => $violation->violator->temporary_address ?: $violation->violator->permanent_address,
                'photo' => $violation->violator->photo,
                'is_registered' => true,
                'violator' => $violation->violator,
                'violations_count' => 1,
                'last_activity_at' => $violation->date_of_violation?->timestamp ?? $violation->created_at?->timestamp ?? 0,
                'last_activity_label' => 'Violation on ' . ($violation->date_of_violation?->format('M d, Y') ?? 'record'),
            ]);
        }

        foreach ($incidentMotorists as $motorist) {
            $personKey = $motorist->violator
                ? 'violator:' . $motorist->violator->id
                : 'manual:' . md5(strtolower(trim(
                    ($motorist->motorist_name ?? '') . '|' .
                    ($motorist->motorist_license ?? '') . '|' .
                    ($motorist->motorist_contact ?? '')
                )));

            $rememberUser($personKey, [
                'name' => $motorist->violator?->full_name ?? ($motorist->motorist_name ?: 'Unknown motorist'),
                'license_number' => $motorist->violator?->license_number ?? $motorist->motorist_license,
                'contact_number' => $motorist->violator?->contact_number ?? $motorist->motorist_contact,
                'address' => $motorist->violator
                    ? ($motorist->violator->temporary_address ?: $motorist->violator->permanent_address)
                    : $motorist->motorist_address,
                'photo' => $motorist->violator?->photo ?? $motorist->motorist_photo,
                'is_registered' => (bool) $motorist->violator,
                'violator' => $motorist->violator,
                'incidents_count' => 1,
                'last_activity_at' => $motorist->incident?->date_of_incident?->timestamp ?? $motorist->created_at?->timestamp ?? 0,
                'last_activity_label' => 'Incident ' . ($motorist->incident->incident_number ?? 'record'),
            ]);
        }

        $vehicleUsers = $vehicleUsers
            ->sort(function (array $a, array $b): int {
                if ($a['is_owner'] !== $b['is_owner']) {
                    return $a['is_owner'] ? -1 : 1;
                }

                $aTotal = $a['violations_count'] + $a['incidents_count'];
                $bTotal = $b['violations_count'] + $b['incidents_count'];

                if ($aTotal !== $bTotal) {
                    return $bTotal <=> $aTotal;
                }

                if (($a['last_activity_at'] ?? 0) !== ($b['last_activity_at'] ?? 0)) {
                    return ($b['last_activity_at'] ?? 0) <=> ($a['last_activity_at'] ?? 0);
                }

                return strcasecmp($a['name'], $b['name']);
            })
            ->values();

        return view('vehicles.show', compact('vehicle', 'vehicleUsers'));
    }

    public function create(Violator $violator)
    {
        return view('vehicles.create', compact('violator'));
    }

    public function store(Request $request, Violator $violator)
    {
        $data = $request->validate([
            'plate_number' => ['required', 'string', 'max:20', 'unique:vehicles,plate_number'],
            'vehicle_type' => ['required', 'in:MV,MC'],
            'owner_name'   => ['nullable', 'string', 'max:255'],
            'make'         => ['nullable', 'string', 'max:100'],
            'model'        => ['nullable', 'string', 'max:100'],
            'color'        => ['nullable', 'string', 'max:50'],
            'year'         => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'or_number'      => ['nullable', 'string', 'max:50'],
            'cr_number'      => ['nullable', 'string', 'max:50'],
            'chassis_number' => ['nullable', 'string', 'max:50'],
            'photos'         => ['nullable', 'array', 'max:4'],
            'photos.*'     => ['image', 'max:20480'],
        ]);

        $data['violator_id'] = $violator->id;
        unset($data['photos']);

        $vehicle = Vehicle::create($data);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $path = $file->store('vehicle-photos', uploads_disk());
                VehiclePhoto::create(['vehicle_id' => $vehicle->id, 'photo' => $path]);
            }
        }

        return redirect()->route('violators.show', $violator)
            ->with('success', 'Vehicle added successfully.');
    }

    public function edit(Vehicle $vehicle)
    {
        $vehicle->load(['violator', 'photos']);
        return view('vehicles.edit', compact('vehicle'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'plate_number' => ['required', 'string', 'max:20', "unique:vehicles,plate_number,{$vehicle->id}"],
            'vehicle_type' => ['required', 'in:MV,MC'],
            'owner_name'   => ['nullable', 'string', 'max:255'],
            'make'         => ['nullable', 'string', 'max:100'],
            'model'        => ['nullable', 'string', 'max:100'],
            'color'        => ['nullable', 'string', 'max:50'],
            'year'         => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'or_number'      => ['nullable', 'string', 'max:50'],
            'cr_number'      => ['nullable', 'string', 'max:50'],
            'chassis_number' => ['nullable', 'string', 'max:50'],
            'photos'         => ['nullable', 'array'],
            'photos.*'       => ['image', 'max:10240'],
        ]);

        unset($data['photos']);
        $vehicle->update($data);

        if ($request->hasFile('photos')) {
            $currentCount = $vehicle->photos()->count();
            $newFiles     = $request->file('photos');
            $allowed      = 4 - $currentCount;

            if ($allowed <= 0) {
                return back()->withErrors(['photos' => 'Maximum 4 photos already reached. Delete an existing photo first.']);
            }

            foreach (array_slice($newFiles, 0, $allowed) as $file) {
                $path = $file->store('vehicle-photos', uploads_disk());
                VehiclePhoto::create(['vehicle_id' => $vehicle->id, 'photo' => $path]);
            }

            if (count($newFiles) > $allowed) {
                return redirect()->route('violators.show', $vehicle->violator_id)
                    ->with('success', 'Vehicle updated. Only ' . $allowed . ' photo(s) were saved (4-photo limit reached).');
            }
        }

        return redirect()->route('violators.show', $vehicle->violator_id)
            ->with('success', 'Vehicle updated successfully.');
    }

    public function destroy(Vehicle $vehicle)
    {
        $violatorId = $vehicle->violator_id;

        foreach ($vehicle->photos as $photo) {
            Storage::disk(uploads_disk())->delete($photo->photo);
        }
        $vehicle->delete();

        return redirect()->route('violators.show', $violatorId)
            ->with('success', 'Vehicle removed.');
    }
}
