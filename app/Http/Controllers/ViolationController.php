<?php

namespace App\Http\Controllers;

use App\Models\Lgu;
use App\Models\Vehicle;
use App\Models\Violation;
use App\Models\ViolationVehiclePhoto;
use App\Models\Violator;
use App\Models\ViolationType;
use App\Services\NotificationService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ViolationController extends Controller
{
    public function index(Request $request)
    {
        $query = Violation::with(['violator', 'violationType', 'vehicle', 'lgu']);

        $user = Auth::user();
        if (Schema::hasColumn('violations', 'lgu_id')) {
            if ($user && $user->lgu_id && !$user->isSuperAdmin() && !$user->isProvinceAdmin()) {
                $query->where('lgu_id', $user->lgu_id);
            } elseif ($lguId = $request->input('lgu_id')) {
                $query->where('lgu_id', $lguId);
            }
        }

        $search = $request->input('search') ?: $request->input('plate');
        if ($search) {
            $lk = '%' . mb_strtolower(trim($search)) . '%';
            $query->where(function ($q) use ($lk) {
                $q->whereHas('violator', function ($sq) use ($lk) {
                    $sq->whereRaw('LOWER(first_name) LIKE ?', [$lk])
                      ->orWhereRaw('LOWER(last_name) LIKE ?', [$lk])
                      ->orWhereRaw('LOWER(middle_name) LIKE ?', [$lk])
                      ->orWhereRaw("LOWER(CONCAT(first_name, ' ', last_name)) LIKE ?", [$lk])
                      ->orWhereRaw("LOWER(CONCAT(last_name, ' ', first_name)) LIKE ?", [$lk]);
                })
                ->orWhereHas('vehicle', function ($sq) use ($lk) {
                    $sq->whereRaw('LOWER(plate_number) LIKE ?', [$lk]);
                })
                ->orWhereRaw('LOWER(vehicle_plate) LIKE ?', [$lk])
                ->orWhereRaw('LOWER(ticket_number) LIKE ?', [$lk]);
            });
        }

        if ($typeId = $request->input('type')) {
            $query->where('violation_type_id', $typeId);
        }

        if ($status = $request->input('status')) {
            if ($status === 'overdue') {
                if (Schema::hasColumn('violations', 'due_date')) {
                    $query->overdue();
                } else {
                    $query->whereIn('status', ['pending', 'partial']);
                }
            } else {
                $query->where('status', $status);
            }
        }

        if ($month = $request->input('month')) {
            if (is_numeric($month) && (int)$month >= 1 && (int)$month <= 12) {
                $query->whereMonth('date_of_violation', (int)$month);
            }
        }

        if ($year = $request->input('year')) {
            if (is_numeric($year) && (int)$year > 1900) {
                $query->whereYear('date_of_violation', (int)$year);
            }
        }

        if ($dateFrom = $request->input('date_from')) {
            try {
                $parsed = \Carbon\Carbon::parse($dateFrom)->toDateString();
                $query->whereDate('date_of_violation', '>=', $parsed);
            } catch (\Throwable $e) {}
        }

        if ($dateTo = $request->input('date_to')) {
            try {
                $parsed = \Carbon\Carbon::parse($dateTo)->toDateString();
                $query->whereDate('date_of_violation', '<=', $parsed);
            } catch (\Throwable $e) {}
        }

        $today = now()->toDateString();
        if (Schema::hasColumn('violations', 'due_date')) {
            $query->orderByRaw("
                CASE
                    WHEN status = 'pending' AND (due_date IS NULL OR due_date >= '{$today}') THEN 1
                    WHEN (status IN ('pending', 'partial') AND due_date IS NOT NULL AND due_date < '{$today}') THEN 2
                    WHEN status = 'partial' AND (due_date IS NULL OR due_date >= '{$today}') THEN 3
                    WHEN status = 'settled' THEN 4
                    ELSE 5
                END ASC
            ");
        } else {
            $query->orderByRaw("
                CASE
                    WHEN status = 'pending' THEN 1
                    WHEN status = 'partial' THEN 3
                    WHEN status = 'settled' THEN 4
                    ELSE 5
                END ASC
            ");
        }

        $violations = $query->orderByDesc('date_of_violation')->paginate(20)->withQueryString();

        try {
            $violationTypes = Cache::remember('violation_types', 600, fn() => ViolationType::orderBy('name')->get());
        } catch (\Throwable $e) {
            $violationTypes = ViolationType::orderBy('name')->get();
        }

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
            'gps_lat'            => ['nullable', 'numeric', 'between:-90,90'],
            'gps_lng'            => ['nullable', 'numeric', 'between:-180,180'],
            'ticket_number'           => ['nullable', 'string', 'max:50', 'unique:violations,ticket_number'],
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
        $data['lgu_id']      = Lgu::findByPsgcCityCode($request->input('_loc_city_code'))?->id ?? Auth::user()->lgu_id ?? $violator->lgu_id;

        $violation = Violation::create($data);

        app(NotificationService::class)->notifyNewViolation($violation);

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
        $violation->load(['violator', 'vehicle.violator', 'vehicle.photos', 'violationType', 'recorder', 'vehiclePhotos', 'incident', 'payments']);
        return view('violations.show', compact('violation'));
    }

    public function printRecord(Violation $violation)
    {
        $violation->load(['violator', 'vehicle.violator', 'vehicle.photos', 'violationType', 'recorder', 'vehiclePhotos', 'incident', 'payments']);
        return view('violations.print', compact('violation'));
    }

    /** Compact 58mm receipt layout for portable Bluetooth ticket printers (e.g. PT-210). */
    public function printThermal(Violation $violation)
    {
        $violation->load(['violator', 'vehicle', 'violationType', 'recorder', 'lgu']);

        return view('violations.print-thermal', compact('violation'));
    }

    /** Dedicated payment receipt for a specific transaction. */
    public function printReceipt(Violation $violation, \App\Models\Payment $payment)
    {
        // Ensure the payment belongs to this violation
        abort_unless((int) $payment->violation_id === (int) $violation->id, 404);

        $violation->load(['violator', 'violationType', 'lgu', 'payments']);

        return view('violations.print-receipt', compact('violation', 'payment'));
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
            'gps_lat'            => ['nullable', 'numeric', 'between:-90,90'],
            'gps_lng'            => ['nullable', 'numeric', 'between:-180,180'],
            'ticket_number'           => ['nullable', 'string', 'max:50'],
            'citation_ticket_photo'   => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:20480'],
            'valid_id_photo'          => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:20480'],
            'status'       => ['required', 'in:pending,partial,settled,contested'],
            'notes'        => ['nullable', 'string', 'max:1000'],
        ]);

        // Settlement state (pending/partial/settled) is payment-driven and can only
        // change via the Settle action (ViolationController::settle -> PaymentService),
        // which is the only path that keeps the `payments` table in sync. This form
        // may only toggle between pending and contested for records not yet settled;
        // a tampered request can neither un-settle a paid record nor fake one as settled.
        if (in_array($violation->status, ['settled', 'partial'], true)) {
            $data['status'] = $violation->status;
        } elseif (!in_array($data['status'], ['pending', 'contested'], true)) {
            $data['status'] = 'pending';
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

        // Only touch lgu_id when the location selector actually resolved a city —
        // editing without re-picking a location must not wipe the existing LGU tag.
        if ($cityCode = $request->input('_loc_city_code')) {
            $data['lgu_id'] = Lgu::findByPsgcCityCode($cityCode)?->id;
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

        $violation->loadMissing('violationType');
        $balance = $violation->balanceRemaining();

        $request->merge(['cashier_name' => Auth::user()->name]);

        $data = $request->validate([
            'or_number'      => ['required', 'string', 'max:50', Rule::unique('payments', 'or_number')],
            'cashier_name'   => ['required', 'string', 'max:150'],
            'payment_method' => ['required', 'in:cash,gcash,maya,bank,other'],
            'amount_paid'    => ['nullable', 'numeric', 'min:0.01', 'max:' . max($balance, 0.01)],
            'receipt_photo'  => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:20480'],
        ]);

        // #2 — Server-enforce cashier name to prevent spoofing
        $data['cashier_name'] = Auth::user()->name;

        if ($request->hasFile('receipt_photo')) {
            $file = $request->file('receipt_photo');
            $data['receipt_photo'] = $file->store('receipt-photos', uploads_disk());

            // #6 — Strip EXIF metadata by re-encoding through GD
            $storedPath = Storage::disk(uploads_disk())->path($data['receipt_photo']);
            $mime = $file->getMimeType();
            $img = match (true) {
                str_contains($mime, 'png')  => @imagecreatefrompng($storedPath),
                str_contains($mime, 'jpeg'), str_contains($mime, 'jpg') => @imagecreatefromjpeg($storedPath),
                default => null,
            };
            if ($img) {
                match (true) {
                    str_contains($mime, 'png')  => imagepng($img, $storedPath),
                    default => imagejpeg($img, $storedPath, 90),
                };
                imagedestroy($img);
            }
        }

        try {
            app(PaymentService::class)->recordPayment($violation, $data, Auth::user());
        } catch (\InvalidArgumentException $e) {
            if (!empty($data['receipt_photo'])) {
                Storage::disk(uploads_disk())->delete($data['receipt_photo']);
            }
            return back()->withInput()->with('error', $e->getMessage());
        }

        $violation->refresh();
        $message = $violation->status === 'settled'
            ? 'Violation settled successfully.'
            : 'Partial payment recorded. Remaining balance: ₱' . number_format($violation->balanceRemaining(), 2) . '.';

        return back()->with('success', $message);
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

    public function cashier(Request $request)
    {
        $search = trim($request->input('search', ''));
        $violation = null;
        $user = Auth::user();

        if ($search !== '') {
            $rawSearch   = trim($search);
            $cleanSearch = str_replace(['-', ' ', ','], '', $rawSearch);
            $isPgsql     = DB::getDriverName() === 'pgsql';
            $op          = $isPgsql ? 'ilike' : 'like';

            // Always match using lowercase so PostgreSQL ILIKE works correctly
            $lowerSearch = mb_strtolower($rawSearch);
            $like        = '%' . $lowerSearch . '%';
            $cleanLike   = '%' . mb_strtolower($cleanSearch) . '%';

            // Helper to wrap column in LOWER() for SQLite (ILIKE on pgsql handles it natively)
            $col = fn(string $c) => $isPgsql ? $c : "LOWER($c)";

            $query = Violation::with(['violator', 'violationType', 'vehicle', 'payments'])
                ->withSum('activePayments as total_paid', 'amount_paid')
                ->where(function ($q) use ($rawSearch, $like, $cleanLike, $op, $col, $isPgsql, $lowerSearch) {
                    // 1. Direct Ticket # or Record ID
                    $q->where('ticket_number', $op, $like)
                      ->orWhere('id', (int) $rawSearch)
                      // 2. Violator Name / License # / Contact #
                      ->orWhereHas('violator', function ($vq) use ($rawSearch, $like, $cleanLike, $op, $col, $isPgsql, $lowerSearch) {
                          $vq->where('first_name', $op, $like)
                             ->orWhere('last_name', $op, $like)
                             ->orWhere('middle_name', $op, $like)
                             ->orWhere('license_number', $op, $like)
                             // Flexible license number matching (strip hyphens/spaces)
                             ->orWhereRaw(
                                 "REPLACE(REPLACE(" . ($isPgsql ? 'license_number' : 'LOWER(license_number)') . ", '-', ''), ' ', '') $op ?",
                                 [$cleanLike]
                             )
                             // Concatenated full-name patterns
                             ->orWhereRaw("LOWER(CONCAT(last_name, ', ', first_name)) $op ?",  [$like])
                             ->orWhereRaw("LOWER(CONCAT(first_name, ' ', last_name)) $op ?",   [$like])
                             ->orWhereRaw("LOWER(CONCAT(last_name, ' ', first_name)) $op ?",   [$like])
                             ->orWhereRaw("LOWER(CONCAT(first_name, ' ', middle_name, ' ', last_name)) $op ?", [$like]);

                          // Split multi-word queries into per-token AND conditions
                          $words = array_filter(explode(' ', str_replace(',', ' ', $lowerSearch)));
                          if (count($words) > 1) {
                              $vq->orWhere(function ($subQ) use ($words, $op) {
                                  foreach ($words as $w) {
                                      $wLike = '%' . $w . '%';
                                      $subQ->where(function ($wQ) use ($wLike, $op) {
                                          $wQ->where('first_name',  $op, $wLike)
                                             ->orWhere('last_name',  $op, $wLike)
                                             ->orWhere('middle_name', $op, $wLike);
                                      });
                                  }
                              });
                          }
                      })
                      // 3. Vehicle Plate / Registered Owner
                      ->orWhere('vehicle_plate',      $op, $like)
                      ->orWhere('vehicle_owner_name', $op, $like)
                      ->orWhereHas('vehicle', fn($vq) =>
                          $vq->where('plate_number', $op, $like)
                             ->orWhereRaw(
                                 "REPLACE(" . ($isPgsql ? 'plate_number' : 'LOWER(plate_number)') . ", '-', '') $op ?",
                                 [$cleanLike]
                             )
                      );
                });

            // Scope by assigned LGU for cashiers
            if ($user->lgu_id) {
                $query->where('lgu_id', $user->lgu_id);
            }

            // Prioritize unpaid/pending tickets over already settled ones
            $query->orderByRaw("CASE WHEN status IN ('pending', 'partial') THEN 0 ELSE 1 END")
                  ->orderBy('date_of_violation', 'desc');

            $violation = $query->first();
        }

        // ── AJAX live-search: return JSON so the page can update without a reload ─
        if ($request->ajax() || $request->wantsJson()) {
            if (!$search) {
                return response()->json(['found' => false, 'empty' => true]);
            }
            if (!$violation) {
                return response()->json(['found' => false, 'empty' => false, 'search' => $search]);
            }

            $isOverdue = $violation->isOverdue();
            $displayStatus = $isOverdue ? 'overdue' : $violation->status;

            return response()->json([
                'found'         => true,
                'id'            => $violation->id,
                'ticket_number' => $violation->ticket_number ?: '#' . $violation->id,
                'status'        => $violation->status,
                'display_status'=> $displayStatus,
                'violator_name' => $violation->violator?->full_name,
                'license_number'=> $violation->violator?->license_number,
                'violation_type'=> $violation->violationType?->name,
                'fine_amount'   => $violation->violationType?->fine_amount,
                'late_penalty'  => $violation->isOverdue() ? $violation->latePenaltyAmount() : 0,
                'total_paid'    => $violation->totalAmountPaid(),
                'balance'       => $violation->balanceRemaining(),
                'date'          => $violation->date_of_violation->format('M d, Y'),
                'location'      => $violation->location,
                'plate'         => $violation->vehicle?->plate_number ?? $violation->vehicle_plate,
                'settle_url'    => route('violations.settle', $violation),
                'print_url'     => route('violations.print', $violation),
                'cashier_url'   => route('violations.cashier', ['search' => $violation->ticket_number]),
            ]);
        }

        $pendingQuery = Violation::with(['violator', 'violationType'])
            ->withSum('activePayments as total_paid', 'amount_paid')
            ->whereIn('status', ['pending', 'partial'])
            ->orderBy('date_of_violation', 'desc');

        if ($user->lgu_id) {
            $pendingQuery->where('lgu_id', $user->lgu_id);
        }

        // #13/#21 — Paginated pending tickets (15 per page)
        $pendingTickets = $pendingQuery->paginate(15)->withQueryString();

        return view('violations.cashier', compact('violation', 'search', 'pendingTickets'));
    }
}
