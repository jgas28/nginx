<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 25);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;

        $logs = $this->filteredQuery($request)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends($request->query());

        $users = User::orderBy('fname')->orderBy('lname')->get(['id', 'fname', 'lname', 'employee_code']);
        $events = AuditLog::query()->distinct()->orderBy('event')->pluck('event');
        $models = AuditLog::query()->whereNotNull('auditable_type')->distinct()->orderBy('auditable_type')->pluck('auditable_type');

        if ($request->ajax()) {
            return response()->json([
                'html' => view('audit_logs.partials.table', compact('logs'))->render(),
            ]);
        }

        return view('audit_logs.index', compact('logs', 'users', 'events', 'models'));
    }

    public function exportExcel(Request $request)
    {
        $logs = $this->filteredQuery($request)->orderBy('created_at', 'desc')->get();
        $filename = 'audit-logs-' . now()->format('Y-m-d') . '.csv';

        $callback = function () use ($logs) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Date', 'Actor', 'Event', 'Model', 'Record ID', 'Description', 'IP Address']);

            foreach ($logs as $log) {
                fputcsv($out, [
                    optional($log->created_at)->format('M d, Y h:i A'),
                    $log->actor_name ?? '',
                    $log->event,
                    $log->auditable_type ? class_basename($log->auditable_type) : '',
                    $log->auditable_id ?? '',
                    $log->description ?? '',
                    $log->ip_address ?? '',
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '256M');
        set_time_limit(120);

        $logs = $this->filteredQuery($request)->orderBy('created_at', 'desc')->get();
        $filename = 'audit-logs-' . now()->format('Y-m-d') . '.pdf';

        $pdf = Pdf::loadView('audit_logs.export-pdf', compact('logs'))
            ->setPaper('a4', 'landscape')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isFontSubsettingEnabled', true)
            ->setOption('defaultFont', 'dejavu sans');

        return $pdf->download($filename);
    }

    private function filteredQuery(Request $request)
    {
        $query = AuditLog::query();

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('auditable_type')) {
            $query->where('auditable_type', $request->auditable_type);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('actor_name', 'like', "%{$search}%");
            });
        }

        return $query;
    }
}
