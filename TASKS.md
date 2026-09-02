# Tasks

Work through these roughly in order. Tasks 1-3 should be quick for a senior
engineer familiar with PHP — they're meant to get you oriented in the
codebase. Tasks 4 onward are meaningfully bigger; you're not expected to
finish all of them in the time given, and using an AI coding
assistant/agent to move faster on them is expected and welcome. Prioritize
correctness and clear tradeoffs over finishing everything.

Please commit incrementally (roughly one commit per task) rather than one
giant commit at the end.

---

## 1. Validate product input (warm-up)

`ProductController::store` and `update` currently accept anything —
negative prices, negative/non-integer quantities, missing `name`, or a
`category_id` that doesn't exist in the `categories` table.

Add validation that returns `422` with a useful error body on bad input.
Cover both `store` and `update`.

## 2. Implement DELETE /products/{id} (warm-up)

`ProductController::destroy` is a stub that returns `501`. Implement a
**soft delete**: set `deleted_at` instead of removing the row, and make sure
soft-deleted products stop showing up in `GET /products` and
`GET /products/{id}` (the listing query already filters on `deleted_at`,
double check the single-record lookups do too).

## 3. Filtering and sorting on GET /products (warm-up/medium)

`GET /products` ignores query parameters today. Add support for:

- `category_id` — exact match
- `min_price`, `max_price` — inclusive range
- `sort` — one of `price_asc`, `price_desc`, `name_asc`, `name_desc`
  (reject anything else with `422` rather than passing it into SQL)

Watch out for SQL injection — every value in this codebase should go
through a prepared statement, including sort direction/column, which can't
be bound as a normal parameter.

---

## 4. Pagination

Add `page` and `per_page` query params to `GET /products` (sensible
defaults, e.g. `page=1`, `per_page=20`, with a max `per_page` to prevent
abuse). Response should include pagination metadata (total count, current
page, total pages) alongside `data`. Should compose with task 3's filters.

## 5. Fix the race condition in order creation

`OrderController::store` reads a product's `quantity`, checks it in PHP,
then issues a separate `UPDATE` — a classic check-then-act race. Under
concurrent requests for the same product, stock can go negative (two
requests can both pass the check for the last item).

Fix this so it's safe under concurrency — a transaction with appropriate
locking, or an atomic conditional update
(`UPDATE ... SET quantity = quantity - :qty WHERE id = :id AND quantity >= :qty`
and checking rows-affected) both work. Whichever approach you pick, explain
why in a comment or your PR description.

We will test this by firing concurrent requests at a low-stock product
(see the `french_press` seed row, which starts at quantity 1) — this is
worth verifying yourself before considering it done, not just reasoning
about it in the abstract.

## 6. API key authentication for mutating endpoints

`POST`/`PUT`/`DELETE` on `/products` and `POST /orders` should require a
valid API key, e.g. via an `X-API-Key` header. Reads (`GET`) stay open.
There's a stub in `src/Config.php` (`Config::apiKey()`) you can build on —
feel free to change how the key is sourced/stored if you have a better
idea, just document it.

Unauthorized requests should get a `401` with a clear error body.

## 7. Rate limiting

Add basic rate limiting per API key (e.g. N requests per minute) on the
mutating endpoints from task 6. Exceeding the limit should return `429`
with a `Retry-After` header. State can live in SQLite or in-memory/file —
pick something reasonable for a single-process dev server and note the
limitation if it wouldn't survive multiple server processes.

## 8. Bulk product import via CSV

Add `POST /products/import` accepting a CSV file upload (columns:
`category_id,name,description,price,quantity`). Requirements:

- Validate every row using the same rules as task 1.
- The import should be transactional per-batch: if you can, insert valid
  rows and report invalid ones back to the caller (row number + reason)
  rather than failing the whole batch on one bad row — but this tradeoff
  is worth discussing in your PR description, there's more than one
  reasonable answer here.
- Response should summarize: rows processed, rows inserted, rows rejected
  (with reasons).

## 9. Test suite

Write PHPUnit tests covering:

- Each endpoint's happy path and at least one error case
- The validation rules from task 1
- The soft-delete behavior from task 2
- A concurrency test that actually exercises the fix from task 5 (e.g.
  spawning overlapping requests/processes against a low-stock product and
  asserting quantity never goes negative and no more orders succeed than
  stock allowed)

We care more about the concurrency test than 100% line coverage — it's the
one existing bug in this codebase that's actually hard to catch by reading
the code, so it's the one we most want to see verified rather than assumed
fixed.
