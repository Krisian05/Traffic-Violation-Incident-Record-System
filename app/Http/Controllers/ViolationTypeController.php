<?php

namespace App\Http\Controllers;

use App\Models\Lgu;
use App\Models\ViolationType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ViolationTypeController extends Controller
{
    private function checkAccess(ViolationType $violationType): void
    {
        $user = auth()->user();
        if (!$user) {
            abort(401);
        }

        if ($user->isSuperAdmin() || $user->isProvinceAdmin()) {
            return;
        }

        if ($user->isLguAdmin() && (int) $violationType->lgu_id === (int) $user->lgu_id) {
            return;
        }

        abort(403, 'Forbidden. You may only manage violation types for your assigned LGU.');
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $lgus = Lgu::orderBy('name')->get();

        $query = ViolationType::with(['lgu'])->withCount('violations');

        if ($user->isSuperAdmin() || $user->isProvinceAdmin()) {
            $selectedLgu = $request->input('lgu_id');
            if ($selectedLgu === 'global') {
                $query->whereNull('lgu_id');
            } elseif ($selectedLgu && is_numeric($selectedLgu)) {
                $query->where('lgu_id', (int) $selectedLgu);
            }
        } else {
            // LGU Admin: scoped to their LGU (and global fallback types)
            $query->forLgu($user->lgu_id);
        }

        $types = $query->orderBy('name')->get();
        return view('violation-types.index', compact('types', 'lgus'));
    }

    public function create()
    {
        $lgus = Lgu::orderBy('name')->get();
        return view('violation-types.create', compact('lgus'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $targetLguId = ($user->isSuperAdmin() || $user->isProvinceAdmin())
            ? $request->input('lgu_id')
            : $user->lgu_id;

        if ($targetLguId === '') {
            $targetLguId = null;
        }

        $data = $request->validate([
            'name'                => [
                'required',
                'string',
                'max:150',
                Rule::unique('violation_types')->where(fn($q) => $q->where('lgu_id', $targetLguId)),
            ],
            'code'                => ['nullable', 'string', 'max:50'],
            'description'         => ['nullable', 'string', 'max:500'],
            'fine_amount'         => ['nullable', 'numeric', 'min:0'],
            'late_penalty_amount' => ['nullable', 'numeric', 'min:0'],
            'points'              => ['nullable', 'integer', 'min:0'],
            'lgu_id'              => ['nullable', 'exists:lgus,id'],
        ]);

        $data['lgu_id'] = $targetLguId;

        ViolationType::create($data);

        return redirect()->route('violation-types.index')
            ->with('success', 'Violation type added successfully.');
    }

    public function edit(ViolationType $violationType)
    {
        $this->checkAccess($violationType);
        $lgus = Lgu::orderBy('name')->get();
        return view('violation-types.edit', compact('violationType', 'lgus'));
    }

    public function update(Request $request, ViolationType $violationType)
    {
        $this->checkAccess($violationType);
        $user = auth()->user();

        $targetLguId = ($user->isSuperAdmin() || $user->isProvinceAdmin())
            ? $request->input('lgu_id', $violationType->lgu_id)
            : $user->lgu_id;

        if ($targetLguId === '') {
            $targetLguId = null;
        }

        $data = $request->validate([
            'name'                => [
                'required',
                'string',
                'max:150',
                Rule::unique('violation_types')->ignore($violationType->id)->where(fn($q) => $q->where('lgu_id', $targetLguId)),
            ],
            'code'                => ['nullable', 'string', 'max:50'],
            'description'         => ['nullable', 'string', 'max:500'],
            'fine_amount'         => ['nullable', 'numeric', 'min:0'],
            'late_penalty_amount' => ['nullable', 'numeric', 'min:0'],
            'points'              => ['nullable', 'integer', 'min:0'],
            'lgu_id'              => ['nullable', 'exists:lgus,id'],
        ]);

        $data['lgu_id'] = $targetLguId;

        $violationType->update($data);

        return redirect()->route('violation-types.index')
            ->with('success', 'Violation type updated successfully.');
    }

    public function destroy(ViolationType $violationType)
    {
        $this->checkAccess($violationType);

        if ($violationType->violations()->exists()) {
            return back()->with('error', 'Cannot delete a violation type that has existing violation records.');
        }

        $violationType->delete();

        return redirect()->route('violation-types.index')
            ->with('success', 'Violation type deleted successfully.');
    }
}
