<?php
// public/index.php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../lib/ZohoAuth.php';
require_once __DIR__ . '/../lib/ZohoCRM.php';

$config = require __DIR__ . '/../config/config.php';
$auth = new ZohoAuth($config);
$crm = new ZohoCRM($auth, $config);

// Obter produtos do Zoho CRM
$products = $crm->getProducts();

if (isset($products['data'])) {
    $pdo = new PDO('mysql:host=localhost;dbname=zoho_crm', 'root', '');

    foreach ($products['data'] as $product) {
        // Verificar se o produto já existe no banco de dados local
        $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt->execute([$product['id']]);
        $existingProduct = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingProduct) {
            // Se o produto já existe, verificar se o estoque foi alterado
            if ($existingProduct['stock'] != $product['Qty_in_Stock']) {
                // Atualizar o estoque e outras informações
                $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ? WHERE product_id = ?");
                $stmt->execute([
                    $product['Product_Name'],
                    $product['Description'],
                    $product['Unit_Price'],
                    $product['Qty_in_Stock'],
                    $product['id'],
                ]);
            }

            if($existingProduct['price'] != $product['Unit_Price']){
                // Atualizar o estoque e outras informações
                $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ? WHERE product_id = ?");
                $stmt->execute([
                    $product['Product_Name'],
                    $product['Description'],
                    $product['Unit_Price'],
                    $product['Qty_in_Stock'],
                    $product['id'],
                ]);
            }
        } else {
            // Se o produto não existe, inseri-lo
            $stmt = $pdo->prepare("INSERT INTO products (product_id, name, description, price, stock) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $product['id'],
                $product['Product_Name'],
                $product['Description'],
                $product['Unit_Price'],
                $product['Qty_in_Stock'],
            ]);
        }
    }

    echo "Produtos importados e atualizados com sucesso!";
} else {
    echo "Erro ao importar produtos.";
}
