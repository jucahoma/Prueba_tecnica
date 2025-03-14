<?php
use Slim\Factory\AppFactory;
use Illuminate\Database\Capsule\Manager as Capsule;

require __DIR__ . '/../config/database.php';

$app = AppFactory::create();

$app->post('/products', function ($request, $response) {
    $data = $request->getParsedBody();

    $product = Capsule::table('products')->insert([
        'name' => $data['name'],
        'price' => $data['price']
    ]);

    return $response->withJson(['message' => 'Producto registrado'], 201);
});

$app->get('/products', function ($request, $response) {
    $products = Capsule::table('products')->get();
    return $response->withJson($products);
});

$app->run();
?>
