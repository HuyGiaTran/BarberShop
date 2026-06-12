<?php

namespace App\Listeners;

use App\Events\InvoicePaid;
use App\Services\LoyaltyService;

class AddLoyaltyPoints
{
    public function __construct(
        private readonly LoyaltyService $loyaltyService
    ) {
    }

    public function handle(InvoicePaid $event): void
    {
        $this->loyaltyService->awardPointsForInvoice($event->invoice);
    }
}
