<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Database;

$pdo = Database::connection();

$pdo->exec(file_get_contents(__DIR__ . '/schema.sql'));

$categories = ['Electronics', 'Books', 'Home & Kitchen', 'Toys'];
$insertCategory = $pdo->prepare('INSERT INTO categories (name) VALUES (:name)');
foreach ($categories as $name) {
    $insertCategory->execute(['name' => $name]);
}

$products = [
    ['category_id' => 1, 'name' => 'Wireless Mouse', 'description' => 'Ergonomic 2.4GHz wireless mouse', 'price' => 19.99, 'quantity' => 50],
    ['category_id' => 1, 'name' => 'Mechanical Keyboard', 'description' => 'Hot-swappable mechanical keyboard', 'price' => 89.50, 'quantity' => 20],
    ['category_id' => 2, 'name' => 'The Pragmatic Programmer', 'description' => 'Classic software engineering book', 'price' => 34.95, 'quantity' => 15],
    ['category_id' => 3, 'name' => 'French Press', 'description' => '34oz stainless steel french press', 'price' => 24.00, 'quantity' => 1],
    ['category_id' => 4, 'name' => 'Building Blocks Set', 'description' => '250-piece building block set', 'price' => 29.99, 'quantity' => 0],
];

$insertProduct = $pdo->prepare(
    'INSERT INTO products (category_id, name, description, price, quantity) VALUES (:category_id, :name, :description, :price, :quantity)'
);
foreach ($products as $product) {
    $insertProduct->execute($product);
}

echo "Seeded " . count($categories) . " categories and " . count($products) . " products.\n";
