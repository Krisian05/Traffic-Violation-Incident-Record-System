<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use App\Models\Violation;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OnlinePaymentController extends Controller
{
    /**
     * Public online payment landing search page.
     */
    public function index(Request $request)
    {
        $query = trim($request->input('search', ''));
        $violations = collect();

        if ($query !== '') {
            $violations = Violation::with(['violator', 'violationType', 'lgu', 'payments'])
                ->where(function ($q) use ($query) {
                    $q->where('ticket_number', 'like', "%{$query}%")
                      ->orWhereHas('violator', function ($vq) use ($query) {
                          $vq->where('license_number', 'like', "%{$query}%")
                             ->orWhere('first_name', 'like', "%{$query}%")
                             ->orWhere('last_name', 'like', "%{$query}%");
                      })
                      ->orWhere('vehicle_plate', 'like', "%{$query}%")
                      ->orWhereHas('vehicle', function ($vq) use ($query) {
                          $vq->where('plate_number', 'like', "%{$query}%");
                      });
                })
                ->latest('date_of_violation')
                ->take(10)
                ->get();
        }

        return view('online-payment.index', [
            'searchQuery' => $query,
            'violations'  => $violations,
        ]);
    }

    /**
     * Process search form submit.
     */
    public function search(Request $request)
    {
        $request->validate([
            'search' => 'required|string|max:100',
        ]);

        $query = trim($request->input('search'));

        // If exact or partial ticket match exists, redirect straight to checkout
        $exactViolation = Violation::where('ticket_number', $query)
            ->orWhere('ticket_number', 'like', "%{$query}%")
            ->first();

        if ($exactViolation) {
            return redirect()->route('online-payment.checkout', $exactViolation->ticket_number);
        }

        return redirect()->route('online-payment.index', ['search' => $query]);
    }

    /**
     * Mobile checkout page for a specific citation ticket.
     */
    public function checkout(string $ticket)
    {
        // Support ticket number search (case-insensitive & space/hyphen clean)
        $cleanTicket = trim($ticket);
        
        $violationQuery = Violation::with(['violator', 'violationType', 'lgu', 'payments', 'vehicle'])
            ->where('ticket_number', $cleanTicket)
            ->orWhere('ticket_number', 'like', "%{$cleanTicket}%");

        // Safely check integer ID for PostgreSQL compatibility without throwing type 22P02 exception
        if (is_numeric($cleanTicket)) {
            $violationQuery->orWhere('id', (int) $cleanTicket);
        }

        $violation = $violationQuery->first();

        if (!$violation) {
            return redirect()->route('online-payment.index', ['search' => $cleanTicket])
                ->with('error', "Citation ticket '{$cleanTicket}' was not found. Please check your ticket number or search by License Number or Plate Number.");
        }

        $lgu = $violation->lgu ?: $violation->violator?->lgu;

        return view('online-payment.checkout', [
            'violation'       => $violation,
            'lgu'             => $lgu,
            'baseFine'        => (float) ($violation->violationType?->fine_amount ?? 0),
            'latePenalty'     => $violation->latePenaltyAmount(),
            'totalDue'        => $violation->totalAmountDue(),
            'totalPaid'       => $violation->totalAmountPaid(),
            'balanceRemaining'=> $violation->balanceRemaining(),
            'isOverdue'       => $violation->isOverdue(),
        ]);
    }

    /**
     * Redirect to full-screen GCash payment portal page.
     */
    public function gcashGateway(string $ticket)
    {
        $cleanTicket = trim($ticket);
        $violationQuery = Violation::with(['violator', 'violationType', 'lgu', 'payments', 'vehicle'])
            ->where('ticket_number', $cleanTicket)
            ->orWhere('ticket_number', 'like', "%{$cleanTicket}%");

        if (is_numeric($cleanTicket)) {
            $violationQuery->orWhere('id', (int) $cleanTicket);
        }

        $violation = $violationQuery->first();
        if (!$violation) {
            return redirect()->route('online-payment.index')->with('error', 'Citation ticket not found.');
        }

        return view('online-payment.gcash', [
            'violation'       => $violation,
            'lgu'             => $violation->lgu ?: $violation->violator?->lgu,
            'balanceRemaining'=> $violation->balanceRemaining(),
        ]);
    }

    /**
     * Redirect to full-screen Maya payment portal page.
     */
    public function mayaGateway(string $ticket)
    {
        $cleanTicket = trim($ticket);
        $violationQuery = Violation::with(['violator', 'violationType', 'lgu', 'payments', 'vehicle'])
            ->where('ticket_number', $cleanTicket)
            ->orWhere('ticket_number', 'like', "%{$cleanTicket}%");

        if (is_numeric($cleanTicket)) {
            $violationQuery->orWhere('id', (int) $cleanTicket);
        }

        $violation = $violationQuery->first();
        if (!$violation) {
            return redirect()->route('online-payment.index')->with('error', 'Citation ticket not found.');
        }

        return view('online-payment.maya', [
            'violation'       => $violation,
            'lgu'             => $violation->lgu ?: $violation->violator?->lgu,
            'balanceRemaining'=> $violation->balanceRemaining(),
        ]);
    }

    /**
     * Process the online self-service payment checkout.
     */
    public function process(Request $request, Violation $violation)
    {
        $balance = $violation->balanceRemaining();
        if ($balance <= 0 || $violation->isSettled()) {
            return redirect()->route('online-payment.checkout', $violation->ticket_number)
                ->with('error', 'This violation has already been fully settled.');
        }

        $validated = $request->validate([
            'payment_method'   => 'required|in:gcash,maya,card',
            'mobile_number'    => 'nullable|string|max:20',
            'card_number'      => 'nullable|string|max:30',
            'transaction_ref'  => 'nullable|string|max:50',
        ]);

        $method = $validated['payment_method'];

        // Generate unique standard Official Receipt Number for online payment
        $dateStr = now()->format('Ymd');
        $randomSuffix = strtoupper(Str::random(5));
        $orNumber = "OR-ONL-{$dateStr}-{$randomSuffix}";

        // Ensure OR uniqueness
        while (Payment::where('or_number', $orNumber)->exists()) {
            $randomSuffix = strtoupper(Str::random(5));
            $orNumber = "OR-ONL-{$dateStr}-{$randomSuffix}";
        }

        $methodLabel = match ($method) {
            'gcash' => 'GCash Express',
            'maya'  => 'Maya Wallet',
            'card'  => 'Credit/Debit Card',
            default => 'Online Gateway',
        };

        // System collector user for audit logging
        $collector = User::where('role', 'admin')->first() ?: User::first();

        $paymentService = app(PaymentService::class);
        $payment = $paymentService->recordPayment(
            $violation,
            [
                'or_number'      => $orNumber,
                'cashier_name'   => "Online Portal ({$methodLabel})",
                'payment_method' => $method,
                'amount_paid'    => $balance,
                'paid_at'        => now(),
            ],
            $collector
        );

        return redirect()->route('online-payment.receipt', $payment)
            ->with('success', 'Payment processed successfully! Official Receipt generated.');
    }

    /**
     * Display digital E-Receipt page.
     */
    public function receipt(Payment $payment)
    {
        $payment->load(['violation.violator', 'violation.violationType', 'violation.lgu']);
        $violation = $payment->violation;

        return view('online-payment.receipt', [
            'payment'   => $payment,
            'violation' => $violation,
            'lgu'       => $violation?->lgu ?: $violation?->violator?->lgu,
        ]);
    }
}
