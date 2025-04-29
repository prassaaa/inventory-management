<?php

namespace App\Http\Controllers;

use App\Models\AccountPayable;
use App\Models\AccountReceivable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    /**
     * Record payment for account payable
     */
    public function recordPayment(Request $request)
    {
        $validated = $request->validate([
            'payable_id' => 'required|exists:account_payables,id',
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $payable = AccountPayable::findOrFail($validated['payable_id']);

            // Make sure payment amount doesn't exceed remaining amount
            $remainingAmount = $payable->amount - $payable->paid_amount;
            if ($validated['payment_amount'] > $remainingAmount) {
                return redirect()->back()->with('error', 'Jumlah pembayaran melebihi sisa hutang.');
            }

            // Update paid amount
            $newPaidAmount = $payable->paid_amount + $validated['payment_amount'];
            $newStatus = $newPaidAmount >= $payable->amount ? 'paid' :
                         ($newPaidAmount > 0 ? 'partially_paid' : 'unpaid');

            $payable->update([
                'paid_amount' => $newPaidAmount,
                'status' => $newStatus,
                'payment_date' => $validated['payment_date'],
                'notes' => $payable->notes . "\n" . date('d/m/Y') . ": Pembayaran Rp " .
                          number_format($validated['payment_amount'], 0, ',', '.') .
                          ($validated['payment_notes'] ? " - " . $validated['payment_notes'] : ""),
                'updated_by' => Auth::id(),
            ]);

            // Create financial journal entry (if needed)
            // ...

            DB::commit();

            return redirect()->route('reports.payables')->with('success', 'Pembayaran hutang berhasil dicatat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Record payment for account receivable
     */
    public function recordReceivablePayment(Request $request)
    {
        $validated = $request->validate([
            'receivable_id' => 'required|exists:account_receivables,id',
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $receivable = AccountReceivable::findOrFail($validated['receivable_id']);

            // Make sure payment amount doesn't exceed remaining amount
            $remainingAmount = $receivable->amount - $receivable->paid_amount;
            if ($validated['payment_amount'] > $remainingAmount) {
                return redirect()->back()->with('error', 'Jumlah pembayaran melebihi sisa piutang.');
            }

            // Update paid amount
            $newPaidAmount = $receivable->paid_amount + $validated['payment_amount'];
            $newStatus = $newPaidAmount >= $receivable->amount ? 'paid' :
                         ($newPaidAmount > 0 ? 'partially_paid' : 'unpaid');

            $receivable->update([
                'paid_amount' => $newPaidAmount,
                'status' => $newStatus,
                'payment_date' => $validated['payment_date'],
                'notes' => $receivable->notes . "\n" . date('d/m/Y') . ": Penerimaan Rp " .
                          number_format($validated['payment_amount'], 0, ',', '.') .
                          ($validated['payment_notes'] ? " - " . $validated['payment_notes'] : ""),
                'updated_by' => Auth::id(),
            ]);

            // Create financial journal entry (if needed)
            // ...

            DB::commit();

            return redirect()->route('reports.receivables')->with('success', 'Penerimaan pembayaran piutang berhasil dicatat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
