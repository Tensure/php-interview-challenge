# Inventory API — Take-Home Interview

A small PHP REST API for managing products and orders. Part of it already
works; your job is to extend and fix it. See [TASKS.md](TASKS.md) for the
list of work, ordered roughly from "warm-up" to "will probably take a while."

## Stack

- PHP 8.1+, no framework — plain PDO + a tiny hand-rolled router
- SQLite (zero setup, file-based)
- PHPUnit for tests

## Setup

```bash
composer install
composer seed        # creates database/database.sqlite and seeds sample data
composer serve        # starts php -S localhost:8000 -t public
```

## Existing endpoints

| Method | Path            | Status                      |
|--------|-----------------|------------------------------|
| GET    | /products       | works (no filtering/sorting/pagination yet) |
| GET    | /products/{id}  | works |
| POST   | /products       | works (no validation yet) |
| PUT    | /products/{id}  | works |
| DELETE | /products/{id}  | **not implemented** (501) |
| POST   | /orders         | works (has a concurrency bug) |
| GET    | /orders/{id}    | works |

Example:

```bash
curl -s localhost:8000/products | jq
curl -s -X POST localhost:8000/products \
  -H 'Content-Type: application/json' \
  -d '{"category_id":1,"name":"USB-C Hub","price":29.99,"quantity":10}'
```

## Running tests

```bash
composer test
```

## What we're evaluating

We're less interested in a perfect diff than in how you think: how you read
unfamiliar code, the tradeoffs you make under time pressure, and how you
verify your own work. Commit as you go rather than in one big commit at the
end — we want to see your process, not just the destination.

It's fine (encouraged, even) to use an AI coding assistant/agent for this,
especially for the later tasks in TASKS.md. If you do, please leave a short
note in your PR description on which tasks you used one for and how you
directed it — we're curious about your workflow, not trying to catch you
out.
