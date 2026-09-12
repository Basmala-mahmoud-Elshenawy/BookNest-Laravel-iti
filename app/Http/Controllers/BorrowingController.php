<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrowing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BorrowingController extends Controller
{
    private const LOAN_DAYS = 14;

    public function borrow(Request $request, Book $book): RedirectResponse
    {
        $user = $request->user();

        try {
            DB::transaction(function () use ($user, $book) {
                $lockedBook = Book::query()->lockForUpdate()->find($book->id);
                if (! $lockedBook) {
                    throw new NotFoundHttpException('Book not found.');
                }

                if ($lockedBook->available_copies < 1) {
                    throw new \RuntimeException('This book is currently unavailable.');
                }

                $existing = Borrowing::query()
                    ->where('user_id', $user->id)
                    ->where('book_id', $lockedBook->id)
                    ->currentlyActive()
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    throw new \LogicException('You already have an active borrowing for this book.');
                }

                $now = Carbon::now();
                Borrowing::create([
                    'user_id' => $user->id,
                    'book_id' => $lockedBook->id,
                    'borrowed_at' => $now,
                    'due_at' => $now->copy()->addDays(self::LOAN_DAYS),
                    'status' => 'active',
                ]);

                $updated = Book::whereKey($lockedBook->id)
                    ->where('available_copies', '>', 0)
                    ->decrement('available_copies');

                if ($updated !== 1) {
                    throw new \RuntimeException('The book became unavailable. Please try again.');
                }
            });
        } catch (\LogicException $e) {
            return back()->withErrors(['borrow' => $e->getMessage()]);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['borrow' => $e->getMessage()]);
        } catch (NotFoundHttpException $e) {
            return back()->withErrors(['borrow' => 'This book no longer exists.']);
        }

        return back()->with('status', 'Book borrowed successfully. It is due in '.self::LOAN_DAYS.' days.');
    }

    public function return(Request $request, Borrowing $borrowing): RedirectResponse
    {
        $user = $request->user();

        if ($borrowing->user_id !== $user->id) {
            abort(403, 'You can only return your own borrowings.');
        }

        try {
            DB::transaction(function () use ($borrowing) {
                $lockedBorrowing = Borrowing::query()->lockForUpdate()->find($borrowing->id);
                if (! $lockedBorrowing || $lockedBorrowing->returned_at !== null || ! in_array($lockedBorrowing->status, ['active', 'overdue'], true)) {
                    throw new \LogicException('This borrowing is already returned or invalid.');
                }

                $lockedBook = Book::query()->lockForUpdate()->find($lockedBorrowing->book_id);
                if (! $lockedBook) {
                    throw new \LogicException('The associated book no longer exists.');
                }

                $lockedBorrowing->update([
                    'returned_at' => Carbon::now(),
                    'status' => 'returned',
                ]);

                $newAvailable = min($lockedBook->total_copies, $lockedBook->available_copies + 1);
                $lockedBook->update(['available_copies' => $newAvailable]);
            });
        } catch (\LogicException $e) {
            return back()->withErrors(['return' => $e->getMessage()]);
        }

        return back()->with('status', 'Book returned successfully.');
    }

    public function index(Request $request): View
    {
        $borrowings = $request->user()->borrowings()->with('book')->latest('borrowed_at')->paginate(15)->withQueryString();
        return view('user.borrowings', compact('borrowings'));
    }
}
