<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\CreateReportRequest;
use App\Enums\ReportStatus;
use App\Models\Report;
use Illuminate\Http\JsonResponse;

class ReportController extends BaseApiController
{
    /**
     * POST /api/v1/reports
     */
    public function store(CreateReportRequest $request): JsonResponse
    {
        $reportableType = $request->reportableType();
        $reportableId   = $request->validated('reportable_id');

        $existing = Report::query()
            ->where('reporter_id', $request->user()->id)
            ->where('reportable_type', $reportableType)
            ->where('reportable_id', $reportableId)
            ->whereIn('status', [ReportStatus::Pending->value, ReportStatus::Reviewed->value])
            ->exists();

        if ($existing) {
            return $this->errorResponse('You have already reported this content.', 422);
        }

        $model = $reportableType::find($reportableId);
        if (! $model) {
            return $this->errorResponse('Content not found.', 404);
        }

        $report = Report::create([
            'reporter_id'     => $request->user()->id,
            'reportable_type' => $reportableType,
            'reportable_id'   => $reportableId,
            'reason'          => $request->validated('reason'),
            'description'     => $request->validated('description'),
            'status'          => ReportStatus::Pending,
        ]);

        return $this->successResponse(
            ['report_id' => $report->id],
            'Report submitted. Our team will review it.',
            201,
        );
    }
}
