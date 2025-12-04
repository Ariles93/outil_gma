<?php

use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\AgentsController;
use App\Controllers\MaterialsController;
use App\Controllers\AssignmentsController;

use App\Controllers\LogsController;
use App\Controllers\UsersController;
use App\Controllers\TrashController;
use App\Controllers\CategoriesController;
use App\Controllers\ExportsController;

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Start session
session_start();

// Helper functions
require_once __DIR__ . '/app/Core/Helpers.php';

if (!function_exists('url')) {
    function url($path = '')
    {
        return '/gestion-materiel/public/' . ltrim($path, '/');
    }
}
require_once __DIR__ . '/app/Core/Router.php';
require_once __DIR__ . '/app/Core/Controller.php';
require_once __DIR__ . '/app/Config/Database.php';
require_once __DIR__ . '/app/Core/Model.php';

// Models
require_once __DIR__ . '/app/Models/Agent.php';
require_once __DIR__ . '/app/Models/Assignment.php';
require_once __DIR__ . '/app/Models/Material.php';
require_once __DIR__ . '/app/Models/User.php';
require_once __DIR__ . '/app/Models/Log.php';
require_once __DIR__ . '/app/Models/Category.php';

// Controllers
require_once __DIR__ . '/app/Controllers/AuthController.php';
require_once __DIR__ . '/app/Controllers/DashboardController.php';
require_once __DIR__ . '/app/Controllers/AgentsController.php';
require_once __DIR__ . '/app/Controllers/MaterialsController.php';
require_once __DIR__ . '/app/Controllers/AssignmentsController.php';
require_once __DIR__ . '/app/Controllers/LogsController.php';
require_once __DIR__ . '/app/Controllers/UsersController.php';
require_once __DIR__ . '/app/Controllers/TrashController.php';
require_once __DIR__ . '/app/Controllers/CategoriesController.php';
require_once __DIR__ . '/app/Controllers/ExportsController.php';

$router = new Router();

// Define routes
// 1. Auth
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'authenticate']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/forgot-password', [AuthController::class, 'forgotPassword']);
$router->post('/forgot-password/send', [AuthController::class, 'sendResetLink']);
$router->get('/reset-password', [AuthController::class, 'resetPassword']);
$router->post('/reset-password/update', [AuthController::class, 'updatePassword']);

// 2. Dashboard
$router->get('/', [DashboardController::class, 'index']);

// 3. Categories
$router->get('/categories', [CategoriesController::class, 'index']);
$router->get('/categories/create', [CategoriesController::class, 'create']);
$router->post('/categories/store', [CategoriesController::class, 'store']);
$router->get('/categories/edit', [CategoriesController::class, 'edit']);
$router->post('/categories/update', [CategoriesController::class, 'update']);
$router->post('/categories/delete', [CategoriesController::class, 'delete']);

// 4. Agents
$router->get('/agents', [AgentsController::class, 'index']);
$router->get('/agents/create', [AgentsController::class, 'create']);
$router->post('/agents/create', [AgentsController::class, 'store']);
$router->get('/agents/view', [AgentsController::class, 'show']);
$router->get('/agents/edit', [AgentsController::class, 'edit']);
$router->post('/agents/edit', [AgentsController::class, 'update']);

// 4. Materials
$router->get('/materials', [MaterialsController::class, 'index']);
$router->get('/materials/view', [MaterialsController::class, 'show']);
$router->get('/materials/edit', [MaterialsController::class, 'edit']);
$router->post('/materials/edit', [MaterialsController::class, 'update']);
$router->get('/materials/create', [MaterialsController::class, 'create']);
$router->post('/materials/create', [MaterialsController::class, 'store']);
$router->post('/materials/delete', [MaterialsController::class, 'delete']);

// 5. Assignments
$router->get('/assignments', [AssignmentsController::class, 'index']);
$router->get('/assignments/create', [AssignmentsController::class, 'create']);
$router->post('/assignments/create', [AssignmentsController::class, 'store']);
$router->post('/assignments/return', [AssignmentsController::class, 'returnMaterial']);
$router->get('/assignments/pdf', [AssignmentsController::class, 'generatePdf']);
$router->get('/assignments/return-pdf', [AssignmentsController::class, 'generateReturnPdf']);

// 6. Logs
$router->get('/logs', [LogsController::class, 'index']);

// 7. Users
$router->get('/users', [UsersController::class, 'index']);
$router->get('/users/create', [UsersController::class, 'create']);
$router->post('/users/store', [UsersController::class, 'store']);
$router->get('/users/edit', [UsersController::class, 'edit']);
$router->post('/users/update', [UsersController::class, 'update']);
$router->post('/users/delete', [UsersController::class, 'delete']);

// 8. Trash
$router->get('/trash', [TrashController::class, 'index']);
$router->post('/trash/restore', [TrashController::class, 'restore']);

// 9. Exports
$router->get('/exports/materials', [ExportsController::class, 'exportMaterials']);
$router->get('/exports/agents', [ExportsController::class, 'exportAgents']);

// 10. API
$router->get('/api/materials/search', [MaterialsController::class, 'apiSearch']);

// 11. Dispatch
$router->dispatch();