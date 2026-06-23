<?php
$_SERVER['REQUEST_URI'] = '/login';
$_SERVER['REQUEST_METHOD'] = 'GET';
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());
$content = $response->getContent();
preg_match('/name="_token" value="([^"]+)"/', $content, $m);
$token = $m[1] ?? 'not found';
echo "Login status: " . $response->getStatusCode() . "\n";
echo "CSRF token: " . substr($token, 0, 15) . "...\n";
// now try login
$_SERVER['REQUEST_URI'] = '/login';
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = ['email' => 'admin@example.com', 'password' => 'password', '_token' => $token];
$request2 = Illuminate\Http\Request::capture();
$response2 = $kernel->handle($request2);
echo "POST login status: " . $response2->getStatusCode() . "\n";
echo "Redirect to: " . ($response2->headers->get('Location') ?? 'none') . "\n";
