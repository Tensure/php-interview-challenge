<?php

namespace App\Controllers;

use App\Database;
use App\Support\Response;

class ProductController
{
    public function index(array $params): void
    {
        $pdo = Database::connection();

        // NOTE: query params like ?sort=, ?category_id=, ?min_price=, ?max_price=,
        // ?page=, ?per_page= are intentionally ignored right now. See TASKS.md.
        $stmt = $pdo->query('SELECT * FROM products WHERE deleted_at IS NULL');
        $products = $stmt->fetchAll();

        Response::json(['data' => $products]);
    }

    public function show(array $params): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute(['id' => $params['id']]);
        $product = $stmt->fetch();

        if (!$product) {
            Response::error('Product not found', 404);
            return;
        }

        Response::json(['data' => $product]);
    }

    public function store(array $params): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        // NOTE: no validation is performed here yet (negative price, negative
        // quantity, missing name, unknown category_id, etc. are all accepted).
        // See TASKS.md task 1.

        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO products (category_id, name, description, price, quantity)
             VALUES (:category_id, :name, :description, :price, :quantity)'
        );
        $stmt->execute([
            'category_id' => $input['category_id'] ?? null,
            'name' => $input['name'] ?? null,
            'description' => $input['description'] ?? null,
            'price' => $input['price'] ?? 0,
            'quantity' => $input['quantity'] ?? 0,
        ]);

        $id = $pdo->lastInsertId();
        $stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id');
        $stmt->execute(['id' => $id]);

        Response::json(['data' => $stmt->fetch()], 201);
    }

    public function update(array $params): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $pdo = Database::connection();

        $stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute(['id' => $params['id']]);
        $product = $stmt->fetch();

        if (!$product) {
            Response::error('Product not found', 404);
            return;
        }

        $stmt = $pdo->prepare(
            "UPDATE products
             SET name = :name, description = :description, price = :price,
                 quantity = :quantity, category_id = :category_id, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id"
        );
        $stmt->execute([
            'id' => $params['id'],
            'category_id' => $input['category_id'] ?? $product['category_id'],
            'name' => $input['name'] ?? $product['name'],
            'description' => $input['description'] ?? $product['description'],
            'price' => $input['price'] ?? $product['price'],
            'quantity' => $input['quantity'] ?? $product['quantity'],
        ]);

        $stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id');
        $stmt->execute(['id' => $params['id']]);

        Response::json(['data' => $stmt->fetch()]);
    }

    public function destroy(array $params): void
    {
        // TODO: not implemented yet. See TASKS.md task 2.
        Response::error('Not implemented', 501);
    }
}
