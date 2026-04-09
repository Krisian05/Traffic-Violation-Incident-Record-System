<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\IncidentChargeType;
use App\Models\IncidentMedia;
use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use App\Models\Violation;
use App\Models\ViolationVehiclePhoto;
use App\Models\Violator;
use App\Models\ViolationType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OfficerController extends Controller
{
    // ─────────────────────────────────────────────
    //  DASHBOARD
    // ─────────────────────────────────────────────

    public function dashboard(): View
    {
        $motoristCount      = Violator::count();
        $violationCount     = Violation::count();
        $incidentCount      = Incident::count();
        $openIncidentCount  = Incident::where('status', 'open')->count();
        $overdueCount       = Violation::overdue()->count();

        return view('officer.dashboard', compact('motoristCount', 'violationCount', 'incidentCount', 'openIncidentCount', 'overdueCount'));
    }

    // ─────────────────────────────────────────────
    //  MOTORISTS
    // ─────────────────────────────────────────────

    public function motorists(Request $request): View
    {
        $search = trim($request->input('search', ''));

        $query = Violator::withCount('violations');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name',  'like', "%{$search}%")
                  ->orWhere('middle_name','like', "%{$search}%")
                  ->orWhere('license_number', 'like', "%{$search}%");
            });
        }

        $violators = $query->orderBy('last_name')->paginate(15)->withQueryString();

        return view('officer.motorists.index', compact('violators', 'search'));
    }

    public function motoristSuggestions(Request $request)
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $search = trim($request->input('q', ''));

        if ($search === '') {
            return response()->json([]);
        }

        $motorists = Violator::withCount('violations')->select('violators.*')
            ->with(['vehicles' => fn($query) => $query->select('id', 'violator_id', 'plate_number')->limit(1)])
            ->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('license_number', 'like', "%{$search}%");
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit(7)
            ->get()
            ->map(function (Violator $violator) {
                $plate = $violator->vehicles->first()?->plate_number;
                $status = $violator->violations_count >= 3
                    ? 'Recidivist'
                    : ($violator->violations_count === 2 ? 'Repeat Offender' : null);

                return [
                    'id' => $violator->id,
                    'label' => $violator->last_name . ', ' . $violator->first_name,
                    'sub' => collect([
                        $violator->license_number ? 'License: ' . $violator->license_number : null,
                        $plate ? 'Plate: ' . $plate : null,
                    ])->filter()->implode(' · ') ?: 'No license or vehicle on file',
                    'initials' => strtoupper(substr((string) $violator->first_name, 0, 1) . substr((string) $violator->last_name, 0, 1)),
                    'violations_count' => $violator->violations_count,
                    'status' => $status,
                    'url' => route('officer.motorists.show', $violator),
                ];
            })
            ->values();

        return response()->json($motorists);
    }

    public function createMotorist(): View
    {
        return view('officer.motorists.create');
    }

    public function storeMotorist(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name'            => ['required', 'string', 'max:100'],
            'middle_name'           => ['nullable', 'string', 'max:100'],
            'last_name'             => ['required', 'string', 'max:100'],
            'date_of_birth'         => ['nullable', 'date', 'before:today'],
            'place_of_birth'        => ['nullable', 'string', 'max:255'],
            'gender'                => ['nullable', 'in:Male,Female,Other'],
            'civil_status'          => ['nullable', 'in:Single,Married,Widowed,Separated'],
            'blood_type'            => ['nullable', 'in:O+,O-,A+,A-,B+,B-,AB+,AB-'],
            'height'                => ['nullable', 'string', 'max:20'],
            'weight'                => ['nullable', 'string', 'max:20'],
            'valid_id'              => ['nullable', 'string', 'max:255'],
            'email'                 => ['nullable', 'email', 'max:255'],
            'contact_number'        => ['nullable', 'string', 'max:20'],
            'address'               => ['nullable', 'string', 'max:500'],
            'permanent_address'     => ['nullable', 'string', 'max:500'],
            'license_number'        => ['nullable', 'string', 'max:50', 'unique:violators,license_number'],
            'license_type'          => ['nullable', 'in:Non-Professional,Professional'],
            'license_issued_date'   => ['nullable', 'date'],
            'license_expiry_date'   => ['nullable', 'date'],
            'license_restriction'   => ['nullable', 'array'],
            'license_restriction.*' => ['in:A,A1,B,B1,B2,C,D,BE,CE'],
            'license_conditions'    => ['nullable', 'string', 'max:500'],
            'photo'                 => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $duplicate = Violator::whereRaw('LOWER(first_name) = LOWER(?) AND LOWER(last_name) = LOWER(?)', [
            $data['first_name'], $data['last_name'],
        ])->exists();

        if ($duplicate) {
            return back()->withInput()->with('error', 'This Name is Already Exist.');
        }

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('violator-photos', uploads_disk());
        }

        if (!empty($data['license_restriction']) && is_array($data['license_restriction'])) {
            $data['license_restriction'] = implode(',', $data['license_restriction']);
        }

        // Map officer 'address' field to the canonical temporary_address column
        if (!empty($data['address'])) {
            $data['temporary_address'] = $data['address'];
        }
        unset($data['address']);

        $violator = Violator::create($data);

        return redirect()->route('officer.motorists.show', $violator)
            ->with('success', 'Motorist ' . e($violator->first_name . ' ' . $violator->last_name) . ' added successfully.');
    }

    public function showMotorist(Violator $violator): View
    {
        $violator->load([
            'vehicles.photos',
            'violations.violationType',
            'violations.vehicle',
            'violations.recorder',
            'violations.incident',
            'incidentMotorists.incident',
            'incidentMotorists.vehicle',
        ]);

        $incidents = Incident::with(['motorists.chargeType'])
            ->whereHas('motorists', fn($q) => $q->where('violator_id', $violator->id))
            ->orderByDesc('date_of_incident')
            ->orderByDesc('created_at')
            ->get();

        return view('officer.motorists.show', compact('violator', 'incidents'));
    }

    public function editMotorist(Violator $violator): View
    {
        return view('officer.motorists.edit', compact('violator'));
    }

    public function updateMotorist(Request $request, Violator $violator): RedirectResponse
    {
        $data = $request->validate([
            'first_name'            => ['required', 'string', 'max:100'],
            'middle_name'           => ['nullable', 'string', 'max:100'],
            'last_name'             => ['required', 'string', 'max:100'],
            'date_of_birth'         => ['nullable', 'date', 'before:today'],
            'place_of_birth'        => ['nullable', 'string', 'max:255'],
            'gender'                => ['nullable', 'in:Male,Female,Other'],
            'civil_status'          => ['nullable', 'in:Single,Married,Widowed,Separated'],
            'blood_type'            => ['nullable', 'in:O+,O-,A+,A-,B+,B-,AB+,AB-'],
            'height'                => ['nullable', 'string', 'max:20'],
            'weight'                => ['nullable', 'string', 'max:20'],
            'valid_id'              => ['nullable', 'string', 'max:255'],
            'email'                 => ['nullable', 'email', 'max:255'],
            'contact_number'        => ['nullable', 'string', 'max:20'],
            'address'               => ['nullable', 'string', 'max:500'],
            'permanent_address'     => ['nullable', 'string', 'max:500'],
            'license_number'        => ['nullable', 'string', 'max:50', 'unique:violators,license_number,' . $violator->id],
            'license_type'          => ['nullable', 'in:Non-Professional,Professional'],
            'license_issued_date'   => ['nullable', 'date'],
            'license_expiry_date'   => ['nullable', 'date'],
            'license_restriction'   => ['nullable', 'array'],
            'license_restriction.*' => ['in:A,A1,B,B1,B2,C,D,BE,CE'],
            'license_conditions'    => ['nullable', 'string', 'max:500'],
            'photo'                 => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        if ($request->hasFile('photo')) {
            if ($violator->photo) {
                Storage::disk(uploads_disk())->delete($violator->photo);
            }
            $data['photo'] = $request->file('photo')->store('violator-photos', uploads_disk());
        }

        if (!empty($data['license_restriction']) && is_array($data['license_restriction'])) {
            $data['license_restriction'] = implode(',', $data['license_restriction']);
        } elseif (array_key_exists('license_restriction', $data) && empty($data['license_restriction'])) {
            $data['license_restriction'] = null;
        }

        if (!empty($data['address'])) {
            $data['temporary_address'] = $data['address'];
        }
        unset($data['address']);

        $violator->update($data);

        return redirect()->route('officer.motorists.show', $violator)
            ->with('success', 'Motorist updated successfully.');
    }

    // ─────────────────────────────────────────────
    //  VEHICLES
    // ─────────────────────────────────────────────

    public function createVehicle(Violator $violator): View
    {
        return view('officer.vehicles.create', compact('violator'));
    }

    public function storeVehicle(Request $request, Violator $violator): RedirectResponse
    {
        $data = $request->validate([
            'plate_number'   => ['required', 'string', 'max:20', 'unique:vehicles,plate_number'],
            'vehicle_type'   => ['required', 'in:MV,MC'],
            'make'           => ['nullable', 'string', 'max:100'],
            'model'          => ['nullable', 'string', 'max:100'],
            'color'          => ['nullable', 'string', 'max:50'],
            'year'           => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'or_number'      => ['nullable', 'string', 'max:50'],
            'cr_number'      => ['nullable', 'string', 'max:50'],
            'chassis_number' => ['nullable', 'string', 'max:50'],
            'owner_name'     => ['nullable', 'string', 'max:200'],
            'photos'         => ['nullable', 'array', 'max:4'],
            'photos.*'       => ['image', 'max:20480'],
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

        return redirect()->route('officer.motorists.show', $violator)
            ->with('success', 'Vehicle added successfully.');
    }

    public function createOfflineVehicle(): View
    {
        return view('officer.vehicles.offline-create');
    }

    public function storeOfflineVehicle(Request $request): RedirectResponse
    {
        $request->validate([
            'offline_motorist_key' => ['required', 'string'],
            'plate_number'         => ['required', 'string', 'max:20', 'unique:vehicles,plate_number'],
            'vehicle_type'         => ['required', 'in:MV,MC'],
            'make'                 => ['nullable', 'string', 'max:100'],
            'model'                => ['nullable', 'string', 'max:100'],
            'color'                => ['nullable', 'string', 'max:50'],
            'year'                 => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'or_number'            => ['nullable', 'string', 'max:50'],
            'cr_number'            => ['nullable', 'string', 'max:50'],
            'chassis_number'       => ['nullable', 'string', 'max:50'],
            'owner_name'           => ['nullable', 'string', 'max:200'],
            'photos'               => ['nullable', 'array', 'max:4'],
            'photos.*'             => ['image', 'max:20480'],
        ]);

        // TODO: Resolve offline_motorist_key to violator_id during sync (extend mobile-offline.js)
        // For now require violator_id from synced form
        $violatorId = $request->input('violator_id');
        $violator = Violator::findOrFail($violatorId);

        $data = $request->only([
            'plate_number', 'vehicle_type', 'make', 'model', 'color', 'year',
            'or_number', 'cr_number', 'chassis_number', 'owner_name'
        ]);
        $data['violator_id'] = $violator->id;

        $vehicle = Vehicle::create($data);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $path = $file->store('vehicle-photos', uploads_disk());
                VehiclePhoto::create(['vehicle_id' => $vehicle->id, 'photo' => $path]);
            }
        }

        return redirect()->route('officer.motorists.show', $violator)
            ->with('success', 'Vehicle added successfully.');
    }

    // ─────────────────────────────────────────────
    //  VIOLATIONS
    // ─────────────────────────────────────────────

    public function createViolation(Violator $violator): View
    {
        $violationTypes = Cache::remember('violation_types', 600, fn() => ViolationType::orderBy('name')->get());
        $allVehicles = Vehicle::with('violator:id,first_name,middle_name,last_name')
            ->orderBy('plate_number')
            ->get(['id', 'violator_id', 'plate_number', 'make', 'model', 'color', 'vehicle_type', 'owner_name']);
        $relatedIncidents = Incident::whereHas('motorists', fn($q) => $q->where('violator_id', $violator->id))
            ->orderByDesc('date_of_incident')
            ->orderByDesc('created_at')
            ->get(['id', 'incident_number', 'date_of_incident', 'location']);

        return view('officer.violations.create', compact('violator', 'violationTypes', 'allVehicles', 'relatedIncidents'));
    }

    public function createOfflineViolation(): View
    {
        $violationTypes = Cache::remember('violation_types', 600, fn() => ViolationType::orderBy('name')->get());

        return view('officer.violations.offline-create', compact('violationTypes'));
    }

    public function storeViolation(Request $request, Violator $violator): RedirectResponse
    {
        $data = $request->validate([
            'violation_type_id'     => ['required', 'exists:violation_types,id'],
            'incident_id'           => ['nullable', 'exists:incidents,id'],
            'vehicle_id'            => ['nullable', 'exists:vehicles,id'],
            'vehicle_plate'         => ['nullable', 'string', 'max:30'],
            'vehicle_type'          => ['nullable', 'in:MV,MC'],
            'vehicle_owner_name'    => ['nullable', 'string', 'max:200'],
            'vehicle_make'          => ['nullable', 'string', 'max:100'],
            'vehicle_model'         => ['nullable', 'string', 'max:100'],
            'vehicle_color'         => ['nullable', 'string', 'max:50'],
            'vehicle_or_number'     => ['nullable', 'string', 'max:50'],
            'vehicle_cr_number'     => ['nullable', 'string', 'max:50'],
            'vehicle_chassis'       => ['nullable', 'string', 'max:100'],
            'photos'                => ['nullable', 'array', 'max:4'],
            'photos.*'              => ['image', 'mimes:jpg,jpeg,png', 'max:20480'],
            'date_of_violation'     => ['required', 'date', 'before_or_equal:today'],
            'location'              => ['nullable', 'string', 'max:255'],
            'ticket_number'         => ['nullable', 'string', 'max:50'],
            'citation_ticket_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:20480'],
            'status'                => ['required', 'in:pending,settled'],
            'notes'                 => ['nullable', 'string', 'max:1000'],
        ]);

        if (!empty($data['vehicle_id'])) {
            $selectedVehicle = Vehicle::with('violator:id,first_name,middle_name,last_name')->find($data['vehicle_id']);
            if (empty($data['vehicle_owner_name']) && $selectedVehicle) {
                $data['vehicle_owner_name'] = $selectedVehicle->owner_name ?: $selectedVehicle->violator?->full_name;
            }

            foreach (['vehicle_plate', 'vehicle_make', 'vehicle_model', 'vehicle_color', 'vehicle_or_number', 'vehicle_cr_number', 'vehicle_chassis'] as $f) {
                unset($data[$f]);
            }
        } elseif (!empty($data['vehicle_plate'])) {
            // Auto-register manual vehicle so it appears on the violator's profile
            $vehicle = Vehicle::where('violator_id', $violator->id)
                ->where('plate_number', $data['vehicle_plate'])->first();
            if (!$vehicle) {
                $vehicle = Vehicle::create([
                    'violator_id'    => $violator->id,
                    'plate_number'   => $data['vehicle_plate'],
                    'vehicle_type'   => $data['vehicle_type'] ?? null,
                    'owner_name'     => !empty($data['vehicle_owner_name']) ? $data['vehicle_owner_name'] : $violator->full_name,
                    'make'           => $data['vehicle_make'] ?? null,
                    'model'          => $data['vehicle_model'] ?? null,
                    'color'          => $data['vehicle_color'] ?? null,
                    'or_number'      => $data['vehicle_or_number'] ?? null,
                    'cr_number'      => $data['vehicle_cr_number'] ?? null,
                    'chassis_number' => $data['vehicle_chassis'] ?? null,
                ]);
            } else {
                $vehicle->update([
                    'vehicle_type'   => $vehicle->vehicle_type ?: ($data['vehicle_type'] ?? null),
                    'owner_name'     => $vehicle->owner_name ?: (!empty($data['vehicle_owner_name']) ? $data['vehicle_owner_name'] : $violator->full_name),
                    'make'           => $vehicle->make ?: ($data['vehicle_make'] ?? null),
                    'model'          => $vehicle->model ?: ($data['vehicle_model'] ?? null),
                    'color'          => $vehicle->color ?: ($data['vehicle_color'] ?? null),
                    'or_number'      => $vehicle->or_number ?: ($data['vehicle_or_number'] ?? null),
                    'cr_number'      => $vehicle->cr_number ?: ($data['vehicle_cr_number'] ?? null),
                    'chassis_number' => $vehicle->chassis_number ?: ($data['vehicle_chassis'] ?? null),
                ]);
            }

            if (empty($data['vehicle_owner_name'])) {
                $data['vehicle_owner_name'] = $vehicle->owner_name ?: $violator->full_name;
            }
            $data['vehicle_id'] = $vehicle->id;
        }

        if ($request->hasFile('citation_ticket_photo')) {
            $data['citation_ticket_photo'] = $request->file('citation_ticket_photo')
                ->store('citation-photos', uploads_disk());
        }

        unset($data['photos']);
        $data['violator_id'] = $violator->id;
        $data['recorded_by'] = Auth::id();

        $violation = Violation::create($data);

        if ($request->hasFile('photos')) {
            foreach (array_slice($request->file('photos'), 0, 4) as $file) {
                $path = $file->store('violation-vehicle-photos', uploads_disk());
                ViolationVehiclePhoto::create(['violation_id' => $violation->id, 'photo' => $path]);
            }
        }

        return redirect()->route('officer.motorists.show', $violator)
            ->with('success', 'Violation recorded successfully.');
    }

    public function showViolation(Violation $violation): View
    {
        $violation->load(['violationType', 'vehicle.violator', 'vehiclePhotos', 'recorder', 'violator', 'incident']);

        return view('officer.violations.show', compact('violation'));
    }

    public function editViolation(Violation $violation): View
    {
        $this->authorize('update', $violation);
        $violation->load(['violator', 'vehiclePhotos']);
        $violationTypes = Cache::remember('violation_types', 600, fn() => ViolationType::orderBy('name')->get());
        $allVehicles = Vehicle::with('violator:id,first_name,middle_name,last_name')
            ->orderBy('plate_number')
            ->get(['id', 'violator_id', 'plate_number', 'make', 'model', 'color', 'vehicle_type', 'owner_name']);
        $relatedIncidents = Incident::whereHas('motorists', fn($q) => $q->where('violator_id', $violation->violator_id))
            ->orderByDesc('date_of_incident')
            ->orderByDesc('created_at')
            ->get(['id', 'incident_number', 'date_of_incident', 'location']);

        return view('officer.violations.edit', compact('violation', 'violationTypes', 'allVehicles', 'relatedIncidents'));
    }

    public function updateViolation(Request $request, Violation $violation): RedirectResponse
    {
        $this->authorize('update', $violation);

        $data = $request->validate([
            'violation_type_id'     => ['required', 'exists:violation_types,id'],
            'incident_id'           => ['nullable', 'exists:incidents,id'],
            'vehicle_id'            => ['nullable', 'exists:vehicles,id'],
            'vehicle_plate'         => ['nullable', 'string', 'max:30'],
            'vehicle_type'          => ['nullable', 'in:MV,MC'],
            'vehicle_owner_name'    => ['nullable', 'string', 'max:200'],
            'vehicle_make'          => ['nullable', 'string', 'max:100'],
            'vehicle_model'         => ['nullable', 'string', 'max:100'],
            'vehicle_color'         => ['nullable', 'string', 'max:50'],
            'vehicle_or_number'     => ['nullable', 'string', 'max:50'],
            'vehicle_cr_number'     => ['nullable', 'string', 'max:50'],
            'vehicle_chassis'       => ['nullable', 'string', 'max:100'],
            'photos'                => ['nullable', 'array'],
            'photos.*'              => ['image', 'mimes:jpg,jpeg,png', 'max:51200'],
            'date_of_violation'     => ['required', 'date', 'before_or_equal:today'],
            'location'              => ['nullable', 'string', 'max:255'],
            'ticket_number'         => ['nullable', 'string', 'max:50'],
            'citation_ticket_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:20480'],
            'status'                => ['required', 'in:pending,settled'],
            'notes'                 => ['nullable', 'string', 'max:1000'],
            'or_number'             => ['nullable', 'string', 'max:50'],
            'cashier_name'          => ['nullable', 'string', 'max:150'],
            'receipt_photo'         => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:20480'],
        ]);

        if ($data['status'] === 'settled' && !$violation->settled_at) {
            $data['settled_at'] = now();
        }

        if ($data['status'] !== 'settled') {
            $data['settled_at'] = null;
            $data['or_number'] = null;
            $data['cashier_name'] = null;
            if ($violation->receipt_photo) {
                Storage::disk(uploads_disk())->delete($violation->receipt_photo);
            }
            $data['receipt_photo'] = null;
        }

        unset($data['photos']);

        if ($request->boolean('remove_citation_photo') && !$request->hasFile('citation_ticket_photo')) {
            if ($violation->citation_ticket_photo) {
                Storage::disk(uploads_disk())->delete($violation->citation_ticket_photo);
            }
            $data['citation_ticket_photo'] = null;
        } elseif ($request->hasFile('citation_ticket_photo')) {
            if ($violation->citation_ticket_photo) {
                Storage::disk(uploads_disk())->delete($violation->citation_ticket_photo);
            }
            $data['citation_ticket_photo'] = $request->file('citation_ticket_photo')->store('citation-photos', uploads_disk());
        } else {
            unset($data['citation_ticket_photo']);
        }

        if ($data['status'] === 'settled') {
            if ($request->boolean('remove_receipt_photo') && !$request->hasFile('receipt_photo')) {
                if ($violation->receipt_photo) {
                    Storage::disk(uploads_disk())->delete($violation->receipt_photo);
                }
                $data['receipt_photo'] = null;
            } elseif ($request->hasFile('receipt_photo')) {
                if ($violation->receipt_photo) {
                    Storage::disk(uploads_disk())->delete($violation->receipt_photo);
                }
                $data['receipt_photo'] = $request->file('receipt_photo')->store('receipt-photos', uploads_disk());
            } else {
                unset($data['receipt_photo']);
            }
        }

        if (!empty($data['vehicle_id'])) {
            $selectedVehicle = Vehicle::with('violator:id,first_name,middle_name,last_name')->find($data['vehicle_id']);
            if (empty($data['vehicle_owner_name']) && $selectedVehicle) {
                $data['vehicle_owner_name'] = $selectedVehicle->owner_name ?: $selectedVehicle->violator?->full_name;
            }

            foreach ($violation->vehiclePhotos as $photo) {
                Storage::disk(uploads_disk())->delete($photo->photo);
            }
            $violation->vehiclePhotos()->delete();

            foreach (['vehicle_plate', 'vehicle_make', 'vehicle_model', 'vehicle_color', 'vehicle_or_number', 'vehicle_cr_number', 'vehicle_chassis'] as $field) {
                $data[$field] = null;
            }
        } elseif (!empty($data['vehicle_plate'])) {
                $existing = Vehicle::where('violator_id', $violation->violator_id)
                ->where('plate_number', $data['vehicle_plate'])
                ->first();

            if (!$existing) {
                $existing = Vehicle::create([
                    'violator_id'    => $violation->violator_id,
                    'plate_number'   => $data['vehicle_plate'],
                    'vehicle_type'   => $data['vehicle_type'] ?? null,
                    'owner_name'     => !empty($data['vehicle_owner_name']) ? $data['vehicle_owner_name'] : $violation->violator?->full_name ?? 'Unknown',
                    'make'           => $data['vehicle_make'] ?? null,
                    'model'          => $data['vehicle_model'] ?? null,
                    'color'          => $data['vehicle_color'] ?? null,
                    'or_number'      => $data['vehicle_or_number'] ?? null,
                    'cr_number'      => $data['vehicle_cr_number'] ?? null,
                    'chassis_number' => $data['vehicle_chassis'] ?? null,
                ]);
            } else {
                $existing->fill([
                    'vehicle_type'   => $existing->vehicle_type ?: ($data['vehicle_type'] ?? null),
                    'owner_name'     => $existing->owner_name ?: (!empty($data['vehicle_owner_name']) ? $data['vehicle_owner_name'] : ($violation->violator?->full_name ?? 'Unknown')),
                    'make'           => $existing->make ?: ($data['vehicle_make'] ?? null),
                    'model'          => $existing->model ?: ($data['vehicle_model'] ?? null),
                    'color'          => $existing->color ?: ($data['vehicle_color'] ?? null),
                    'or_number'      => $existing->or_number ?: ($data['vehicle_or_number'] ?? null),
                    'cr_number'      => $existing->cr_number ?: ($data['vehicle_cr_number'] ?? null),
                    'chassis_number' => $existing->chassis_number ?: ($data['vehicle_chassis'] ?? null),
                ])->save();
            }

            if (empty($data['vehicle_owner_name'])) {
                $data['vehicle_owner_name'] = $existing->owner_name ?: $violation->violator?->full_name;
            }
            $data['vehicle_id'] = $existing->id;
        }

        $violation->update($data);

        if ($request->hasFile('photos') && empty($request->input('vehicle_id'))) {
            $currentCount = $violation->vehiclePhotos()->count();
            $allowed = 4 - $currentCount;

            if ($allowed <= 0) {
                return back()->withErrors(['photos' => 'Maximum 4 photos already reached. Delete an existing photo first.']);
            }

            $newFiles = $request->file('photos');
            foreach (array_slice($newFiles, 0, $allowed) as $file) {
                $path = $file->store('violation-vehicle-photos', uploads_disk());
                ViolationVehiclePhoto::create(['violation_id' => $violation->id, 'photo' => $path]);
            }

            if (count($newFiles) > $allowed) {
                return redirect()->route('officer.violations.show', $violation)
                    ->with('success', 'Violation updated. Only ' . $allowed . ' photo(s) were saved (4-photo limit reached).');
            }
        }

        return redirect()->route('officer.violations.show', $violation)
            ->with('success', 'Violation updated successfully.');
    }

    // ─────────────────────────────────────────────
    //  INCIDENTS
    // ─────────────────────────────────────────────

    public function incidents(Request $request): View
    {
        $search = trim($request->input('search', ''));
        $status = $request->input('status', '');

        $query = Incident::with('motorists')->withCount('motorists');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('location', 'like', "%{$search}%")
                  ->orWhere('incident_number', 'like', "%{$search}%")
                  ->orWhereHas('motorists', fn($mq) =>
                      $mq->where('motorist_name', 'like', "%{$search}%")
                  );
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $incidents = $query->orderByDesc('date_of_incident')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('officer.incidents.index', compact('incidents', 'search', 'status'));
    }

    public function createIncident(): View
    {
        $chargeTypes     = Cache::remember('incident_charge_types', 600, fn() => IncidentChargeType::orderBy('name')->get());
        $violators       = Violator::orderBy('last_name')->orderBy('first_name')
                              ->get([
                                  'id',
                                  'first_name',
                                  'middle_name',
                                  'last_name',
                                  'license_number',
                                  'license_type',
                                  'license_restriction',
                                  'license_expiry_date',
                                  'contact_number',
                                  'temporary_address',
                                  'permanent_address',
                              ]);
        $vehiclesByOwner = Vehicle::orderBy('plate_number')
            ->get(['id', 'violator_id', 'plate_number', 'vehicle_type', 'make', 'model', 'color', 'owner_name', 'or_number', 'cr_number', 'chassis_number'])
            ->groupBy('violator_id');

        return view('officer.incidents.create', compact('chargeTypes', 'violators', 'vehiclesByOwner'));
    }

    public function storeIncident(Request $request): RedirectResponse
    {
        $request->validate([
            'date_of_incident'                    => 'required|date|before_or_equal:today',
            'time_of_incident'                    => 'nullable|date_format:H:i',
            'location'                            => 'required|string|max:255',
            'description'                         => 'nullable|string|max:2000',
            'incident_photos'                     => 'nullable|array|max:6',
            'incident_photos.*'                   => 'image|mimes:jpg,jpeg,png|max:20480',
            'motorists'                           => 'required|array|min:2|max:10',
            'motorists.*.violator_id'             => 'nullable|exists:violators,id',
            'motorists.*.motorist_name'           => 'nullable|string|max:200',
            'motorists.*.motorist_license'        => 'nullable|string|max:100',
            'motorist_photos'                     => 'nullable|array',
            'motorist_photos.*'                   => 'nullable|array|max:4',
            'motorist_photos.*.*'                 => 'nullable|file|mimes:jpg,jpeg,png|max:20480',
            'motorist_id_photos'                  => 'nullable|array',
            'motorist_id_photos.*'                => 'nullable|file|mimes:jpg,jpeg,png|max:20480',
            'motorists.*.license_type'            => 'nullable|string|max:50',
            'motorists.*.license_restriction'     => 'nullable|array',
            'motorists.*.license_restriction.*'   => 'nullable|string|max:10',
            'motorists.*.license_expiry_date'     => 'nullable|date',
            'motorists.*.motorist_contact'        => 'nullable|string|max:30',
            'motorists.*.motorist_address'        => 'nullable|string|max:300',
            'motorists.*.vehicle_id'              => 'nullable|exists:vehicles,id',
            'motorists.*.vehicle_plate'           => 'nullable|string|max:30',
            'motorists.*.vehicle_type_manual'     => 'nullable|string|max:10',
            'motorists.*.vehicle_make'            => 'nullable|string|max:100',
            'motorists.*.vehicle_model'           => 'nullable|string|max:100',
            'motorists.*.vehicle_color'           => 'nullable|string|max:50',
            'motorists.*.vehicle_or_number'       => 'nullable|string|max:50',
            'motorists.*.vehicle_cr_number'       => 'nullable|string|max:50',
            'motorists.*.vehicle_chassis'         => 'nullable|string|max:100',
            'motorists.*.incident_charge_type_id' => 'nullable|exists:incident_charge_types,id',
            'motorists.*.notes'                   => 'nullable|string|max:500',
            'media'                               => 'nullable|array|max:20',
            'media.*'                             => 'file|mimes:jpg,jpeg,png,pdf|max:20480',
            'media_types'                         => 'nullable|array',
            'media_types.*'                       => 'in:scene,ticket,document,other',
            'captions'                            => 'nullable|array',
            'captions.*'                          => 'nullable|string|max:200',
        ]);

        foreach ($request->input('motorists', []) as $i => $m) {
            if (empty($m['violator_id']) && empty($m['motorist_name'])) {
                return back()
                    ->withErrors(["motorists.{$i}.motorist_name" => 'Each motorist must have a name or be linked to a registered motorist.'])
                    ->withInput();
            }
        }

        $incident = DB::transaction(function () use ($request) {
            $incident = Incident::create([
                'date_of_incident' => $request->input('date_of_incident'),
                'time_of_incident' => $request->input('time_of_incident'),
                'location'         => $request->input('location'),
                'description'      => $request->input('description'),
                'status'           => 'open',
                'recorded_by'      => Auth::id(),
            ]);

            $vehiclePhotos = $request->file('motorist_photos') ? [$request->file('motorist_photos')] : [];
            $motoristIdPhotos = $request->file('motorist_id_photos', []);

            if ($request->hasFile('incident_photos')) {
                foreach (array_slice($request->file('incident_photos'), 0, 6) as $file) {
                    $path = $file->store('incident-photos', uploads_disk());
                    IncidentMedia::create([
                        'incident_id' => $incident->id,
                        'file_path'   => $path,
                        'media_type'  => 'scene',
                    ]);
                }
            }

            foreach ($request->input('motorists', []) as $i => $m) {
                $m = $this->normalizeOfficerIncidentMotorist($m);

                $vehiclePathArr = [];
                if (!empty($vehiclePhotos[$i]) && is_array($vehiclePhotos[$i])) {
                    foreach (array_slice($vehiclePhotos[$i], 0, 4) as $photo) {
                        if ($photo && $photo->isValid()) {
                            $vehiclePathArr[] = $photo->store('motorist-photos', uploads_disk());
                        }
                    }
                }

                $idPhotoPath = !empty($motoristIdPhotos[$i])
                    ? $motoristIdPhotos[$i]->store('motorist-id-photos', uploads_disk())
                    : null;

                // Auto-register manual motorist into Violators table
                if (empty($m['violator_id']) && !empty($m['motorist_name'])) {
                    [$m['violator_id'], $m['vehicle_id']] = $this->autoRegisterOfficerMotorist($m);
                }

                $incident->motorists()->create([
                    'violator_id'             => $m['violator_id'] ?? null,
                    'motorist_name'           => $m['motorist_name'] ?? null,
                    'motorist_license'        => $m['motorist_license'] ?? null,
                    'motorist_photo'          => $idPhotoPath,
                    'motorist_contact'        => $m['motorist_contact'] ?? null,
                    'motorist_address'        => $m['motorist_address'] ?? null,
                    'license_type'            => $m['license_type'] ?? null,
                    'license_restriction'     => !empty($m['license_restriction']) ? implode(',', (array) $m['license_restriction']) : null,
                    'license_expiry_date'     => $m['license_expiry_date'] ?? null,
                    'vehicle_id'              => !empty($m['vehicle_id']) ? $m['vehicle_id'] : null,
                    'vehicle_plate'           => $m['vehicle_plate'] ?? null,
                    'vehicle_type_manual'     => $m['vehicle_type_manual'] ?? null,
                    'vehicle_make'            => $m['vehicle_make'] ?? null,
                    'vehicle_model'           => $m['vehicle_model'] ?? null,
                    'vehicle_color'           => $m['vehicle_color'] ?? null,
                    'vehicle_or_number'       => $m['vehicle_or_number'] ?? null,
                    'vehicle_cr_number'       => $m['vehicle_cr_number'] ?? null,
                    'vehicle_chassis'         => $m['vehicle_chassis'] ?? null,
                    'vehicle_photo'           => !empty($vehiclePathArr) ? $vehiclePathArr : null,
                    'incident_charge_type_id' => $m['incident_charge_type_id'] ?? null,
                    'notes'                   => $m['notes'] ?? null,
                ]);
            }

            if ($request->hasFile('media')) {
                $mediaTypes = $request->input('media_types', []);
                $captions = $request->input('captions', []);

                foreach ($request->file('media') as $i => $file) {
                    $path = $file->store('incident-media', uploads_disk());
                    $incident->media()->create([
                        'file_path'  => $path,
                        'media_type' => $mediaTypes[$i] ?? 'scene',
                        'caption'    => $captions[$i] ?? null,
                    ]);
                }
            }

            return $incident;
        });

        return redirect()->route('officer.incidents.show', $incident)
            ->with('success', 'Incident ' . e($incident->incident_number) . ' recorded successfully.');
    }

    /**
     * Same auto-registration logic as IncidentController but self-contained for the officer portal.
     * Returns [violator_id, vehicle_id|null].
     */
    private function autoRegisterOfficerMotorist(array $m): array
    {
        $violator = null;
        if (!empty($m['motorist_license'])) {
            $violator = Violator::where('license_number', $m['motorist_license'])->first();
            if ($violator) {
                // Fill any missing profile fields
                $fill = [];
                if (empty($violator->license_type)        && !empty($m['license_type']))        $fill['license_type']        = $m['license_type'];
                if (empty($violator->license_restriction) && !empty($m['license_restriction'])) $fill['license_restriction']  = is_array($m['license_restriction']) ? implode(',', $m['license_restriction']) : $m['license_restriction'];
                if (!$violator->license_expiry_date       && !empty($m['license_expiry_date'])) $fill['license_expiry_date']  = $m['license_expiry_date'];
                if (empty($violator->contact_number)      && !empty($m['motorist_contact']))    $fill['contact_number']       = $m['motorist_contact'];
                if (empty($violator->temporary_address)   && !empty($m['motorist_address']))    $fill['temporary_address']    = $m['motorist_address'];
                if (!empty($fill)) {
                    $violator->fill($fill)->save();
                }
            }
        }

        if (!$violator) {
            $parts     = preg_split('/\s+/', trim($m['motorist_name'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
            $lastName  = count($parts) > 1 ? array_pop($parts) : ($parts[0] ?? '');
            $firstName = implode(' ', $parts);
            $violator  = Violator::create([
                'first_name'          => $firstName,
                'last_name'           => $lastName,
                'license_number'      => $m['motorist_license'] ?? null,
                'license_type'        => $m['license_type'] ?? null,
                'license_restriction' => !empty($m['license_restriction']) ? implode(',', (array) $m['license_restriction']) : null,
                'license_expiry_date' => $m['license_expiry_date'] ?? null,
                'contact_number'      => $m['motorist_contact'] ?? null,
                'temporary_address'   => $m['motorist_address'] ?? null,
            ]);
        }

        $vehicleId = null;
        if (!empty($m['vehicle_plate'])) {
            $vehicle = Vehicle::where('violator_id', $violator->id)
                ->where('plate_number', $m['vehicle_plate'])->first();
            if (!$vehicle) {
                $vehicle = Vehicle::create([
                    'violator_id'    => $violator->id,
                    'plate_number'   => $m['vehicle_plate'],
                    'owner_name'     => $violator->full_name,
                    'vehicle_type'   => $m['vehicle_type_manual'] ?? null,
                    'make'           => $m['vehicle_make'] ?? null,
                    'model'          => $m['vehicle_model'] ?? null,
                    'color'          => $m['vehicle_color'] ?? null,
                    'or_number'      => $m['vehicle_or_number'] ?? null,
                    'cr_number'      => $m['vehicle_cr_number'] ?? null,
                    'chassis_number' => $m['vehicle_chassis'] ?? null,
                ]);
            } else {
                $vehicle->fill([
                    'owner_name'     => $vehicle->owner_name ?: $violator->full_name,
                    'vehicle_type'   => $vehicle->vehicle_type ?: ($m['vehicle_type_manual'] ?? null),
                    'make'           => $vehicle->make ?: ($m['vehicle_make'] ?? null),
                    'model'          => $vehicle->model ?: ($m['vehicle_model'] ?? null),
                    'color'          => $vehicle->color ?: ($m['vehicle_color'] ?? null),
                    'or_number'      => $vehicle->or_number ?: ($m['vehicle_or_number'] ?? null),
                    'cr_number'      => $vehicle->cr_number ?: ($m['vehicle_cr_number'] ?? null),
                    'chassis_number' => $vehicle->chassis_number ?: ($m['vehicle_chassis'] ?? null),
                ])->save();
            }
            $vehicleId = $vehicle->id;
        }

        return [$violator->id, $vehicleId];
    }

    private function normalizeOfficerIncidentMotorist(array $motorist): array
    {
        if (!empty($motorist['violator_id'])) {
            $violator = Violator::find($motorist['violator_id']);
            if ($violator) {
                $motorist['motorist_name'] = !empty($motorist['motorist_name']) ? $motorist['motorist_name'] : $violator->full_name;
                $motorist['motorist_license'] = !empty($motorist['motorist_license']) ? $motorist['motorist_license'] : $violator->license_number;
                $motorist['license_type'] = !empty($motorist['license_type']) ? $motorist['license_type'] : $violator->license_type;
                $motorist['license_expiry_date'] = !empty($motorist['license_expiry_date']) ? $motorist['license_expiry_date'] : optional($violator->license_expiry_date)->format('Y-m-d');
                $motorist['motorist_contact'] = !empty($motorist['motorist_contact']) ? $motorist['motorist_contact'] : $violator->contact_number;
                $motorist['motorist_address'] = !empty($motorist['motorist_address']) ? $motorist['motorist_address'] : ($violator->temporary_address ?: $violator->permanent_address);

                if (empty($motorist['license_restriction']) && !empty($violator->license_restriction)) {
                    $motorist['license_restriction'] = array_values(array_filter(array_map('trim', explode(',', $violator->license_restriction))));
                }
            }
        }

        if (!empty($motorist['vehicle_id'])) {
            $vehicle = Vehicle::find($motorist['vehicle_id']);
            if ($vehicle) {
                $motorist['vehicle_plate'] = !empty($motorist['vehicle_plate']) ? $motorist['vehicle_plate'] : $vehicle->plate_number;
                $motorist['vehicle_type_manual'] = !empty($motorist['vehicle_type_manual']) ? $motorist['vehicle_type_manual'] : $vehicle->vehicle_type;
                $motorist['vehicle_make'] = !empty($motorist['vehicle_make']) ? $motorist['vehicle_make'] : $vehicle->make;
                $motorist['vehicle_model'] = !empty($motorist['vehicle_model']) ? $motorist['vehicle_model'] : $vehicle->model;
                $motorist['vehicle_color'] = !empty($motorist['vehicle_color']) ? $motorist['vehicle_color'] : $vehicle->color;
                $motorist['vehicle_or_number'] = !empty($motorist['vehicle_or_number']) ? $motorist['vehicle_or_number'] : $vehicle->or_number;
                $motorist['vehicle_cr_number'] = !empty($motorist['vehicle_cr_number']) ? $motorist['vehicle_cr_number'] : $vehicle->cr_number;
                $motorist['vehicle_chassis'] = !empty($motorist['vehicle_chassis']) ? $motorist['vehicle_chassis'] : $vehicle->chassis_number;
            }
        }

        return $motorist;
    }

    public function showIncident(Incident $incident): View
    {
        $incident->load(['motorists.violator', 'motorists.vehicle', 'motorists.chargeType', 'recorder', 'media']);

        return view('officer.incidents.show', compact('incident'));
    }

    public function editIncident(Incident $incident): View
    {
        $this->authorize('update', $incident);
        $incident->load(['motorists.vehicle', 'media']);
        $chargeTypes = Cache::remember('incident_charge_types', 600, fn() => IncidentChargeType::orderBy('name')->get());

        return view('officer.incidents.edit', compact('incident', 'chargeTypes'));
    }

    public function updateIncident(Request $request, Incident $incident): RedirectResponse
    {
        $this->authorize('update', $incident);

        $validated = $request->validate([
            'date_of_incident' => 'required|date|before_or_equal:today',
            'time_of_incident' => 'nullable|date_format:H:i',
            'location'         => 'required|string|max:255',
            'description'      => 'nullable|string|max:2000',
            'incident_photos'  => 'nullable|array|max:6',
            'incident_photos.*' => 'image|mimes:jpg,jpeg,png|max:20480',
        ]);

        $incident->update([
            'date_of_incident' => $validated['date_of_incident'],
            'time_of_incident' => $validated['time_of_incident'] ?? null,
            'location'         => $validated['location'],
            'description'      => $validated['description'] ?? null,
        ]);

        if ($request->hasFile('incident_photos')) {
            foreach (array_slice($request->file('incident_photos'), 0, 6) as $file) {
                $path = $file->store('incident-photos', uploads_disk());
                IncidentMedia::create([
                    'incident_id' => $incident->id,
                    'file_path'   => $path,
                    'media_type'  => 'scene',
                ]);
            }
        }

        return redirect()->route('officer.incidents.show', $incident)
            ->with('success', 'Incident updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password'  => ['required'],
            'password'          => ['required', 'min:8', 'confirmed'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password updated successfully.');
    }
}
