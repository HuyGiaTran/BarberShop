<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public Invoice $invoice;

    /**
     * Create a new notification instance.
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
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
        $amount = number_format($this->invoice->total_amount, 0, ',', '.') . ' VNĐ';

        return (new MailMessage)
            ->subject('Hóa đơn thanh toán - Barber Shop')
            ->greeting('Xin chào ' . $notifiable->name . '!')
            ->line('Cảm ơn bạn đã sử dụng dịch vụ của Barber Shop.')
            ->line('Hóa đơn thanh toán của bạn đã được tạo.')
            ->line('Mã hóa đơn: #' . $this->invoice->id)
            ->line('Tổng tiền: ' . $amount)
            ->line('Trạng thái: ' . ($this->invoice->payment_status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán'))
            ->line('Cảm ơn bạn đã ủng hộ chúng tôi!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'invoice_id' => $this->invoice->id,
            'message' => 'Hóa đơn mới đã được tạo.',
        ];
    }
}
