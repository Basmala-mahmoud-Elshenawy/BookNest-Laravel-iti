<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Category;
use App\Models\User;
use Illuminate\View\View;

/**
 * Every route in this whole Admin\ namespace is protected by the 'admin'
 * middleware (see routes/web.php) -- controllers here never need to
 * re-check the role themselves, but they also never trust client input for
 * anything sensitive.
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_books' => Book::sum('total_copies'),
            'available_books' => Book::sum('available_copies'),
            'total_users' => User::count(),
            'total_categories' => Category::count(),
            'active_borrowings' => Borrowing::currentlyActive()->count(),
            'overdue_borrowings' => Borrowing::currentlyActive()->whereNotNull('due_at')->where('due_at', '<', now())->count(),
        ];

        $perCategory = Category::withCount('books')->orderByDesc('books_count')->get();
        $topCategory = $perCategory->first();

        $lowAvailability = Book::where('available_copies', '<=', 1)
            ->orderBy('available_copies')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'perCategory', 'topCategory', 'lowAvailability'));
    }
}
