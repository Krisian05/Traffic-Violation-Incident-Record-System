<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Incident;
use App\Models\IncidentMedia;
use App\Models\IncidentMotorist;
use App\Models\IncidentChargeType;
use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use App\Models\Violator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class IncidentController extends Controller
{
    public function index(Request $request): View
    {
        $search    = trim((string) $request->input('search', ''));
        $dateFrom  = $this->normalizeDateFilter($request->input('date_from'));
        $dateTo    = $this->normalizeDateFilter($request->input('date_to'));
        $status    = $this->normalizeStatusFilter($request->input('status'));

        if ($dateFrom !== '' && $dateTo !== '' && $dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        $query = Incident::with(['motorists.violator', 'media', 'recorder'])
            ->withCount(['motorists', 'media']);

        if ($search !== '') {
            $lk = '%' . $search . '%';
            $searchTerms = array_values(array_filter(
                preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) ?: []
            ));

            $query->where(function ($q) use ($lk, $searchTerms) {
                $q->whereLike('location', $lk)
                  ->orWhereLike('incident_number', $lk)
                  ->orWhereHas('motorists', function ($mq) use ($lk, $searchTerms) {
                      $mq->whereLike('motorist_name', $lk)
                         ->orWhereHas('violator', function ($vq) use ($searchTerms) {
                             foreach ($searchTerms as $term) {
                                 $termLike = '%' . $term . '%';

                                 $vq->where(function ($nameQ) use ($termLike) {
                                     $nameQ->whereLike('first_name', $termLike)
                                         ->orWhereLike('middle_name', $termLike)
                                         ->orWhereLike('last_name', $termLike);
                                 });
                             }
                         });
                  });
            });
        }

        if ($dateFrom !== '') {
            $query->whereDate('date_of_incident', '>=', $dateFrom);
        }
        if ($dateTo !== '') {
            $query->whereDate('date_of_incident', '<=', $dateTo);
        }
        if ($status !== '') {
            $query->where('status', $status);
        }

        $incidents = $query->orderByDesc('date_of_incident')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('incidents.index', compact('incidents', 'search', 'dateFrom', 'dateTo', 'status'));
    }

    public function create(): View
    {
        $chargeTypes     = IncidentChargeType::orderBy('name')->get();
        $violators       = Violator::orderBy('last_name')->orderBy('first_name')->get(['id', 'first_name', 'middle_name', 'last_name']);
        $vehiclesByOwner = Vehicle::orderBy('plate_number')
            ->get(['id', 'violator_id', 'plate_number', 'vehicle_type', 'make', 'model'])
            ->groupBy('violator_id');

        return view('incidents.create', compact('chargeTypes', 'violators', 'vehiclesByOwner'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date_of_incident'                  => 'required|date|before_or_equal:today',
            'time_of_incident'                  => 'nullable|date_format:H:i',
            'location'                          => 'required|string|max:255',
            'description'                       => 'nullable|string|max:2000',
            'status'                            => 'required|in:cleared,under_investigation,solved,settled',
            'motorists'                         => 'required|array|min:1|max:10',
            'motorists.*.violator_id'           => 'nullable|exists:violators,id',
            'motorists.*.motorist_name'         => 'nullable|string|max:200',
            'motorists.*.motorist_license'      => 'nullable|string|max:100',
            'motorists.*.incident_charge_type_id' => 'nullable|exists:incident_charge_types,id',
            'motorists.*.notes'                        => 'nullable|string|max:500',
            'motorists.*.vehicle_owner_violator_id'    => 'nullable|exists:violators,id',
            'motorists.*.vehicle_owner_name'           => 'nullable|string|max:200',
            'motorists.*.vehicle_owner_contact'        => 'nullable|string|max:100',
            'motorist_photos'                     => 'nullable|array',
            'motorist_photos.*'                   => 'nullable|array|max:4',
            'motorist_photos.*.*'                 => 'nullable|file|mimes:jpg,jpeg,png|max:20480',
            'motorist_id_photos'                  => 'nullable|array',
            'motorist_id_photos.*'                => 'nullable|file|mimes:jpg,jpeg,png|max:20480',
            'motorists.*.license_type'            => 'nullable|string|max:50',
            'motorists.*.license_restriction'     => 'nullable|array',
            'motorists.*.license_restriction.*'   => 'nullable|string|max:10',
            'motorists.*.license_expiry_date'   => 'nullable|date',
            'media'                             => 'nullable|array|max:20',
            'media.*'                           => 'file|mimes:jpg,jpeg,png,pdf|max:20480',
            'media_types'                       => 'nullable|array',
            'media_types.*'                     => 'in:scene,ticket,document,other',
            'captions'                          => 'nullable|array',
            'captions.*'                        => 'nullable|string|max:200',
        ]);

        // Ensure each motorist has a name or linked violator
        foreach ($request->input('motorists', []) as $i => $m) {
            if (empty($m['violator_id']) && empty($m['motorist_name'])) {
                return back()->withErrors(["motorists.{$i}.motorist_name" => 'Each motorist must have a name or be linked to a registered motorist.'])->withInput();
            }
        }

        $incident = DB::transaction(function () use ($request, $validated) {
            $incident = Incident::create([
                'date_of_incident' => $validated['date_of_incident'],
                'time_of_incident' => $validated['time_of_incident'] ?? null,
                'location'         => $validated['location'],
                'description'      => $validated['description'] ?? null,
                'status'           => $validated['status'],
                'recorded_by'      => Auth::id(),
            ]);

            $vehiclePhotos    = $request->file('motorist_photos', []);
            $motoristIdPhotos = $request->file('motorist_id_photos', []);
            foreach ($request->input('motorists', []) as $i => $m) {
                $vehiclePathArr = [];
                if (!empty($vehiclePhotos[$i]) && is_array($vehiclePhotos[$i])) {
                    /** @var \Illuminate\Http\UploadedFile[] $photoFiles */
                    $photoFiles = $vehiclePhotos[$i];
                    foreach (array_slice($photoFiles, 0, 4) as $photo) {
                        if ($photo && $photo->isValid()) {
                            $vehiclePathArr[] = $photo->store('motorist-photos', uploads_disk());
                        }
                    }
                }
                $idPhotoPath = !empty($motoristIdPhotos[$i]) ? $motoristIdPhotos[$i]->store('motorist-id-photos', uploads_disk()) : null;
                // Resolve driver, owner, and vehicle — each linked to the correct Violator record
                $m = $this->resolveIncidentMotorist($m);

                $incident->motorists()->create([
                    'violator_id'                  => $m['violator_id'] ?? null,
                    'motorist_name'                => $m['motorist_name'] ?? null,
                    'motorist_license'             => $m['motorist_license'] ?? null,
                    'motorist_photo'               => $idPhotoPath,
                    'motorist_contact'             => $m['motorist_contact'] ?? null,
                    'motorist_address'             => $m['motorist_address'] ?? null,
                    'license_type'                 => $m['license_type'] ?? null,
                    'license_restriction'          => !empty($m['license_restriction']) ? implode(',', (array) $m['license_restriction']) : null,
                    'license_expiry_date'          => $m['license_expiry_date'] ?? null,
                    'vehicle_id'                   => !empty($m['vehicle_id']) ? $m['vehicle_id'] : null,
                    'vehicle_plate'                => $m['vehicle_plate'] ?? null,
                    'vehicle_type_manual'          => $m['vehicle_type_manual'] ?? null,
                    'vehicle_make'                 => $m['vehicle_make'] ?? null,
                    'vehicle_model'                => $m['vehicle_model'] ?? null,
                    'vehicle_color'                => $m['vehicle_color'] ?? null,
                    'vehicle_or_number'            => $m['vehicle_or_number'] ?? null,
                    'vehicle_cr_number'            => $m['vehicle_cr_number'] ?? null,
                    'vehicle_chassis'              => $m['vehicle_chassis'] ?? null,
                    'vehicle_photo'                => !empty($vehiclePathArr) ? $vehiclePathArr : null,
                    'vehicle_owner_violator_id'    => !empty($m['vehicle_owner_violator_id']) ? $m['vehicle_owner_violator_id'] : null,
                    'vehicle_owner_name'           => $m['vehicle_owner_name'] ?? null,
                    'vehicle_owner_contact'        => $m['vehicle_owner_contact'] ?? null,
                    'incident_charge_type_id'      => $m['incident_charge_type_id'] ?? null,
                    'notes'                        => $m['notes'] ?? null,
                ]);

                // Sync vehicle photos to the vehicle_photos table so they appear in the vehicle profile
                if (!empty($m['vehicle_id']) && !empty($vehiclePathArr)) {
                    foreach ($vehiclePathArr as $path) {
                        VehiclePhoto::create(['vehicle_id' => $m['vehicle_id'], 'photo' => $path]);
                    }
                }
            }

            if ($request->hasFile('media')) {
                $mediaTypes = $request->input('media_types', []);
                $captions   = $request->input('captions', []);

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

        return redirect()->route('incidents.show', $incident)
            ->with('success', 'Incident ' . e($incident->incident_number) . ' recorded successfully.');
    }

    public function show(Incident $incident): View
    {
        $incident->load(['motorists.violator', 'motorists.vehicle', 'motorists.chargeType', 'motorists.ownerViolator', 'media', 'recorder']);

        return view('incidents.show', compact('incident'));
    }

    public function edit(Incident $incident): View
    {
        $this->authorize('update', $incident);
        $incident->load(['motorists', 'media']);
        $chargeTypes     = IncidentChargeType::orderBy('name')->get();
        $violators       = Violator::orderBy('last_name')->orderBy('first_name')->get(['id', 'first_name', 'middle_name', 'last_name']);
        $vehiclesByOwner = Vehicle::orderBy('plate_number')
            ->get(['id', 'violator_id', 'plate_number', 'vehicle_type', 'make', 'model'])
            ->groupBy('violator_id');

        return view('incidents.edit', compact('incident', 'chargeTypes', 'violators', 'vehiclesByOwner'));
    }

    public function update(Request $request, Incident $incident): RedirectResponse
    {
        $this->authorize('update', $incident);
        $validated = $request->validate([
            'date_of_incident'              => 'required|date|before_or_equal:today',
            'time_of_incident'              => 'nullable|date_format:H:i',
            'location'                      => 'required|string|max:255',
            'description'                   => 'nullable|string|max:2000',
            'status'                        => 'required|in:cleared,under_investigation,solved,settled',
            'motorists'                     => 'required|array|min:1|max:10',
            'motorists.*.motorist_id'       => 'nullable|integer|exists:incident_motorists,id',
            'motorists.*.violator_id'       => 'nullable|exists:violators,id',
            'motorists.*.motorist_name'     => 'nullable|string|max:200',
            'motorists.*.motorist_license'  => 'nullable|string|max:100',
            'motorists.*.incident_charge_type_id' => 'nullable|exists:incident_charge_types,id',
            'motorists.*.notes'                        => 'nullable|string|max:500',
            'motorists.*.vehicle_owner_violator_id'    => 'nullable|exists:violators,id',
            'motorists.*.vehicle_owner_name'           => 'nullable|string|max:200',
            'motorists.*.vehicle_owner_contact'        => 'nullable|string|max:100',
            'motorists.*.license_type'          => 'nullable|string|max:50',
            'motorists.*.license_restriction'   => 'nullable|array',
            'motorists.*.license_restriction.*' => 'nullable|string|max:10',
            'motorists.*.license_expiry_date'   => 'nullable|date',
            'motorist_photos'                         => 'nullable|array',
            'motorist_photos.*'                       => 'nullable|array|max:4',
            'motorist_photos.*.*'                     => 'nullable|file|mimes:jpg,jpeg,png|max:20480',
            'motorist_id_photos'                      => 'nullable|array',
            'motorist_id_photos.*'                    => 'nullable|file|mimes:jpg,jpeg,png|max:20480',
            'motorists.*.existing_vehicle_photos'     => 'nullable|array|max:4',
            'motorists.*.existing_vehicle_photos.*'   => 'nullable|string|max:500',
            'motorists.*.existing_motorist_photo'     => 'nullable|string|max:500',
            'media'                           => 'nullable|array|max:20',
            'media.*'                         => 'file|mimes:jpg,jpeg,png,pdf|max:20480',
            'media_types'                     => 'nullable|array',
            'media_types.*'                   => 'in:scene,ticket,document,other',
            'captions'                        => 'nullable|array',
            'captions.*'                      => 'nullable|string|max:200',
        ]);

        foreach ($request->input('motorists', []) as $i => $m) {
            if (empty($m['violator_id']) && empty($m['motorist_name'])) {
                return back()->withErrors(["motorists.{$i}.motorist_name" => 'Each motorist must have a name or be linked to a registered motorist.'])->withInput();
            }
        }

        DB::transaction(function () use ($request, $validated, $incident) {
            $incident->update([
                'date_of_incident' => $validated['date_of_incident'],
                'time_of_incident' => $validated['time_of_incident'] ?? null,
                'location'         => $validated['location'],
                'description'      => $validated['description'] ?? null,
                'status'           => $validated['status'],
            ]);

            // Collect the IDs of motorists being kept/updated
            $submittedIds = collect($request->input('motorists', []))
                ->pluck('motorist_id')
                ->filter()
                ->map(fn($id) => (int) $id)
                ->toArray();

            // Delete removed motorists and their associated files
            $incident->motorists()->whereNotIn('id', $submittedIds)->each(function (IncidentMotorist $m) {
                foreach ($m->vehicle_photo ?? [] as $path) {
                    if (!Storage::disk(uploads_disk())->delete($path)) {
                        Log::warning("Failed to delete vehicle photo: {$path}");
                    }
                }
                if ($m->motorist_photo) {
                    if (!Storage::disk(uploads_disk())->delete($m->motorist_photo)) {
                        Log::warning("Failed to delete motorist photo: {$m->motorist_photo}");
                    }
                }
                $m->delete();
            });

            $vehiclePhotos    = $request->file('motorist_photos', []);
            $motoristIdPhotos = $request->file('motorist_id_photos', []);

            foreach ($request->input('motorists', []) as $i => $m) {
                $existingVehiclePhotos = array_values(array_filter((array) ($m['existing_vehicle_photos'] ?? [])));
                $newVehiclePathArr     = [];

                if (!empty($vehiclePhotos[$i]) && is_array($vehiclePhotos[$i])) {
                    /** @var \Illuminate\Http\UploadedFile[] $photoFiles */
                    $photoFiles = $vehiclePhotos[$i];
                    $remaining  = max(0, 4 - count($existingVehiclePhotos));
                    foreach (array_slice($photoFiles, 0, $remaining) as $photo) {
                        if ($photo && $photo->isValid()) {
                            $newVehiclePathArr[] = $photo->store('motorist-photos', uploads_disk());
                        }
                    }
                }
                $vehiclePathArr = array_values(array_merge($existingVehiclePhotos, $newVehiclePathArr));

                // Motorist ID photo
                if (!empty($motoristIdPhotos[$i])) {
                    $idPhotoPath = $motoristIdPhotos[$i]->store('motorist-id-photos', uploads_disk());
                } else {
                    $idPhotoPath = !empty($m['existing_motorist_photo']) ? $m['existing_motorist_photo'] : null;
                }

                $motoristData = [
                    'violator_id'                  => $m['violator_id'] ?? null,
                    'motorist_name'                => $m['motorist_name'] ?? null,
                    'motorist_license'             => $m['motorist_license'] ?? null,
                    'motorist_photo'               => $idPhotoPath,
                    'motorist_contact'             => $m['motorist_contact'] ?? null,
                    'motorist_address'             => $m['motorist_address'] ?? null,
                    'license_type'                 => $m['license_type'] ?? null,
                    'license_restriction'          => !empty($m['license_restriction']) ? implode(',', (array) $m['license_restriction']) : null,
                    'license_expiry_date'          => $m['license_expiry_date'] ?? null,
                    'vehicle_id'                   => !empty($m['vehicle_id']) ? $m['vehicle_id'] : null,
                    'vehicle_plate'                => $m['vehicle_plate'] ?? null,
                    'vehicle_type_manual'          => $m['vehicle_type_manual'] ?? null,
                    'vehicle_make'                 => $m['vehicle_make'] ?? null,
                    'vehicle_model'                => $m['vehicle_model'] ?? null,
                    'vehicle_color'                => $m['vehicle_color'] ?? null,
                    'vehicle_or_number'            => $m['vehicle_or_number'] ?? null,
                    'vehicle_cr_number'            => $m['vehicle_cr_number'] ?? null,
                    'vehicle_chassis'              => $m['vehicle_chassis'] ?? null,
                    'vehicle_photo'                => !empty($vehiclePathArr) ? $vehiclePathArr : null,
                    'vehicle_owner_violator_id'    => !empty($m['vehicle_owner_violator_id']) ? $m['vehicle_owner_violator_id'] : null,
                    'vehicle_owner_name'           => $m['vehicle_owner_name'] ?? null,
                    'vehicle_owner_contact'        => $m['vehicle_owner_contact'] ?? null,
                    'incident_charge_type_id'      => $m['incident_charge_type_id'] ?? null,
                    'notes'                        => $m['notes'] ?? null,
                ];

                if (!empty($m['motorist_id'])) {
                    // Selectively update existing motorist row
                    $motorist = IncidentMotorist::where('id', (int) $m['motorist_id'])
                        ->where('incident_id', $incident->id)
                        ->first();

                    if ($motorist) {
                        // Resolve driver, owner, and vehicle for existing motorist row
                        if (empty($motoristData['violator_id']) && !empty($m['motorist_name'])) {
                            $resolved = $this->resolveIncidentMotorist($m);
                            $motoristData['violator_id']             = $resolved['violator_id'] ?? null;
                            $motoristData['vehicle_owner_violator_id'] = $resolved['vehicle_owner_violator_id'] ?? null;
                            if (empty($motoristData['vehicle_id'])) {
                                $motoristData['vehicle_id'] = $resolved['vehicle_id'] ?? null;
                            }
                        }
                        // Delete vehicle photos that were removed by user
                        foreach (array_diff($motorist->vehicle_photo ?? [], $existingVehiclePhotos) as $orphan) {
                            if (!Storage::disk(uploads_disk())->delete($orphan)) {
                                Log::warning("Failed to delete orphaned vehicle photo: {$orphan}");
                            }
                        }
                        // Delete old ID photo if a new one was uploaded
                        if (!empty($motoristIdPhotos[$i]) && $motorist->motorist_photo && $motorist->motorist_photo !== $idPhotoPath) {
                            Storage::disk(uploads_disk())->delete($motorist->motorist_photo);
                        }
                        $motorist->update($motoristData);

                        // Sync any newly uploaded vehicle photos to the vehicle_photos table
                        $vehicleId = $motoristData['vehicle_id'] ?? $motorist->vehicle_id;
                        if ($vehicleId && !empty($newVehiclePathArr)) {
                            foreach ($newVehiclePathArr as $path) {
                                VehiclePhoto::create(['vehicle_id' => $vehicleId, 'photo' => $path]);
                            }
                        }
                    } else {
                        $newMotorist = $incident->motorists()->create($motoristData);
                        if (!empty($motoristData['vehicle_id']) && !empty($newVehiclePathArr)) {
                            foreach ($newVehiclePathArr as $path) {
                                VehiclePhoto::create(['vehicle_id' => $motoristData['vehicle_id'], 'photo' => $path]);
                            }
                        }
                    }
                } else {
                    // Resolve driver, owner, and vehicle for new motorist row
                    if (empty($motoristData['violator_id']) && !empty($m['motorist_name'])) {
                        $resolved = $this->resolveIncidentMotorist($m);
                        $motoristData['violator_id']               = $resolved['violator_id'] ?? null;
                        $motoristData['vehicle_owner_violator_id'] = $resolved['vehicle_owner_violator_id'] ?? null;
                        if (empty($motoristData['vehicle_id'])) {
                            $motoristData['vehicle_id'] = $resolved['vehicle_id'] ?? null;
                        }
                    }
                    $incident->motorists()->create($motoristData);

                    // Sync vehicle photos to vehicle_photos table
                    if (!empty($motoristData['vehicle_id']) && !empty($vehiclePathArr)) {
                        foreach ($vehiclePathArr as $path) {
                            VehiclePhoto::create(['vehicle_id' => $motoristData['vehicle_id'], 'photo' => $path]);
                        }
                    }
                }
            }

            // Add new media files (existing media stays unless deleted individually)
            if ($request->hasFile('media')) {
                $mediaTypes = $request->input('media_types', []);
                $captions   = $request->input('captions', []);

                foreach ($request->file('media') as $i => $file) {
                    $path = $file->store('incident-media', uploads_disk());
                    $incident->media()->create([
                        'file_path'  => $path,
                        'media_type' => $mediaTypes[$i] ?? 'scene',
                        'caption'    => $captions[$i] ?? null,
                    ]);
                }
            }
        });

        return redirect()->route('incidents.show', $incident)
            ->with('success', 'Incident ' . e($incident->incident_number) . ' updated.');
    }

    public function destroy(Incident $incident): RedirectResponse
    {
        $this->authorize('delete', $incident);
        foreach ($incident->media as $media) {
            if (!Storage::disk(uploads_disk())->delete($media->file_path)) {
                Log::warning("Failed to delete incident media: {$media->file_path}");
            }
        }
        foreach ($incident->motorists as $motorist) {
            foreach ($motorist->vehicle_photo ?? [] as $path) {
                if (!Storage::disk(uploads_disk())->delete($path)) {
                    Log::warning("Failed to delete vehicle photo: {$path}");
                }
            }
            if ($motorist->motorist_photo) {
                if (!Storage::disk(uploads_disk())->delete($motorist->motorist_photo)) {
                    Log::warning("Failed to delete motorist photo: {$motorist->motorist_photo}");
                }
            }
        }
        $incident->delete();

        return redirect()->route('incidents.index')
            ->with('success', 'Incident deleted.');
    }

    public function destroyMedia(IncidentMedia $media): RedirectResponse
    {
        $this->authorize('deleteMedia', $media->incident);
        $incidentId = $media->incident_id;

        if (!Storage::disk(uploads_disk())->delete($media->file_path)) {
            Log::warning("Failed to delete incident media file: {$media->file_path}");
        }
        $media->delete();

        return redirect()->route('incidents.show', $incidentId)
            ->with('success', 'Media deleted.');
    }

    public function printRecord(Incident $incident): View
    {
        $incident->load(['motorists.violator', 'motorists.vehicle', 'motorists.chargeType', 'motorists.ownerViolator', 'media', 'recorder']);

        return view('incidents.print', compact('incident'));
    }

    /**
     * Resolve all auto-registration for one motorist entry.
     *
     * Rules:
     *   1. Auto-register the DRIVER as a Violator if entered manually.
     *   2. Determine the VEHICLE OWNER (may be the driver, or a different person).
     *   3. Auto-register the owner as a Violator if only a name was provided.
     *   4. Register the vehicle under the OWNER's Violator record.
     *
     * Returns the updated $m array with violator_id, vehicle_owner_violator_id,
     * and vehicle_id filled in.
     */
    private function resolveIncidentMotorist(array $m): array
    {
        // ── Step 1: Auto-register driver ──────────────────────────────────────
        if (empty($m['violator_id']) && !empty($m['motorist_name'])) {
            $m['violator_id'] = $this->autoRegisterDriver($m);
        }

        // ── Step 2: Determine owner Violator ID ───────────────────────────────
        if (!empty($m['vehicle_owner_violator_id'])) {
            // Owner is already a registered violator — nothing to create
            $ownerViolatorId = (int) $m['vehicle_owner_violator_id'];
        } elseif (!empty($m['vehicle_owner_name'])) {
            // Owner entered manually — register them as a Violator (no incident link)
            $ownerViolatorId = $this->autoRegisterOwner($m);
            $m['vehicle_owner_violator_id'] = $ownerViolatorId;
        } else {
            // Driver is also the owner
            $ownerViolatorId = !empty($m['violator_id']) ? (int) $m['violator_id'] : null;
        }

        // ── Step 3: Register vehicle under the OWNER ──────────────────────────
        if ($ownerViolatorId && empty($m['vehicle_id'])) {
            $vehicleId = $this->autoRegisterVehicle($m, $ownerViolatorId);
            if ($vehicleId) {
                $m['vehicle_id'] = $vehicleId;
            }
        }

        return $m;
    }

    /**
     * Find or create a Violator for the DRIVER.
     * Matches by license number to avoid duplicates, fills in missing fields if found.
     */
    private function autoRegisterDriver(array $m): int
    {
        if (!empty($m['motorist_license'])) {
            $existing = Violator::where('license_number', $m['motorist_license'])->first();
            if ($existing) {
                $fill = [];
                if (empty($existing->license_type)        && !empty($m['license_type']))        $fill['license_type']        = $m['license_type'];
                if (empty($existing->license_restriction) && !empty($m['license_restriction'])) $fill['license_restriction']  = is_array($m['license_restriction']) ? implode(',', $m['license_restriction']) : $m['license_restriction'];
                if (!$existing->license_expiry_date       && !empty($m['license_expiry_date'])) $fill['license_expiry_date']  = $m['license_expiry_date'];
                if (empty($existing->contact_number)      && !empty($m['motorist_contact']))    $fill['contact_number']       = $m['motorist_contact'];
                if (empty($existing->temporary_address)   && !empty($m['motorist_address']))    $fill['temporary_address']    = $m['motorist_address'];
                if ($fill) $existing->update($fill);
                return $existing->id;
            }
        }

        $parts     = preg_split('/\s+/', trim($m['motorist_name'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
        $lastName  = count($parts) > 1 ? array_pop($parts) : ($parts[0] ?? '');
        $firstName = implode(' ', $parts);

        return Violator::create([
            'first_name'          => $firstName,
            'last_name'           => $lastName,
            'license_number'      => !empty($m['motorist_license']) ? $m['motorist_license'] : null,
            'license_type'        => $m['license_type'] ?? null,
            'license_restriction' => !empty($m['license_restriction']) ? implode(',', (array) $m['license_restriction']) : null,
            'license_expiry_date' => $m['license_expiry_date'] ?? null,
            'contact_number'      => $m['motorist_contact'] ?? null,
            'temporary_address'   => $m['motorist_address'] ?? null,
        ])->id;
    }

    /**
     * Find or create a Violator for the VEHICLE OWNER (not the driver).
     * The owner has no incident link — they are only associated via their vehicle.
     */
    private function autoRegisterOwner(array $m): int
    {
        $parts     = preg_split('/\s+/', trim($m['vehicle_owner_name'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
        $lastName  = count($parts) > 1 ? array_pop($parts) : ($parts[0] ?? '');
        $firstName = implode(' ', $parts);

        return Violator::create([
            'first_name'     => $firstName,
            'last_name'      => $lastName,
            'contact_number' => $m['vehicle_owner_contact'] ?? null,
        ])->id;
    }

    /**
     * Find or create a Vehicle record under the given OWNER Violator.
     * Returns the vehicle id, or null if no plate number provided.
     */
    private function autoRegisterVehicle(array $m, int $ownerViolatorId): ?int
    {
        if (empty($m['vehicle_plate'])) {
            return null;
        }

        // Plate numbers are globally unique — search across all owners
        $existing = Vehicle::withTrashed()->where('plate_number', $m['vehicle_plate'])->first();

        if ($existing) {
            // Restore soft-deleted vehicle if needed
            if ($existing->trashed()) {
                $existing->restore();
            }
            return $existing->id;
        }

        return Vehicle::create([
            'violator_id'    => $ownerViolatorId,
            'plate_number'   => $m['vehicle_plate'],
            'vehicle_type'   => $m['vehicle_type_manual'] ?? null,
            'make'           => $m['vehicle_make'] ?? null,
            'model'          => $m['vehicle_model'] ?? null,
            'color'          => $m['vehicle_color'] ?? null,
            'or_number'      => $m['vehicle_or_number'] ?? null,
            'cr_number'      => $m['vehicle_cr_number'] ?? null,
            'chassis_number' => $m['vehicle_chassis'] ?? null,
        ])->id;
    }

    private function normalizeDateFilter(mixed $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        $value = trim($value);

        if ($value === '') {
            return '';
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value)->toDateString();
        } catch (\Throwable) {
            return '';
        }
    }

    private function normalizeStatusFilter(mixed $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        return in_array($value, ['under_investigation', 'cleared', 'solved', 'settled'], true)
            ? $value
            : '';
    }
}
