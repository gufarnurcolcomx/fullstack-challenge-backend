<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Departement;
use Illuminate\Support\Str;

class DepartementController extends Controller
{
    public function index()
    {
        $departements = Departement::where('user_id', auth()->id())->get();
        return response()->json($departements, 200);
    }

    public function show($id)
    {
        $departement = Departement::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$departement) {
            return response()->json(['message' => 'Departement not found'], 404);
        }

        return response()->json($departement);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'departement_name' => 'required|string|max:255',
            'max_clock_in_time' => 'required|date_format:H:i:s',
            'max_clock_out_time' => 'required|date_format:H:i:s',
        ]);

        $departement = Departement::create([
            'id' => (string) Str::uuid(),
            'user_id' => auth()->id(),
            'departement_name' => $validated['departement_name'],
            'max_clock_in_time' => $validated['max_clock_in_time'],
            'max_clock_out_time' => $validated['max_clock_out_time'],
        ]);

        return response()->json($departement, 201);
    }

    public function update(Request $request, $id)
    {
        $departement = Departement::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$departement) {
            return response()->json(['message' => 'Departement not found'], 404);
        }

        $departement->update($request->only([
            'departement_name',
            'max_clock_in_time',
            'max_clock_out_time'
        ]));

        return response()->json($departement);
    }

    public function destroy($id)
    {
        $departement = Departement::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$departement) {
            return response()->json(['message' => 'Departement not found'], 404);
        }

        $departement->delete();

        return response()->json(['message' => 'Departement deleted']);
    }
}
