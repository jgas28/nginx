<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $status = $request->input('status');

        $query = Attendance::with('user')
            ->orderBy('date', 'desc');

        // Apply search by employee name
        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('fname', 'like', '%' . $search . '%')
                  ->orWhere('lname', 'like', '%' . $search . '%')
                  ->orWhere('employee_code', 'like', '%' . $search . '%');
            });
        }

        // Filter by date range
        if ($dateFrom && $dateTo) {
            $query->whereBetween('date', [
                Carbon::parse($dateFrom)->startOfDay(),
                Carbon::parse($dateTo)->endOfDay(),
            ]);
        }

        // Filter by status
        if ($status) {
            $query->where('status', $status);
        }

        $attendanceRecords = $query->paginate(15);

        // Calculate dashboard stats
        $totalRecords = Attendance::count();
        $presentCount = Attendance::where('status', 'Present')->count();
        $lateCount = Attendance::where('status', 'Late')->count();
        $absentCount = Attendance::where('status', 'Absent')->count();

        // Monthly stats (current month)
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $monthlyPresent = Attendance::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->where('status', 'Present')
            ->count();
        $monthlyLate = Attendance::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->where('status', 'Late')
            ->count();
        $monthlyAbsent = Attendance::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->where('status', 'Absent')
            ->count();

        return view('attendance.index', compact(
            'attendanceRecords',
            'search',
            'dateFrom',
            'dateTo',
            'status',
            'totalRecords',
            'presentCount',
            'lateCount',
            'absentCount',
            'monthlyPresent',
            'monthlyLate',
            'monthlyAbsent'
        ));
    }

    public function create()
    {
        $employees = User::where('status', '!=', 0)->orderBy('fname')->get();
        return view('attendance.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
            'status' => 'required|in:Present,Late,Absent',
            'remarks' => 'nullable|string',
        ]);

        $timeIn = $request->time_in ? Carbon::createFromFormat('H:i', $request->time_in) : null;
        $timeOut = $request->time_out ? Carbon::createFromFormat('H:i', $request->time_out) : null;
        $totalHours = null;

        if ($timeIn && $timeOut) {
            $totalHours = $timeOut->diffInMinutes($timeIn) / 60;
        }

        Attendance::create([
            'user_id' => $request->user_id,
            'date' => $request->date,
            'time_in' => $timeIn,
            'time_out' => $timeOut,
            'total_hours' => $totalHours,
            'status' => $request->status,
            'remarks' => $request->remarks,
        ]);

        return redirect()->route('attendance.index')
            ->with('success', 'Attendance record created successfully.');
    }
}
