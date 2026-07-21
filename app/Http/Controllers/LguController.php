<?php

namespace App\Http\Controllers;

use App\Models\Lgu;
use Illuminate\Http\Request;

class LguController extends Controller
{
    public function index()
    {
        $lgus = Lgu::withCount(['users', 'violations', 'incidents'])->orderBy('name')->get();
        return view('lgus.index', compact('lgus'));
    }

    public function create()
    {
        return view('lgus.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'                => ['required', 'string', 'max:10', 'alpha_dash', 'unique:lgus,code'],
            'name'                => ['required', 'string', 'max:150'],
            'province'            => ['required', 'string', 'max:150'],
            'psgc_city_code'      => ['nullable', 'string', 'max:20', 'unique:lgus,psgc_city_code'],
            'ordinance_reference' => ['nullable', 'string', 'max:255'],
            'treasurer_office'    => ['nullable', 'string', 'max:255'],
        ]);

        $data['code'] = strtoupper($data['code']);

        Lgu::create($data);

        return redirect()->route('lgus.index')->with('success', 'LGU added.');
    }

    public function edit(Lgu $lgu)
    {
        return view('lgus.edit', compact('lgu'));
    }

    public function update(Request $request, Lgu $lgu)
    {
        $data = $request->validate([
            'code'                => ['required', 'string', 'max:10', 'alpha_dash', "unique:lgus,code,{$lgu->id}"],
            'name'                => ['required', 'string', 'max:150'],
            'province'            => ['required', 'string', 'max:150'],
            'psgc_city_code'      => ['nullable', 'string', 'max:20', "unique:lgus,psgc_city_code,{$lgu->id}"],
            'ordinance_reference' => ['nullable', 'string', 'max:255'],
            'treasurer_office'    => ['nullable', 'string', 'max:255'],
        ]);

        $data['code'] = strtoupper($data['code']);

        $lgu->update($data);

        return redirect()->route('lgus.index')->with('success', 'LGU updated.');
    }

    public function destroy(Lgu $lgu)
    {
        if ($lgu->users()->exists() || $lgu->violations()->exists() || $lgu->incidents()->exists()) {
            return back()->with('error', 'Cannot delete an LGU that has linked users or records.');
        }

        $lgu->delete();

        return redirect()->route('lgus.index')->with('success', 'LGU deleted.');
    }
}
