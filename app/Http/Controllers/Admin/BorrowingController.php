<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Borrowing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BorrowingController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,returned,overdue'],
        ]);

        $borrowings = Borrowing::with(['user', 'book'])
            ->when($validated['q'] ?? null, function ($q, $term) {
                $q->where(function ($x) use ($term) {
                    $x->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"))
                      ->orWhereHas('book', fn ($b) => $b->where('title', 'like', "%{$term}%"));
                });
            })
            ->when($validated['status'] ?? null, function ($q, $status) {
                if ($status === 'overdue') {
                    $q->whereNull('returned_at')->whereNotNull('due_at')->where('due_at', '<', now());
                } elseif ($status === 'active') {
                    $q->whereNull('returned_at')->where(function ($x) { $x->whereNull('due_at')->orWhere('due_at', '>=', now()); });
                } else {
                    $q->where('status', 'returned');
                }
            })
            ->latest('borrowed_at')->paginate(20)->withQueryString();

        return view('admin.borrowings.index', compact('borrowings', 'validated'));
    }

    public function return(Borrowing $borrowing): RedirectResponse
    {
        try {
            DB::transaction(function () use ($borrowing) {
                $lockedBorrowing = Borrowing::query()->lockForUpdate()->find($borrowing->id);
                if (! $lockedBorrowing || $lockedBorrowing->returned_at !== null || ! in_array($lockedBorrowing->status, ['active', 'overdue'], true)) {
                    throw new \LogicException('This borrowing is already returned or invalid.');
                }

                $book = Book::query()->lockForUpdate()->find($lockedBorrowing->book_id);
                if (! $book) {
                    throw new \LogicException('The associated book no longer exists.');
                }

                $lockedBorrowing->update(['returned_at' => Carbon::now(), 'status' => 'returned']);
                $book->update(['available_copies' => min($book->total_copies, $book->available_copies + 1)]);
            });
        } catch (\LogicException $e) {
            return back()->withErrors(['return' => $e->getMessage()]);
        }

        return back()->with('status', 'Return recorded successfully.');
    }
}
