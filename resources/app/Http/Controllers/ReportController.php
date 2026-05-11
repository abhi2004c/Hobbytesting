<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ReportStatus;
use App\Http\Requests\CreateReportRequest;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Store a new report (works for both web and API via Accept header).
     */
    public function store(CreateReportRequest $request): JsonResponse|RedirectResponse
    {
        $reportableType = $request->reportableType();
        $reportableId   = $request->validated('reportable_id');

        // Prevent duplicate reports from the same user on the same content
        $existing = Report::query()
            ->where('reporter_id', $request->user()->id)
            ->where('reportable_type', $reportableType)
            ->where('reportable_id', $reportableId)
            ->whereIn('status', [ReportStatus::Pending->value, ReportStatus::Reviewed->value])
            ->exists();

        if ($existing) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'You have already reported this content.'], 422);
            }
            return back()->with('error', 'You have already reported this content.');
        }

        // Verify the reportable actually exists
        $model = $reportableType::find($reportableId);
        if (! $model) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Content not found.'], 404);
            }
            return back()->with('error', 'Content not found.');
        }

        $report = Report::create([
            'reporter_id'     => $request->user()->id,
            'reportable_type' => $reportableType,
            'reportable_id'   => $reportableId,
            'reason'          => $request->validated('reason'),
            'description'     => $request->validated('description'),
            'status'          => ReportStatus::Pending,
        ]);

        // Notify admins via Filament notification if available
        $this->notifyAdmins($report);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Thank you for your report. Our moderation team will review it.',
                'report_id' => $report->id,
            ], 201);
        }

        return back()->with('success', 'Thank you for your report. Our moderation team will review it.');
    }

    /**
     * Send Filament notification to all platform admins.
     */
    private function notifyAdmins(Report $report): void
    {
        try {
            $admins = \App\Models\User::role('platform_admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\Moderation\NewReportNotification($report));
            }
        } catch (\Throwable) {
            // Silently fail — admin notifications are non-critical
        }
    }
}
