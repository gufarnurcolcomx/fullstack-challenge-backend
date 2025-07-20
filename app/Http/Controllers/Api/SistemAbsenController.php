<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\AttendanceHistory;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SistemAbsenController extends Controller
{
    public function searchPerusahaan(Request $request)
    {
        $search = $request->query('q');

        $users = User::where('nama_perusahaan', 'like', '%' . $search . '%')->get();

        return response()->json($users);
    }

    public function searchEmployee(Request $request)
    {
        $request->validate([
            'nama_perusahaan' => 'required|string',
            'q' => 'nullable|string'
        ]);

        $user = User::where('nama_perusahaan', $request->nama_perusahaan)->first();

        if (!$user) {
            return response()->json(['message' => 'Perusahaan tidak ditemukan'], 404);
        }

        $search = $request->query('q');

        $employees = Employee::with('departement')
            ->where('user_id', $user->id)
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                      ->orWhere('employee_id', 'like', '%' . $search . '%');
            })
            ->get();

        return response()->json($employees);
    }

    public function clockIn(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|string|exists:employees,employee_id',
            'user_id' => 'required|exists:users,id'
        ]);

        $employee = Employee::where('employee_id', $request->employee_id)
            ->where('user_id', $request->user_id)
            ->first();

        if (!$employee) {
            return response()->json(['message' => 'Employee tidak ditemukan'], 404);
        }

        $today = Carbon::today();

        $attendance = Attendance::where('employee_id', $employee->employee_id)
            ->where('user_id', $request->user_id)
            ->whereDate('created_at', $today)
            ->first();

        if ($attendance && $attendance->clock_in) {
            return response()->json(['message' => 'Sudah melakukan absen masuk.'], 400);
        }

        if (!$attendance) {
            $attendance = new Attendance();
            $attendance->id = Str::uuid()->toString();
            $attendance->attendance_id = 'ABSEN-' . uniqid();
            $attendance->employee_id = $employee->employee_id;
            $attendance->user_id = $request->user_id;
        }

        $now = Carbon::now('Asia/Jakarta');
        $departement = $employee->departement;
        $maxClockIn = Carbon::parse($departement->max_clock_in_time, 'Asia/Jakarta');

        $status = $now->gt($maxClockIn) ? 'Terlambat' : 'Tepat Waktu';

        $attendance->clock_in = $now;
        $attendance->save();

        AttendanceHistory::create([
            'id' => Str::uuid()->toString(),
            'employee_id' => $employee->employee_id,
            'attendance_id' => $attendance->attendance_id,
            'date_attendance' => $now,
            'attendance_type' => 0,
            'description' => $status,
            'user_id' => $request->user_id
        ]);

        return response()->json(['message' => 'Absen masuk berhasil', 'status' => $status]);
    }

    public function clockOut(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|string|exists:employees,employee_id',
            'user_id' => 'required|exists:users,id'
        ]);

        $employee = Employee::where('employee_id', $request->employee_id)
            ->where('user_id', $request->user_id)
            ->first();

        if (!$employee) {
            return response()->json(['message' => 'Employee tidak ditemukan'], 404);
        }

        $today = Carbon::today();

        $attendance = Attendance::where('employee_id', $employee->employee_id)
            ->where('user_id', $request->user_id)
            ->whereDate('created_at', $today)
            ->first();

        if (!$attendance || !$attendance->clock_in) {
            return response()->json(['message' => 'Belum melakukan absen masuk.'], 400);
        }

        if ($attendance->clock_out) {
            return response()->json(['message' => 'Sudah melakukan absen keluar.'], 400);
        }

        $now = Carbon::now('Asia/Jakarta');
        $departement = $employee->departement;
        $maxClockOut = Carbon::parse($departement->max_clock_out_time, 'Asia/Jakarta');

        $status = $now->lt($maxClockOut) ? 'Pulang Awal' : 'Tepat Waktu';

        $attendance->clock_out = $now;
        $attendance->save();

        AttendanceHistory::create([
            'id' => Str::uuid()->toString(),
            'employee_id' => $employee->employee_id,
            'attendance_id' => $attendance->attendance_id,
            'date_attendance' => $now,
            'attendance_type' => 1,
            'description' => $status,
            'user_id' => $request->user_id
        ]);

        return response()->json(['message' => 'Absen keluar berhasil', 'status' => $status]);
    }

    public function getAttendanceStatus(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|string|exists:employees,employee_id',
            'user_id' => 'required|exists:users,id',
        ]);

        $today = Carbon::today()->toDateString();

        $masuk = AttendanceHistory::where('employee_id', $request->employee_id)
            ->where('user_id', $request->user_id)
            ->whereDate('date_attendance', $today)
            ->where('attendance_type', 0)
            ->orderBy('date_attendance', 'asc')
            ->first();

        $keluar = AttendanceHistory::where('employee_id', $request->employee_id)
            ->where('user_id', $request->user_id)
            ->whereDate('date_attendance', $today)
            ->where('attendance_type', 1)
            ->orderBy('date_attendance', 'desc')
            ->first();

        if (!$masuk && !$keluar) {
            return response()->json([
                'status' => 'belum_absen',
                'message' => 'Belum melakukan absen hari ini.'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'masuk' => $masuk ? Carbon::parse($masuk->date_attendance)->format('H:i:s') : null,
                'keluar' => $keluar ? Carbon::parse($keluar->date_attendance)->format('H:i:s') : null
            ]
        ]);
    }
}
