<?php
use Slim\Factory\AppFactory;
use Illuminate\Database\Capsule\Manager as Capsule;

require __DIR__ . '/../config/database.php';

$app = AppFactory::create();

$app->post('/purchases', function ($request, $response) {
    $data = $request->getParsedBody();

    $purchase = Capsule::table('purchases')->insertGetId([
        'user_id' => $data['user_id'],
    ]);

    foreach ($data['products'] as $product) {
        Capsule::table('purchase_details')->insert([
            'purchase_id' => $purchase,
            'product_id' => $product['id'],
            'quantity' => $product['quantity']
        ]);
    }

    return $response->withJson(['message' => 'Compra registrada'], 201);
});

$app->get('/purchases/{id}', function ($request, $response, $args) {
    $purchase = Capsule::table('purchases')
        ->where('id', $args['id'])
        ->first();

    $details = Capsule::table('purchase_details')
        ->where('purchase_id', $args['id'])
        ->join('products', 'purchase_details.product_id', '=', 'products.id')
        ->select('products.name', 'purchase_details.quantity')
        ->get();

    return $response->withJson(['purchase' => $purchase, 'details' => $details]);
});

$app->run();
?>
