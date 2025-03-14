<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\User;

// Configuración de la base de datos
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'pgsql',
    'host' => 'localhost',
    'database' => 'Prueba_tecnica',
    'username' => 'postgres',
    'password' => 'Camilo1037',
    'charset' => 'utf8',
    'collation' => 'utf8_general_ci',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

// Verificación de conexión
try {
    Capsule::connection()->getPdo();
    echo "Conexión a la base de datos exitosa!";
} catch (\Exception $e) {
    echo "Error al conectar a la base de datos: " . $e->getMessage();
}

// Inicializo Slim
$app = AppFactory::create();

// Habilitar middleware de error
$app->addErrorMiddleware(true, true, true);

// Ruta para verificar que el servidor si está funcionando
$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("¡Slim Framework funcionando!");
    return $response;
});

// ---------------------- RUTAS PARA LOS PRODUCTOS ----------------------

// Crear producto
$app->post('/product', function (Request $request, Response $response, $args) {
    $data = $request->getParsedBody();

    // Crear producto
    $product = new Product();
    $product->name = $data['name'];
    $product->description = $data['description'];
    $product->price = $data['price'];
    $product->save();

    $response->getBody()->write(json_encode(['message' => 'Producto creado correctamente', 'product' => $product]));
    return $response->withHeader('Content-Type', 'application/json');
});

// Todos los productos
$app->get('/products', function (Request $request, Response $response, $args) {
    $products = Product::all();
    $response->getBody()->write(json_encode($products));
    return $response->withHeader('Content-Type', 'application/json');
});

// Producto por ID
$app->get('/products/{id}', function (Request $request, Response $response, $args) {
    $product = Product::find($args['id']);
    if ($product) {
        $response->getBody()->write(json_encode($product));
    } else {
        $response->getBody()->write(json_encode(['message' => 'Producto no encontrado']));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }
    return $response->withHeader('Content-Type', 'application/json');
});

// Modificar un producto
$app->put('/products/{id}', function (Request $request, Response $response, $args) {
    $data = $request->getParsedBody();
    $product = Product::find($args['id']);

    if ($product) {
        $product->name = $data['name'] ?? $product->name;
        $product->description = $data['description'] ?? $product->description;
        $product->price = $data['price'] ?? $product->price;
        $product->save();

        $response->getBody()->write(json_encode(['message' => 'Producto actualizado correctamente', 'product' => $product]));
    } else {
        $response->getBody()->write(json_encode(['message' => 'Producto no encontrado']));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }

    return $response->withHeader('Content-Type', 'application/json');
});

// Eliminar producto
$app->delete('/products/{id}', function (Request $request, Response $response, $args) {
    $product = Product::find($args['id']);

    if ($product) {
        $product->delete();
        $response->getBody()->write(json_encode(['message' => 'Producto eliminado correctamente']));
    } else {
        $response->getBody()->write(json_encode(['message' => 'Producto no encontrado']));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }

    return $response->withHeader('Content-Type', 'application/json');
});

// ---------------------- RUTAS COMPRAS ----------------------

// Registrar compra
$app->post('/purchase', function (Request $request, Response $response, $args) {
    $data = $request->getParsedBody();

    // Verificar usuario 
    $user = User::find($data['user_id']);
    if (!$user) {
        $response->getBody()->write(json_encode(['message' => 'Usuario no encontrado']));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }

    // Verificar que los productos existan 
    $products = Product::whereIn('id', array_column($data['products'], 'product_id'))->get();
    if ($products->count() !== count($data['products'])) {
        $response->getBody()->write(json_encode(['message' => 'Uno o más productos no existen']));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }

    // Calcular precio total 
    $total_price = 0;
    foreach ($data['products'] as $item) {
        $product = $products->where('id', $item['product_id'])->first();
        $total_price += $product->price * $item['quantity'];
    }

    // Registro de compra
    $purchase = new Purchase();
    $purchase->user_id = $data['user_id'];
    $purchase->total_price = $total_price;
    $purchase->save();

    // Registro detalles de compra
    foreach ($data['products'] as $item) {
        $purchaseDetail = new PurchaseDetail();
        $purchaseDetail->purchase_id = $purchase->id;
        $purchaseDetail->product_id = $item['product_id'];
        $purchaseDetail->quantity = $item['quantity'];
        $purchaseDetail->price = $products->where('id', $item['product_id'])->first()->price;
        $purchaseDetail->save();
    }

    $response->getBody()->write(json_encode(['message' => 'Compra registrada correctamente', 'purchase' => $purchase]));
    return $response->withHeader('Content-Type', 'application/json');
});

// Todas las compras
$app->get('/purchases', function (Request $request, Response $response, $args) {
    $purchases = Purchase::with('details')->get();
    $response->getBody()->write(json_encode($purchases));
    return $response->withHeader('Content-Type', 'application/json');
});

// Obtener una compra por ID
$app->get('/purchases/{id}', function (Request $request, Response $response, $args) {
    $purchase = Purchase::with('details')->find($args['id']);
    if ($purchase) {
        $response->getBody()->write(json_encode($purchase));
    } else {
        $response->getBody()->write(json_encode(['message' => 'Compra no encontrada']));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }
    return $response->withHeader('Content-Type', 'application/json');
});

// Ejecutar la aplicación
$app->run();

//saludos Jonattan