<?php

namespace App\Controllers;

use App\Database;
use App\Support\Response;

class OrderController
{
    public function store(array $params): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $productId = $input['product_id'] ?? null;
        $quantity = (int) ($input['quantity'] ?? 0);

        $pdo = Database::connection();

        // NOTE: this read-then-write is NOT safe under concurrent requests.
        // Two simultaneous orders for the last item in stock can both read
        // quantity=1, both pass the check below, and both succeed, leaving
        // quantity at -1. See TASKS.md task 5.
        $stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute(['id' => $productId]);
        $product = $stmt->fetch();

        if (!$product) {
            Response::error('Product not found', 404);
            return;
        }

        if ($quantity <= 0 || $product['quantity'] < $quantity) {
            Response::error('Insufficient stock', 422);
            return;
        }

        $totalPrice = $product['price'] * $quantity;

        $insert = $pdo->prepare(
            'INSERT INTO orders (product_id, quantity, total_price) VALUES (:product_id, :quantity, :total_price)'
        );
        $insert->execute([
            'product_id' => $productId,
            'quantity' => $quantity,
            'total_price' => $totalPrice,
        ]);

        $update = $pdo->prepare('UPDATE products SET quantity = quantity - :quantity WHERE id = :id');
        $update->execute(['quantity' => $quantity, 'id' => $productId]);

        $orderId = $pdo->lastInsertId();
        $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = :id');
        $stmt->execute(['id' => $orderId]);

        Response::json(['data' => $stmt->fetch()], 201);
    }

    public function show(array $params): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = :id');
        $stmt->execute(['id' => $params['id']]);
        $order = $stmt->fetch();

        if (!$order) {
            Response::error('Order not found', 404);
            return;
        }

        Response::json(['data' => $order]);
    }
}
