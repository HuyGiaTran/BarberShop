<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\LoyaltyService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoyaltyController extends Controller
{
    public function __construct(
        private readonly LoyaltyService $loyaltyService
    ) {
    }

    public function index(): View
    {
        $user = Auth::user();
        abort_unless($user?->role === 'customer', 403, 'Chức năng này chỉ dành cho khách hàng.');

        $summary = $this->loyaltyService->summaryForUser($user);

        return view('customer.loyalty.index', [
            'summary' => $summary,
            'user' => $user,
        ]);
    }
}