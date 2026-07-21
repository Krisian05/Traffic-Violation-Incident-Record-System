<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\Vehicle;
use App\Models\Violation;
use App\Models\Violator;
use App\Models\ViolationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OfficerApiController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'motoristCount'     => Violator::count(),
            'violationCount'    => Violation::count(),
            'incidentCount'     => Incident::count(),
            'openIncidentCount' => Incident::where('status', 'under_investigation')->count(),
            'overdueCount'      => Violation::overdue()->count(),
        ]);
    }

    public function motorists(Request $request)
    {
        $search = trim($request->input('search', ''));
        $query = Violator::withCount('violations');

        if ($search !== '') {
            $lk = '%' . mb_strtolower($search) . '%';
            $query->where(function ($q) use ($lk) {
                $q->whereRaw('LOWER(first_name) LIKE ?', [$lk])
                  ->orWhereRaw('LOWER(last_name) LIKE ?', [$lk])
                  ->orWhereRaw('LOWER(middle_name) LIKE ?', [$lk])
                  ->orWhereRaw('LOWER(license_number) LIKE ?', [$lk]);
            });
        }

        $violators = $query->orderBy('last_name')->paginate(15);
        return response()->json($violators);
    }

    public function storeMotorist(Request $request)
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
            return response()->json(['message' => 'This Name already exists.'], 422);
        }

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('violator-photos', uploads_disk());
        }

        if (!empty($data['license_restriction']) && is_array($data['license_restriction'])) {
            $data['license_restriction'] = implode(',', $data['license_restriction']);
        }

        if (!empty($data['address'])) {
            $data['temporary_address'] = $data['address'];
        }
        unset($data['address']);

        // Bind current LGU
        $data['lgu_id'] = Auth::user()->lgu_id;

        $violator = Violator::create($data);

        return response()->json([
            'message' => 'Motorist registered successfully.',
            'violator' => $violator
        ], 201);
    }

    public function storeVehicle(Request $request)
    {
        $request->validate([
            'violator_id'    => ['required', 'exists:violators,id'],
            'plate_number'   => ['required', 'string', 'max:20', Rule::unique('vehicles', 'plate_number')->whereNull('deleted_at')],
            'vehicle_type'   => ['required', 'in:MV,MC'],
            'make'           => ['nullable', 'string', 'max:100'],
            'model'          => ['nullable', 'string', 'max:100'],
            'color'          => ['nullable', 'string', 'max:50'],
            'year'           => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'or_number'      => ['nullable', 'string', 'max:50'],
            'cr_number'      => ['nullable', 'string', 'max:50'],
            'chassis_number' => ['nullable', 'string', 'max:50'],
            'owner_name'     => ['nullable', 'string', 'max:200'],
        ]);

        $data = $request->only([
            'violator_id', 'plate_number', 'vehicle_type', 'make', 'model', 'color', 
            'year', 'or_number', 'cr_number', 'chassis_number', 'owner_name'
        ]);
        $data['lgu_id'] = Auth::user()->lgu_id;

        $vehicle = Vehicle::create($data);

        return response()->json([
            'message' => 'Vehicle registered successfully.',
            'vehicle' => $vehicle
        ], 201);
    }

    public function storeViolation(Request $request)
    {
        $data = $request->validate([
            'violator_id'           => ['required', 'exists:violators,id'],
            'violation_type_id'     => ['required', 'exists:violation_types,id'],
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
            'date_of_violation'     => ['required', 'date', 'before_or_equal:today'],
            'location'              => ['nullable', 'string', 'max:255'],
            'ticket_number'         => ['nullable', 'string', 'max:50', 'unique:violations,ticket_number'],
            'citation_ticket_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:20480'],
            'valid_id_photo'        => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:20480'],
            'status'                => ['required', 'in:pending,settled'],
            'notes'                 => ['nullable', 'string', 'max:1000'],
        ]);

        $violator = Violator::findOrFail($data['violator_id']);

        if (!empty($data['vehicle_id'])) {
            $selectedVehicle = Vehicle::find($data['vehicle_id']);
            if (empty($data['vehicle_owner_name']) && $selectedVehicle) {
                $data['vehicle_owner_name'] = $selectedVehicle->owner_name ?: $selectedVehicle->violator?->full_name;
            }
            foreach (['vehicle_plate', 'vehicle_make', 'vehicle_model', 'vehicle_color', 'vehicle_or_number', 'vehicle_cr_number', 'vehicle_chassis'] as $f) {
                unset($data[$f]);
            }
        } elseif (!empty($data['vehicle_plate'])) {
            $vehicle = Vehicle::withTrashed()->where('plate_number', $data['vehicle_plate'])->first();
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
                    'lgu_id'         => Auth::user()->lgu_id,
                ]);
            } else {
                if ($vehicle->trashed()) {
                    $vehicle->restore();
                }
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
            $data['citation_ticket_photo'] = $request->file('citation_ticket_photo')->store('citation-photos', uploads_disk());
        }

        if ($request->hasFile('valid_id_photo')) {
            $data['valid_id_photo'] = $request->file('valid_id_photo')->store('valid-id-photos', uploads_disk());
        }

        $data['recorded_by'] = Auth::id();
        $data['lgu_id']      = Auth::user()->lgu_id;

        $violation = Violation::create($data);

        return response()->json([
            'message' => 'Violation logged successfully.',
            'violation' => $violation
        ], 201);
    }

    public function storeIncident(Request $request)
    {
        $data = $request->validate([
            'date_of_incident' => ['required', 'date', 'before_or_equal:today'],
            'location'         => ['required', 'string', 'max:255'],
            'description'      => ['required', 'string'],
            'severity'         => ['required', 'in:minor,major,fatal'],
            'status'           => ['required', 'in:under_investigation,resolved,settled'],
        ]);

        $data['incident_number'] = 'INC-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        $data['recorded_by']     = Auth::id();
        $data['lgu_id']          = Auth::user()->lgu_id;

        $incident = Incident::create($data);

        return response()->json([
            'message' => 'Incident reported successfully.',
            'incident' => $incident
        ], 201);
    }
}
