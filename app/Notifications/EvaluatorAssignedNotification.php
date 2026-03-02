<?php

namespace App\Notifications;

use App\Models\Evaluation;
use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EvaluatorAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Project $project,
        private Evaluation $evaluation
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Assessment Assignment - ' . $this->project->title)
            ->greeting('Hello ' . $notifiable->name)
            ->line('You have been assigned to evaluate the project: **' . $this->project->title . '**')
            ->line('Role: ' . $this->evaluation->evaluator_role)
            ->line('Rubric: ' . $this->evaluation->rubricTemplate->name)
            ->action('Go to Staff Dashboard', url('/staff'))
            ->line('Please complete your evaluation at your earliest convenience.');
    }
}
