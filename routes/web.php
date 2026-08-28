<?php
declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\ProfileController;
use App\Controllers\DashboardController;
use App\Controllers\DiscoverController;
use App\Controllers\AdminController;

/** @var \App\Core\Router $router */
$router = $app->getRouter();

// Public pages
$router->get('/', [HomeController::class, 'index']);
$router->get('/features', [HomeController::class, 'features']);
$router->get('/about', [HomeController::class, 'about']);
$router->get('/content-policy', [HomeController::class, 'contentPolicy']);
$router->get('/privacy', [HomeController::class, 'privacy']);
$router->get('/terms', [HomeController::class, 'terms']);
$router->get('/contact', [HomeController::class, 'contact']);

// Auth
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->post('/logout', [AuthController::class, 'logout']);

// Discover
$router->get('/discover', [DiscoverController::class, 'index']);

// Profile interactions
$router->get('/click/{id}', [ProfileController::class, 'click']);
$router->post('/report', [ProfileController::class, 'report']);

// Dashboard (auth required via controller)
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/dashboard/profile', [DashboardController::class, 'profile']);
$router->post('/dashboard/profile', [DashboardController::class, 'updateProfile']);
$router->post('/dashboard/avatar', [DashboardController::class, 'uploadAvatar']);
$router->get('/dashboard/links', [DashboardController::class, 'links']);
$router->post('/dashboard/links', [DashboardController::class, 'createLink']);
$router->post('/dashboard/links/{id}', [DashboardController::class, 'updateLink']);
$router->post('/dashboard/links/{id}/delete', [DashboardController::class, 'deleteLink']);
$router->post('/dashboard/links/reorder', [DashboardController::class, 'reorderLinks']);
$router->get('/dashboard/appearance', [DashboardController::class, 'appearance']);
$router->post('/dashboard/appearance', [DashboardController::class, 'updateAppearance']);
$router->post('/dashboard/banner', [DashboardController::class, 'uploadBanner']);
$router->get('/dashboard/account', [DashboardController::class, 'account']);
$router->post('/dashboard/account', [DashboardController::class, 'updateAccount']);

// Admin
$router->get('/admin', [AdminController::class, 'index']);
$router->get('/admin/users', [AdminController::class, 'users']);
$router->post('/admin/users', [AdminController::class, 'userAction']);
$router->get('/admin/reports', [AdminController::class, 'reports']);
$router->post('/admin/reports', [AdminController::class, 'reportAction']);
$router->get('/admin/settings', [AdminController::class, 'settings']);
$router->post('/admin/settings', [AdminController::class, 'updateSettings']);
