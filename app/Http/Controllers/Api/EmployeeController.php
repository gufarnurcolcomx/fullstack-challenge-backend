<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with('departement')
            ->where('user_id', auth()->id())
            ->get();

        return response()->json($employees);
    }

    public function show($id)
    {
        $employee = Employee::with('departement')
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        return response()->json($employee);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|string|unique:employees,employee_id',
            'departement_id' => 'required|exists:departements,id',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
        ]);

        $employee = Employee::create([
            'id' => (string) Str::uuid(),
            'user_id' => auth()->id(),
            'employee_id' => $validated['employee_id'],
            'departement_id' => $validated['departement_id'],
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
        ]);

        return response()->json(['message' => 'Employee created', 'data' => $employee], 201);
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $request->validate([
            'departement_id' => 'required|exists:departements,id',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
        ]);

        $employee->update($request->only(['departement_id', 'name', 'address']));

        return response()->json(['message' => 'Employee updated', 'data' => $employee]);
    }

    public function destroy($id)
    {
        $employee = Employee::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $employee->delete();

        return response()->json(['message' => 'Employee deleted']);
    }
}
