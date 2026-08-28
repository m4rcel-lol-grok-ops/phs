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
$router->get('/health', [HomeController::class, 'health']);
$router->get('/sitemap.xml', [HomeController::class, 'sitemap']);

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

// Dashboard (auth enforced by middleware)
$router->get('/dashboard', [DashboardController::class, 'index'], ['AuthMiddleware']);
$router->get('/dashboard/profile', [DashboardController::class, 'profile'], ['AuthMiddleware']);
$router->post('/dashboard/profile', [DashboardController::class, 'updateProfile'], ['AuthMiddleware']);
$router->post('/dashboard/avatar', [DashboardController::class, 'uploadAvatar'], ['AuthMiddleware']);
$router->post('/dashboard/avatar/delete', [DashboardController::class, 'deleteAvatar'], ['AuthMiddleware']);
$router->get('/dashboard/links', [DashboardController::class, 'links'], ['AuthMiddleware']);
$router->post('/dashboard/links', [DashboardController::class, 'createLink'], ['AuthMiddleware']);
$router->post('/dashboard/links/reorder', [DashboardController::class, 'reorderLinks'], ['AuthMiddleware']);
$router->post('/dashboard/links/{id}', [DashboardController::class, 'updateLink'], ['AuthMiddleware']);
$router->post('/dashboard/links/{id}/delete', [DashboardController::class, 'deleteLink'], ['AuthMiddleware']);
$router->post('/dashboard/links/{id}/move', [DashboardController::class, 'moveLink'], ['AuthMiddleware']);
$router->get('/dashboard/appearance', [DashboardController::class, 'appearance'], ['AuthMiddleware']);
$router->post('/dashboard/appearance', [DashboardController::class, 'updateAppearance'], ['AuthMiddleware']);
$router->post('/dashboard/banner', [DashboardController::class, 'uploadBanner'], ['AuthMiddleware']);
$router->post('/dashboard/banner/delete', [DashboardController::class, 'deleteBanner'], ['AuthMiddleware']);
$router->get('/dashboard/account', [DashboardController::class, 'account'], ['AuthMiddleware']);
$router->post('/dashboard/account', [DashboardController::class, 'updateAccount'], ['AuthMiddleware']);

// Admin
$router->get('/admin', [AdminController::class, 'index'], ['AdminMiddleware']);
$router->get('/admin/users', [AdminController::class, 'users'], ['AdminMiddleware']);
$router->post('/admin/users', [AdminController::class, 'userAction'], ['AdminMiddleware']);
$router->get('/admin/reports', [AdminController::class, 'reports'], ['AdminMiddleware']);
$router->post('/admin/reports', [AdminController::class, 'reportAction'], ['AdminMiddleware']);
$router->get('/admin/settings', [AdminController::class, 'settings'], ['AdminMiddleware']);
$router->post('/admin/settings', [AdminController::class, 'updateSettings'], ['AdminMiddleware']);
