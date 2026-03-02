<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MarksFinalisedNotification extends Notification implements ShouldQueue
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
            ->subject('Your Marks Are Available - ' . $this->project->title)
            ->greeting('Hello ' . $notifiable->name)
            ->line('Your consolidated marks for the project **' . $this->project->title . '** have been finalized.')
            ->action('View My Marks', url('/student/my-marks'))
            ->line('You can now view your results in the Student Portal.');
    }
}
