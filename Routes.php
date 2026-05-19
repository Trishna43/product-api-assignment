<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->post('products', 'ProductController::create');
$routes->get('products/(:num)', 'ProductController::show/$1');
$routes->put('products/(:num)', 'ProductController::update/$1');