<?php

namespace App\Notifications;

use App\Models\Evaluation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EvaluationUnlockedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Evaluation $evaluation
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $unlockedBy = $this->evaluation->unlockedByUser;

        return (new MailMessage)
            ->subject('Evaluation Unlocked - ' . $this->evaluation->project->title)
            ->greeting('Hello ' . $notifiable->name)
            ->line('Your submitted evaluation for the project **' . $this->evaluation->project->title . '** has been unlocked for editing.')
            ->line('Rubric: ' . $this->evaluation->rubricTemplate->name)
            ->line('Unlocked by: ' . ($unlockedBy?->name ?? 'Coordinator'))
            ->action('Edit Evaluation', url('/staff'))
            ->line('Please review and resubmit your evaluation.');
    }
}
