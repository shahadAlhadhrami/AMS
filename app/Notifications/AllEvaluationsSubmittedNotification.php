<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AllEvaluationsSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Project $project
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('All Evaluations Submitted - ' . $this->project->title)
            ->greeting('Hello ' . $notifiable->name)
            ->line('All evaluations for the project **' . $this->project->title . '** have been submitted.')
            ->line('Consolidated marks have been automatically calculated.')
            ->action('View Consolidated Marks', url('/admin/consolidated-marks'))
            ->line('You can review and override marks if needed.');
    }
}
