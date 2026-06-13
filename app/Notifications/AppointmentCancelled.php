<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentCancelled extends Notification implements ShouldQueue
{
    use Queueable;

    public Appointment $appointment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $date = $this->appointment->appointment_date->format('d/m/Y');
        $time = $this->appointment->appointment_time;

        return (new MailMessage)
            ->subject('Thông báo hủy lịch hẹn')
            ->greeting('Xin chào ' . $notifiable->name . '!')
            ->line('Lịch hẹn cắt tóc của bạn vào lúc ' . $time . ' ngày ' . $date . ' đã bị hủy.')
            ->line('Nếu bạn cần hỗ trợ hoặc muốn đặt lịch mới, vui lòng liên hệ với chúng tôi.')
            ->action('Đặt lịch mới', url('/'))
            ->line('Cảm ơn bạn đã quan tâm đến dịch vụ của Barber Shop.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'appointment_id' => $this->appointment->id,
            'message' => 'Lịch hẹn đã bị hủy.',
        ];
    }
}
