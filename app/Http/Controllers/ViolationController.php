<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Violation;
use App\Models\ViolationVehiclePhoto;
use App\Models\Violator;
use App\Models\ViolationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ViolationController extends Controller
{
    public function index(Request $request)
    {
        $query = Violation::with(['violator', 'violationType', 'vehicle']);

        if ($search = $request->input('search')) {
            $lk = '%' . mb_strtolower($search) . '%';
            $query->whereHas('violator', function ($q) use ($lk) {
                $q->whereRaw('LOWER(first_name) LIKE ?', [$lk])
                  ->orWhereRaw('LOWER(last_name) LIKE ?', [$lk])
                  ->orWhereRaw('LOWER(middle_name) LIKE ?', [$lk]);
            });
        }

        if ($plate = $request->input('plate')) {
            $plateLk = '%' . mb_strtolower($plate) . '%';
            $query->whereHas('vehicle', function ($q) use ($plateLk) {
                $q->whereRaw('LOWER(plate_number) LIKE ?', [$plateLk]);
            });
        }

        if ($typeId = $request->input('type')) {
            $query->where('violation_type_id', $typeId);
        }

        if ($status = $request->input('status')) {
            if ($status === 'overdue') {
                $query->overdue();
            } elseif ($status === 'pending') {
                $query->pendingActive();
            } else {
                $query->where('status', $status);
            }
        }

        if ($month = $request->input('month')) {
            $query->whereMonth('date_of_violation', $month);
        }

        if ($year = $request->input('year')) {
            $query->whereYear('date_of_violation', $year);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('date_of_violation', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('date_of_violation', '<=', $dateTo);
        }

        $violations = $query->orderByDesc('date_of_violation')->paginate(20)->withQueryString();
        $violationTypes = Cache::remember('violation_types', 600, fn() => ViolationType::orderBy('name')->get());

        return view('violations.index', compact('violations', 'violationTypes', 'search'));
    }

    public function create(Violator $violator)
    {
        $allVehicles    = Vehicle::with('violator:id,first_name,last_name')
            ->orderBy('plate_number')
            ->get(['id', 'violator_id', 'plate_number', 'make', 'model', 'color', 'vehicle_type']);
        $violationTypes = Cache::remember('violation_types', 600, fn() => ViolationType::orderBy('name')->get());

        return view('violations.create', compact('violator', 'allVehicles', 'violationTypes'));
    }

    public function store(Request $request, Violator $violator)
    {
        $data = $request->validate([
            'violation_type_id'  => ['required', 'exists:violation_types,id'],
            'incident_id'        => ['nullable', 'exists:incidents,id'],
            'vehicle_id'         => ['nullable', 'exists:vehicles,id'],
            'vehicle_plate'      => ['nullable', 'string', 'max:30'],
            'vehicle_type'       => ['nullable', 'in:MV,MC'],
            'vehicle_owner_name' => ['nullable', 'string', 'max:200'],
            'vehicle_make'       => ['nullable', 'string', 'max:100'],
            'vehicle_model'      => ['nullable', 'string', 'max:100'],
            'vehicle_color'      => ['nullable', 'string', 'max:50'],
            'vehicle_or_number'  => ['nullable', 'string', 'max:50'],
            'vehicle_cr_number'  => ['nullable', 'string', 'max:50'],
            'vehicle_chassis'    => ['nullable', 'string', 'max:100'],
            'photos'             => ['nullable', 'array', 'max:4'],
            'photos.*'           => ['image', 'mimes:jpg,jpeg,png', 'max:51200'],
            'date_of_violation'  => ['required', 'date', 'before_or_equal:today'],
            'location'           => ['nullable', 'string', 'max:255'],
            'ticket_number'           => ['nullable', 'string', 'max:50'],
            'citation_ticket_photo'   => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:20480'],
            'valid_id_photo'          => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:20480'],
            'status'                  => ['required', 'in:pending,settled'],
            'notes'                   => ['nullable', 'string', 'max:1000'],
        ]);

        // Normalise: empty string from the "None" select option must be null for the FK column
        if (($data['vehicle_id'] ?? '') === '') {
            $data['vehicle_id'] = null;
        }

        // If a registered vehicle is selected, clear manual fields
        if (!empty($data['vehicle_id'])) {
            foreach (['vehicle_plate', 'vehicle_make', 'vehicle_model', 'vehicle_color', 'vehicle_or_number', 'vehicle_cr_number', 'vehicle_chassis'] as $f) {
                unset($data[$f]);
            }
        } elseif (!empty($data['vehicle_plate'])) {
            // Auto-register the manually-entered vehicle so it appears on the violator's profile.
            // Search globally by plate (plate_number is unique across the whole table) so we never
            // hit a duplicate-key error when the plate is already registered to another violator.
            // withTrashed() is required: soft-deleted vehicles still hold the unique constraint.
            $existing = Vehicle::withTrashed()->where('plate_number', $data['vehicle_plate'])->first();
            if (!$existing) {
                $existing = Vehicle::create([
                    'violator_id'    => $violator->id,
                    'plate_number'   => $data['vehicle_plate'],
                    'vehicle_type'   => $data['vehicle_type'] ?? null,
                    'make'           => $data['vehicle_make'] ?? null,
                    'model'          => $data['vehicle_model'] ?? null,
                    'color'          => $data['vehicle_color'] ?? null,
                    'or_number'      => $data['vehicle_or_number'] ?? null,
                    'cr_number'      => $data['vehicle_cr_number'] ?? null,
                    'chassis_number' => $data['vehicle_chassis'] ?? null,
                ]);
            } elseif ($existing->trashed()) {
                $existing->restore();
            }
            $data['vehicle_id'] = $existing->id;
        }

        if ($request->hasFile('citation_ticket_photo')) {
            $data['citation_ticket_photo'] = $request->file('citation_ticket_photo')->store('citation-tickets', uploads_disk());
        }

        if ($request->hasFile('valid_id_photo')) {
            $data['valid_id_photo'] = $request->file('valid_id_photo')->store('valid-id-photos', uploads_disk());
        }

        unset($data['photos']);
        $data['violator_id'] = $violator->id;
        $data['recorded_by'] = Auth::id();

        $violation = Violation::create($data);

        // Save photos only for manual vehicle entry
        if ($request->hasFile('photos') && empty($request->input('vehicle_id'))) {
            foreach (\array_slice($request->file('photos'), 0, 4) as $file) {
                $path = $file->store('violation-vehicle-photos', uploads_disk());
                ViolationVehiclePhoto::create(['violation_id' => $violation->id, 'photo' => $path]);
            }
        }

        return redirect()->route('violators.show', $violator)
            ->with('success', 'Violation recorded successfully.');
    }

    public function show(Violation $violation)
    {
        $violation->load(['violator', 'vehicle.violator', 'vehicle.photos', 'violationType', 'recorder', 'vehiclePhotos', 'incident']);
        return view('violations.show', compact('violation'));
    }

    public function printRecord(Violation $violation)
    {
        $violation->load(['violator', 'vehicle.violator', 'vehicle.photos', 'violationType', 'recorder', 'vehiclePhotos', 'incident']);
        return view('violations.print', compact('violation'));
    }

    public function edit(Violation $violation)
    {
        $this->authorize('update', $violation);
        $violation->load(['violator', 'vehiclePhotos']);
        $allVehicles    = Vehicle::with('violator:id,first_name,last_name')
            ->orderBy('plate_number')
            ->get(['id', 'violator_id', 'plate_number', 'make', 'model', 'color', 'vehicle_type']);
        $violationTypes = Cache::remember('violation_types', 600, fn() => ViolationType::orderBy('name')->get());

        return view('violations.edit', compact('violation', 'allVehicles', 'violationTypes'));
    }

    public function update(Request $request, Violation $violation)
    {
        $this->authorize('update', $violation);
        $data = $request->validate([
            'violation_type_id'  => ['required', 'exists:violation_types,id'],
            'vehicle_id'         => ['nullable', 'exists:vehicles,id'],
            'vehicle_plate'      => ['nullable', 'string', 'max:30'],
            'vehicle_type'       => ['nullable', 'in:MV,MC'],
            'vehicle_owner_name' => ['nullable', 'string', 'max:200'],
            'vehicle_make'       => ['nullable', 'string', 'max:100'],
            'vehicle_model'      => ['nullable', 'string', 'max:100'],
            'vehicle_color'      => ['nullable', 'string', 'max:50'],
            'vehicle_or_number'  => ['nullable', 'string', 'max:50'],
            'vehicle_cr_number'  => ['nullable', 'string', 'max:50'],
            'vehicle_chassis'    => ['nullable', 'string', 'max:100'],
            'photos'             => ['nullable', 'array'],
            'photos.*'           => ['image', 'mimes:jpg,jpeg,png', 'max:51200'],
            'date_of_violation'  => ['required', 'date', 'before_or_equal:today'],
            'location'           => ['nullable', 'string', 'max:255'],
            'ticket_number'           => ['nullable', 'string', 'max:50'],
            'citation_ticket_photo'   => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:20480'],
            'valid_id_photo'          => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:20480'],
            'status'       => ['required', 'in:pending,settled'],
            'notes'        => ['nullable', 'string', 'max:1000'],
            'or_number'    => ['nullable', 'string', 'max:50'],
            'cashier_name' => ['nullable', 'string', 'max:150'],
            'receipt_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:20480'],
        ]);

        // Set settled_at when transitioning to settled for the first time
        if ($data['status'] === 'settled' && !$violation->settled_at) {
            $data['settled_at'] = now();
        }
        // Clear settlement fields if unsettling
        if ($data['status'] !== 'settled') {
            $data['settled_at']   = null;
            $data['or_number']    = null;
            $data['cashier_name'] = null;
            if ($violation->receipt_photo) {
                Storage::disk(uploads_disk())->delete($violation->receipt_photo);
            }
            $data['receipt_photo'] = null;
        }

        unset($data['photos']);

        // Citation ticket photo: replace or remove
        if ($request->boolean('remove_citation_photo') && !$request->hasFile('citation_ticket_photo')) {
            if ($violation->citation_ticket_photo) {
                Storage::disk(uploads_disk())->delete($violation->citation_ticket_photo);
            }
            $data['citation_ticket_photo'] = null;
        } elseif ($request->hasFile('citation_ticket_photo')) {
            if ($violation->citation_ticket_photo) {
                Storage::disk(uploads_disk())->delete($violation->citation_ticket_photo);
            }
            $data['citation_ticket_photo'] = $request->file('citation_ticket_photo')->store('citation-tickets', uploads_disk());
        } else {
            unset($data['citation_ticket_photo']);
        }

        // Valid ID photo: replace or remove
        if ($request->boolean('remove_valid_id_photo') && !$request->hasFile('valid_id_photo')) {
            if ($violation->valid_id_photo) {
                Storage::disk(uploads_disk())->delete($violation->valid_id_photo);
            }
            $data['valid_id_photo'] = null;
        } elseif ($request->hasFile('valid_id_photo')) {
            if ($violation->valid_id_photo) {
                Storage::disk(uploads_disk())->delete($violation->valid_id_photo);
            }
            $data['valid_id_photo'] = $request->file('valid_id_photo')->store('valid-id-photos', uploads_disk());
        } else {
            unset($data['valid_id_photo']);
        }

        // Receipt photo: replace or remove (only when settled)
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

        // Normalise: empty string from the "None" select option must be null for the FK column
        if (($data['vehicle_id'] ?? '') === '') {
            $data['vehicle_id'] = null;
        }

        // If switching to a registered vehicle, clear all manual vehicle fields and delete all photos
        if (!empty($data['vehicle_id'])) {
            foreach ($violation->vehiclePhotos as $p) {
                Storage::disk(uploads_disk())->delete($p->photo);
            }
            $violation->vehiclePhotos()->delete();

            foreach (['vehicle_plate', 'vehicle_make', 'vehicle_model', 'vehicle_color', 'vehicle_or_number', 'vehicle_cr_number', 'vehicle_chassis'] as $f) {
                $data[$f] = null;
            }
        } elseif (!empty($data['vehicle_plate'])) {
            // Auto-register the manually-entered vehicle so it appears on the violator's profile.
            // Search globally by plate (plate_number is unique across the whole table) so we never
            // hit a duplicate-key error when the plate is already registered to another violator.
            // withTrashed() is required: soft-deleted vehicles still hold the unique constraint.
            $existing = Vehicle::withTrashed()->where('plate_number', $data['vehicle_plate'])->first();
            if (!$existing) {
                $existing = Vehicle::create([
                    'violator_id'    => $violation->violator_id,
                    'plate_number'   => $data['vehicle_plate'],
                    'vehicle_type'   => $data['vehicle_type'] ?? null,
                    'make'           => $data['vehicle_make'] ?? null,
                    'model'          => $data['vehicle_model'] ?? null,
                    'color'          => $data['vehicle_color'] ?? null,
                    'or_number'      => $data['vehicle_or_number'] ?? null,
                    'cr_number'      => $data['vehicle_cr_number'] ?? null,
                    'chassis_number' => $data['vehicle_chassis'] ?? null,
                ]);
            } elseif ($existing->trashed()) {
                $existing->restore();
            }
            $data['vehicle_id'] = $existing->id;
        }

        $violation->update($data);

        // Add more photos for manual vehicle entry (respecting 4-photo limit)
        if ($request->hasFile('photos') && empty($request->input('vehicle_id'))) {
            $currentCount = $violation->vehiclePhotos()->count();
            $allowed      = 4 - $currentCount;

            if ($allowed <= 0) {
                return back()->withErrors(['photos' => 'Maximum 4 photos already reached. Delete an existing photo first.']);
            }

            $newFiles = $request->file('photos');
            foreach (\array_slice($newFiles, 0, $allowed) as $file) {
                $path = $file->store('violation-vehicle-photos', uploads_disk());
                ViolationVehiclePhoto::create(['violation_id' => $violation->id, 'photo' => $path]);
            }

            if (\count($newFiles) > $allowed) {
                return redirect()->route('violations.show', $violation)
                    ->with('success', 'Violation updated. Only ' . $allowed . ' photo(s) were saved (4-photo limit reached).');
            }
        }

        return redirect()->route('violations.show', $violation)
            ->with('success', 'Violation updated successfully.');
    }

    public function settle(Request $request, Violation $violation)
    {
        $this->authorize('settle', $violation);
        if ($violation->status === 'settled') {
            return back()->with('error', 'This violation has already been settled.');
        }

        $data = $request->validate([
            'or_number'     => ['required', 'string', 'max:50'],
            'cashier_name'  => ['required', 'string', 'max:150'],
            'receipt_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:20480'],
        ]);

        if ($request->hasFile('receipt_photo')) {
            if ($violation->receipt_photo) {
                Storage::disk(uploads_disk())->delete($violation->receipt_photo);
            }
            $data['receipt_photo'] = $request->file('receipt_photo')->store('receipt-photos', uploads_disk());
        }

        $data['status']     = 'settled';
        $data['settled_at'] = now();
        $violation->update($data);

        return back()->with('success', 'Violation settled successfully.');
    }

    public function destroy(Violation $violation)
    {
        $this->authorize('delete', $violation);
        foreach ($violation->vehiclePhotos as $p) {
            Storage::disk(uploads_disk())->delete($p->photo);
        }

        if ($violation->citation_ticket_photo) {
            Storage::disk(uploads_disk())->delete($violation->citation_ticket_photo);
        }

        if ($violation->valid_id_photo) {
            Storage::disk(uploads_disk())->delete($violation->valid_id_photo);
        }

        if ($violation->receipt_photo) {
            Storage::disk(uploads_disk())->delete($violation->receipt_photo);
        }

        $violatorId = $violation->violator_id;
        $violation->delete();

        if ($violatorId && Violator::find($violatorId)) {
            return redirect()->route('violators.show', $violatorId)
                ->with('success', 'Violation record deleted.');
        }

        return redirect()->route('violations.index')
            ->with('success', 'Violation record deleted.');
    }
}
