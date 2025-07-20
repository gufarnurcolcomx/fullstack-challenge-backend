<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\AttendanceHistory;
use Illuminate\Validation\Rule;
use Carbon\Carbon;


class AuthSistemController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'nama_perusahaan' => 'nullable|string|max:50',
            'url_foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $path = null;
        if ($request->hasFile('url_foto')) {
            $file = $request->file('url_foto');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = 'profile_photos/' . $filename;
            $file->move(public_path('profile_photos'), $filename);
        }

        $user = User::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->input('password')),
            'nama_perusahaan' => $request->nama_perusahaan,
            'url_foto' => $path,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil',
            'user' => [
                'nama' => $user->nama,
                'email' => $user->email,
                'url_foto' => $user->url_foto,
            ],
            'token' => $token
        ], 201);
    }
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah.'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }

    public function Dashboard()
    {
        $today = Carbon::today('Asia/Jakarta');
        $userId = auth()->id();

        $totalEmployees = Employee::where('user_id', $userId)->count();

        $attendanceToday = Attendance::where('user_id', $userId)
            ->whereDate('clock_in', $today)
            ->count();

        $completedTodayCount = Attendance::where('user_id', $userId)
            ->whereDate('clock_in', $today)
            ->whereDate('clock_out', $today)
            ->count();

        $attendanceList = Attendance::with('employee.departement')
            ->where('user_id', $userId)
            ->whereDate('clock_in', $today)
            ->orderBy('clock_in', 'asc')
            ->get();

        $completedToday = Attendance::with('employee.departement')
            ->where('user_id', $userId)
            ->whereDate('clock_in', $today)
            ->whereDate('clock_out', $today)
            ->orderBy('clock_in', 'asc')
            ->get();

        $recentHistories = AttendanceHistory::with([
            'employee.departement', 
            'attendance'
        ])
            ->where('user_id', $userId)
            ->orderBy('date_attendance', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($history) {
                return [
                    'employee_name' => $history->employee->name,
                    'departement' => $history->employee->departement->departement_name ?? null,
                    'attendance_type' => $history->attendance_type == 0 ? 'Check-in' : 'Check-out',
                    'date_attendance' => $history->date_attendance,
                    'description' => $history->description,
                ];
            });

        $allHistories = AttendanceHistory::with([
            'employee.departement', 
            'attendance'
        ])
            ->where('user_id', $userId)
            ->orderBy('date_attendance', 'desc')
            ->get()
            ->map(function ($history) {
                return [
                    'employee_name' => $history->employee->name,
                    'departement' => $history->employee->departement->departement_name ?? null,
                    'attendance_type' => $history->attendance_type == 0 ? 'Check-in' : 'Check-out',
                    'date_attendance' => $history->date_attendance,
                    'description' => $history->description,
                ];
            });

        return response()->json([
            'total_employees' => $totalEmployees,
            'attendance_today' => $attendanceToday,
            'completed_today_count' => $completedTodayCount,
            'attendance_list' => $attendanceList,
            'completed_today' => $completedToday,
            'recent_histories' => $recentHistories,
            'all_histories' => $allHistories,
        ]);
    }


    public function Profile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'nama' => $user->nama,
                'email' => $user->email,
                'nama_perusahaan' => $user->nama_perusahaan,
                'url_foto' => $user->url_foto ? url($user->url_foto) : null,
            ]
        ]);
    }

    public function ProfileUpdate(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'nama_perusahaan' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6|confirmed',
            'url_foto' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('url_foto')) {
            $oldPath = public_path($user->url_foto);
            if ($user->url_foto && file_exists($oldPath) && is_file($oldPath)) {
                @unlink($oldPath);
            }

            $file = $request->file('url_foto');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = 'profile_photos/' . $filename;

            $file->move(public_path('profile_photos'), $filename);
            $validated['url_foto'] = $path;
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'user' => [
                'id' => $user->id,
                'nama' => $user->nama,
                'email' => $user->email,
                'nama_perusahaan' => $user->nama_perusahaan,
                'url_foto' => $user->url_foto ? asset($user->url_foto) : null,
            ]
        ]);
    }
}
