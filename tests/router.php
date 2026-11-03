<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$file = realpath($root . $path);

if ($file !== false && str_starts_with($file, $root) && is_file($file)) {
    return false;
}

$routes = [
    '/' => 'index.php',
    '/about' => 'about.php',
    '/contact' => 'contact.php',
    '/prayers' => 'prayers.php',
    '/rosary' => 'rosary.php',
    '/privacy' => 'trust.php',
    '/safety' => 'trust.php',
    '/sources' => 'trust.php',
    '/account' => ['file' => 'account.php', 'mode' => 'login'],
    '/account/login' => ['file' => 'account.php', 'mode' => 'login'],
    '/account/register' => ['file' => 'account.php', 'mode' => 'register'],
    '/account/verify' => ['file' => 'account.php', 'mode' => 'verify'],
    '/account/forgot' => ['file' => 'account.php', 'mode' => 'forgot'],
    '/account/reset' => ['file' => 'account.php', 'mode' => 'reset'],
    '/account/logout' => ['file' => 'account.php', 'mode' => 'logout'],
    '/feed' => ['file' => 'member.php', 'page' => 'feed'],
    '/saved' => ['file' => 'member.php', 'page' => 'saved'],
    '/settings' => ['file' => 'member.php', 'page' => 'settings'],
    '/notifications' => ['file' => 'member.php', 'page' => 'notifications'],
    '/api/chat' => 'api/chat.php',
    '/api/feedback' => 'api/feedback.php',
    '/api/newsletter' => 'api/newsletter.php',
];

if (isset($routes[$path])) {
    $route = $routes[$path];
    if (is_array($route)) {
        if (isset($route['mode'])) {
            $_GET['mode'] = $route['mode'];
        }
        if (isset($route['page'])) {
            $_GET['page'] = $route['page'];
        }
        $route = $route['file'];
    }
    require $root . '/' . $route;
    return true;
}

http_response_code(404);
echo 'Not found';
