<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentBooked extends Notification implements ShouldQueue
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
        $barberName = $this->appointment->barber->name;
        $serviceName = $this->appointment->service->name;

        return (new MailMessage)
            ->subject('Xác nhận đặt lịch cắt tóc thành công')
            ->greeting('Xin chào ' . $notifiable->name . '!')
            ->line('Cảm ơn bạn đã đặt lịch tại Barber Shop.')
            ->line('Thông tin lịch hẹn của bạn:')
            ->line('- Thợ cắt tóc: ' . $barberName)
            ->line('- Dịch vụ: ' . $serviceName)
            ->line('- Thời gian: ' . $time . ' ngày ' . $date)
            ->action('Xem chi tiết', url('/'))
            ->line('Vui lòng đến đúng giờ để được phục vụ tốt nhất. Hẹn gặp lại bạn!');
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
            'message' => 'Lịch hẹn đã được đặt thành công.',
        ];
    }
}
