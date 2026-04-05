<?php

namespace App\Http\Controllers;

use App\Models\IncidentMotorist;
use App\Models\Violator;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class ViolatorController extends Controller
{
    public function index(Request $request)
    {
        $query = Violator::withCount('violations')
            ->withCount(['violations as pending_count' => fn($q) => $q->where('status', 'pending')])
            ->withCount(['violations as overdue_count' => fn($q) => $q->where('status', 'pending')->where('date_of_violation', '<=', now()->subHours(72)->toDateString())]);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('license_number', 'like', "%{$search}%");
            });
        }

        if ($plate = $request->input('plate')) {
            $query->whereHas('vehicles', function ($q) use ($plate) {
                $q->where('plate_number', 'like', "%{$plate}%");
            });
        }

        $violators = $query->orderBy('last_name')->paginate(15)->withQueryString();
        $plate = $request->input('plate');

        return view('violators.index', compact('violators', 'search', 'plate'));
    }

    public function create()
    {
        return view('violators.create');
    }

    public function createFromIncident(IncidentMotorist $motorist): RedirectResponse
    {
        // Split full name: treat last word as last name, rest as first name (best effort)
        $parts     = preg_split('/\s+/', trim($motorist->motorist_name ?? ''), -1, PREG_SPLIT_NO_EMPTY);
        $lastName  = count($parts) > 1 ? array_pop($parts) : ($parts[0] ?? '');
        $firstName = implode(' ', $parts);

        // Restriction codes stored as comma-separated string → array for checkboxes
        $restriction = $motorist->license_restriction
            ? array_filter(array_map('trim', explode(',', $motorist->license_restriction)))
            : [];

        session()->flashInput([
            'first_name'          => $firstName,
            'last_name'           => $lastName,
            'license_number'      => $motorist->motorist_license,
            'license_type'        => $motorist->license_type,
            'license_restriction' => $restriction,
            'license_expiry_date' => $motorist->license_expiry_date
                ? \Carbon\Carbon::parse($motorist->license_expiry_date)->format('Y-m-d')
                : null,
            'contact_number'      => $motorist->motorist_contact,
            'temporary_address'   => $motorist->motorist_address,
            'incident_motorist_id' => $motorist->id,
        ]);

        return redirect()->route('violators.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name'          => ['required', 'string', 'max:100'],
            'middle_name'         => ['nullable', 'string', 'max:100'],
            'last_name'           => ['required', 'string', 'max:100'],
            'date_of_birth'       => ['nullable', 'date', 'before:today'],
            'place_of_birth'      => ['nullable', 'string', 'max:200'],
            'gender'              => ['nullable', 'in:Male,Female,Other'],
            'civil_status'        => ['nullable', 'in:Single,Married,Widowed,Separated'],
            'height'              => ['nullable', 'numeric', 'min:50', 'max:250'],
            'weight'              => ['nullable', 'numeric', 'min:10', 'max:300'],
            'blood_type'          => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'valid_id'            => ['nullable', 'string', 'max:100'],
            'email'               => ['nullable', 'email', 'max:150'],
            'contact_number'      => ['nullable', 'string', 'max:20'],
            'license_number'      => ['nullable', 'string', 'max:50', 'unique:violators,license_number'],
            'license_type'        => ['nullable', 'in:Non-Professional,Professional'],
            'license_restriction'   => ['nullable', 'array'],
            'license_restriction.*' => ['in:A,A1,B,B1,B2,C,D,BE,CE'],
            'license_issued_date' => ['nullable', 'date'],
            'license_expiry_date' => ['nullable', 'date'],
            'license_conditions'  => ['nullable', 'string', 'max:500'],
            'temporary_address'   => ['nullable', 'string', 'max:500'],
            'permanent_address'   => ['nullable', 'string', 'max:500'],
            'photo'               => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:20480'],
        ]);

        $duplicate = Violator::whereRaw('LOWER(first_name) = LOWER(?) AND LOWER(last_name) = LOWER(?)', [
            $data['first_name'], $data['last_name'],
        ])->exists();

        if ($duplicate) {
            return back()->withInput()->with('error', 'This Name is Already Exist.');
        }

        $data['license_restriction'] = !empty($data['license_restriction'])
            ? implode(',', $data['license_restriction'])
            : null;

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('violator-photos', uploads_disk());
        }

        $violator = Violator::create($data);

        // If registered from an incident, link the motorist record and go back to incident
        if ($request->filled('incident_motorist_id')) {
            $motorist = IncidentMotorist::find((int) $request->incident_motorist_id);
            if ($motorist && !$motorist->violator_id) {
                $motorist->update(['violator_id' => $violator->id]);
                return redirect()->route('incidents.show', $motorist->incident_id)
                    ->with('success', $violator->first_name . ' ' . $violator->last_name . ' has been registered and linked to this incident.');
            }
        }

        return redirect()->route('violators.show', $violator)
            ->with('success', 'Motorist profile created successfully.');
    }

    public function show(Violator $violator)
    {
        $violator->load([
            'vehicles.photos',
            'violations.violationType',
            'violations.vehicle',
            'violations.recorder',
            'incidentMotorists.incident',
        ]);

        $violationsByType = $violator->violations
            ->groupBy('violation_type_id')
            ->map(fn($group) => [
                'type'  => $group->first()->violationType,
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->values();

        return view('violators.show', compact('violator', 'violationsByType'));
    }

    public function printRecord(Violator $violator)
    {
        $violator->load([
            'vehicles.photos',
            'violations.violationType',
            'violations.vehicle',
            'violations.recorder',
            'incidentMotorists.incident',
            'incidentMotorists.chargeType',
        ]);

        $violationsByType = $violator->violations
            ->groupBy('violation_type_id')
            ->map(fn($group) => [
                'type'  => $group->first()->violationType,
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->values();

        return view('violators.print', compact('violator', 'violationsByType'));
    }

    public function edit(Violator $violator)
    {
        return view('violators.edit', compact('violator'));
    }

    public function update(Request $request, Violator $violator)
    {
        $data = $request->validate([
            'first_name'          => ['required', 'string', 'max:100'],
            'middle_name'         => ['nullable', 'string', 'max:100'],
            'last_name'           => ['required', 'string', 'max:100'],
            'date_of_birth'       => ['nullable', 'date', 'before:today'],
            'place_of_birth'      => ['nullable', 'string', 'max:200'],
            'gender'              => ['nullable', 'in:Male,Female,Other'],
            'civil_status'        => ['nullable', 'in:Single,Married,Widowed,Separated'],
            'height'              => ['nullable', 'numeric', 'min:50', 'max:250'],
            'weight'              => ['nullable', 'numeric', 'min:10', 'max:300'],
            'blood_type'          => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'valid_id'            => ['nullable', 'string', 'max:100'],
            'email'               => ['nullable', 'email', 'max:150'],
            'contact_number'      => ['nullable', 'string', 'max:20'],
            'license_number'      => ['nullable', 'string', 'max:50', "unique:violators,license_number,{$violator->id}"],
            'license_type'        => ['nullable', 'in:Non-Professional,Professional'],
            'license_restriction'   => ['nullable', 'array'],
            'license_restriction.*' => ['in:A,A1,B,B1,B2,C,D,BE,CE'],
            'license_issued_date' => ['nullable', 'date'],
            'license_expiry_date' => ['nullable', 'date'],
            'license_conditions'  => ['nullable', 'string', 'max:500'],
            'temporary_address'   => ['nullable', 'string', 'max:500'],
            'permanent_address'   => ['nullable', 'string', 'max:500'],
            'photo'               => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:20480'],
        ]);

        $data['license_restriction'] = !empty($data['license_restriction'])
            ? implode(',', $data['license_restriction'])
            : null;

        if ($request->hasFile('photo')) {
            if ($violator->photo) {
                Storage::disk(uploads_disk())->delete($violator->photo);
            }
            $data['photo'] = $request->file('photo')->store('violator-photos', uploads_disk());
        } else {
            unset($data['photo']);
        }

        $violator->update($data);

        return redirect()->route('violators.show', $violator)
            ->with('success', 'Motorist profile updated successfully.');
    }

    public function destroy(Violator $violator)
    {
        if ($violator->photo) {
            Storage::disk(uploads_disk())->delete($violator->photo);
        }

        $violator->delete();

        return redirect()->route('violators.index')
            ->with('success', 'Motorist record deleted.');
    }
}
