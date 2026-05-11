<?php

declare(strict_types=1);

namespace App\Notifications\Moderation;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewReportNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Report $report,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'report_id'       => $this->report->id,
            'reportable_type' => class_basename($this->report->reportable_type),
            'reportable_id'   => $this->report->reportable_id,
            'reason'          => $this->report->reason,
            'reporter_name'   => $this->report->reporter?->name ?? 'Unknown',
            'message'         => "New {$this->report->reason} report filed on a " . class_basename($this->report->reportable_type),
            'action_url'      => '/admin/reports/' . $this->report->id,
        ];
    }
}
