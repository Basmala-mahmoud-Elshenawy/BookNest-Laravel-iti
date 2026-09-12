# BookNest — Laravel Library Management System

BookNest is a real Laravel 12 library application with public book browsing, authentication, deterministic recommendations, favorites, borrowing/returns, overdue handling, admin CRUD, admin borrowing management, and a role-gated AI assistant embedded in the website (direct AI API call, not an agent).

## Demo credentials

After seeding:

- Admin: `admin@booknest.test` / `password`
- User: `demo@booknest.test` / `password`
- User: `reader@booknest.test` / `password`

## Main implemented workflows

- **Borrowing:** authenticated users can borrow available books, receive a 14-day due date, and return their own loans. Borrow and return operations use database transactions and row locks; duplicate active loans are rejected and copy counts are bounded.
- **Overdue:** a loan is effectively `Overdue` whenever it is unreturned and its due date has passed, so the UI does not depend on a stale manually-updated status.
- **Favorites:** users can toggle favorites and view their own favorites; the database unique constraint prevents duplicates and authorization prevents changing another user's favorites.
- **Authors:** admins can create, edit, list, search, and safely delete authors. Authors linked to books cannot be deleted until the relationships are removed.
- **Admin borrowings:** admins can search/filter all borrowing records and record returns.
- **User dashboard:** current loans, overdue count, favorites count, recommendations, recent history, profile, favorites, and chatbot shortcuts.
- **Search:** title, description, ISBN, author, category, availability, title sorting, recommendation sorting, pagination, and query-string preservation.
- **Recommendations:** deterministic database-backed profile/category/token matching is preserved; no random/static recommendation scores were introduced.
- **Chatbot:** authentication → backend role → permitted data → AI context → response. The AI client never queries the database and cannot grant itself permissions. Without an API key/provider, the existing deterministic fallback remains available.
- **Admin authorization:** every admin route is protected by backend middleware that reads only the authenticated `User` model's role.

## Setup / exact local commands

From the project root:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
npm install
npm run build
php artisan serve
```

If you want the Vite development server instead of the compiled frontend:

```bash
npm run dev
```

The Library Assistant is fully rule-based and runs locally. It does not require an AI API key and does not call OpenAI, Gemini, Anthropic, or any other external AI service.

## Tests

Run:

```bash
php artisan test
```

The test suite covers authentication, admin authorization, book search, category/author CRUD, borrowing/returning, duplicate/unavailable borrowing, overdue behavior, favorites ownership, recommendation matching, chatbot authorization, spoofed role parameters, and AI fallback behavior. Public registration always creates a User; only an authenticated Admin can create/promote an Admin from Admin → Users.

## Verification note

The supplied execution environment for this delivery did not contain Composer or the project's `vendor/` directory, and outbound DNS/network access was unavailable. Therefore Laravel dependency installation, `php artisan`, browser execution, database migrations/seeders, and PHPUnit could not be executed in this environment. All application PHP files were syntax-checked successfully with PHP 8.4, and the project was statically audited for the requested routes, controllers, views, relationships, migrations, tests, and placeholder/dead-action patterns.

The real book-cover assets and the 233-entry seed manifest are preserved. Book metadata is not fabricated; the supplied seed data continues to use the existing asset-derived metadata.
