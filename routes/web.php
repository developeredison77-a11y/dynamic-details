<?php

use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\AssetBrandController;
use App\Http\Controllers\Backend\AssetCategoryController;
use App\Http\Controllers\Backend\AssetController;
use App\Http\Controllers\Backend\AssetHandoverController;
use App\Http\Controllers\Backend\AssetReturnController;
use App\Http\Controllers\Backend\ClientController;
use App\Http\Controllers\Backend\DeclarationController;
use App\Http\Controllers\Backend\EmployeeController;
use App\Http\Controllers\Backend\ImportController;
use App\Http\Controllers\Backend\ProfileController;
use App\Http\Controllers\Backend\ReportController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Backend\SettingsController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/', [DashboardController::class, 'index'])->middleware('permission:dashboard.view')->name('dashboard');
    Route::get('/clients', [ClientController::class, 'index'])->middleware('permission:clients.view')->name('clients.index');

    Route::get('/employees', [EmployeeController::class, 'index'])->middleware('permission:employees.view')->name('employees.index');
    Route::get('/employees/create', [EmployeeController::class, 'create'])->middleware('permission:employees.create')->name('employees.create');
    Route::post('/employees', [EmployeeController::class, 'store'])->middleware('permission:employees.create')->name('employees.store');
    Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->middleware('permission:employees.update')->name('employees.edit');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->middleware('permission:employees.update')->name('employees.update');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->middleware('permission:employees.delete')->name('employees.destroy');

    Route::get('/roles', [RoleController::class, 'index'])->middleware('permission:roles.view')->name('roles.index');
    Route::post('/roles', [RoleController::class, 'store'])->middleware('permission:roles.create')->name('roles.store');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->middleware('permission:roles.update')->name('roles.edit');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->middleware('permission:roles.update')->name('roles.update');
    Route::patch('/roles/{role}/status', [RoleController::class, 'toggleStatus'])->middleware('permission:roles.update')->name('roles.status');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:roles.delete')->name('roles.destroy');

    Route::get('/assets', [AssetController::class, 'index'])->middleware('permission:assets.view')->name('assets.index');
    Route::get('/assets/create', [AssetController::class, 'create'])->middleware('permission:assets.create')->name('assets.create');
    Route::post('/assets', [AssetController::class, 'store'])->middleware('permission:assets.create')->name('assets.store');
    Route::get('/assets/{asset}/edit', [AssetController::class, 'edit'])->middleware('permission:assets.update')->name('assets.edit');
    Route::put('/assets/{asset}', [AssetController::class, 'update'])->middleware('permission:assets.update')->name('assets.update');
    Route::delete('/assets/{asset}', [AssetController::class, 'destroy'])->middleware('permission:assets.delete')->name('assets.destroy');

    Route::get('/asset-brands', [AssetBrandController::class, 'index'])->middleware('permission:asset-brands.manage')->name('asset-brands.index');
    Route::post('/asset-brands', [AssetBrandController::class, 'store'])->middleware('permission:asset-brands.manage')->name('asset-brands.store');
    Route::get('/asset-brands/{assetBrand}/edit', [AssetBrandController::class, 'edit'])->middleware('permission:asset-brands.manage')->name('asset-brands.edit');
    Route::put('/asset-brands/{assetBrand}', [AssetBrandController::class, 'update'])->middleware('permission:asset-brands.manage')->name('asset-brands.update');
    Route::patch('/asset-brands/{assetBrand}/status', [AssetBrandController::class, 'toggleStatus'])->middleware('permission:asset-brands.manage')->name('asset-brands.status');
    Route::delete('/asset-brands/{assetBrand}', [AssetBrandController::class, 'destroy'])->middleware('permission:asset-brands.manage')->name('asset-brands.destroy');
    Route::get('/asset-categories', [AssetCategoryController::class, 'index'])->middleware('permission:asset-categories.manage')->name('asset-categories.index');
    Route::post('/asset-categories', [AssetCategoryController::class, 'store'])->middleware('permission:asset-categories.manage')->name('asset-categories.store');
    Route::get('/asset-categories/{assetCategory}/edit', [AssetCategoryController::class, 'edit'])->middleware('permission:asset-categories.manage')->name('asset-categories.edit');
    Route::put('/asset-categories/{assetCategory}', [AssetCategoryController::class, 'update'])->middleware('permission:asset-categories.manage')->name('asset-categories.update');
    Route::patch('/asset-categories/{assetCategory}/status', [AssetCategoryController::class, 'toggleStatus'])->middleware('permission:asset-categories.manage')->name('asset-categories.status');
    Route::delete('/asset-categories/{assetCategory}', [AssetCategoryController::class, 'destroy'])->middleware('permission:asset-categories.manage')->name('asset-categories.destroy');
    Route::get('/asset-handovers', [AssetHandoverController::class, 'index'])->middleware('permission:asset-handovers.view')->name('asset-handovers.index');
    Route::get('/asset-handovers/create', [AssetHandoverController::class, 'create'])->middleware('permission:asset-handovers.create')->name('asset-handovers.create');
    Route::post('/asset-handovers', [AssetHandoverController::class, 'store'])->middleware('permission:asset-handovers.create')->name('asset-handovers.store');
    Route::get('/asset-handovers/{assetHandover}', [AssetHandoverController::class, 'show'])->middleware('permission:asset-handovers.view')->name('asset-handovers.show');
    Route::get('/asset-handovers/{assetHandover}/print', [AssetHandoverController::class, 'print'])->middleware('permission:asset-handovers.view')->name('asset-handovers.print');
    Route::get('/asset-returns', [AssetReturnController::class, 'index'])->middleware('permission:asset-returns.view')->name('asset-returns.index');
    Route::post('/asset-returns/{assignment}', [AssetReturnController::class, 'store'])->middleware('permission:asset-returns.create')->name('asset-returns.store');
    Route::get('/asset-returns/{assetReturn}/print', [AssetReturnController::class, 'print'])->middleware('permission:asset-returns.view')->name('asset-returns.print');
    Route::get('/declarations', [DeclarationController::class, 'index'])->middleware('permission:declarations.view')->name('declarations.index');
    Route::post('/declarations/{assignment}', [DeclarationController::class, 'store'])->middleware('permission:declarations.create')->name('declarations.store');
    Route::get('/declarations/{declaration}', [DeclarationController::class, 'show'])->middleware('permission:declarations.view')->name('declarations.show');
    Route::get('/declarations/{declaration}/print', [DeclarationController::class, 'print'])->middleware('permission:declarations.view')->name('declarations.print');
    Route::get('/imports', [ImportController::class, 'index'])->middleware('permission:imports.view')->name('imports.index');
    Route::get('/imports/employees', [ImportController::class, 'employeeIndex'])->middleware('permission:employees.import')->name('imports.employees.index');
    Route::get('/imports/assets', [ImportController::class, 'assetIndex'])->middleware('permission:assets.import')->name('imports.assets.index');
    Route::get('/imports/template/{type}', [ImportController::class, 'template'])->name('imports.template');
    Route::post('/imports/employees', [ImportController::class, 'employees'])->middleware('permission:employees.import')->name('imports.employees');
    Route::post('/imports/assets', [ImportController::class, 'assets'])->middleware('permission:assets.import')->name('imports.assets');
    Route::get('/reports', [ReportController::class, 'index'])->middleware('permission:reports.view')->name('reports.index');
    Route::get('/reports/export/{type}', [ReportController::class, 'export'])->middleware('permission:reports.export')->name('reports.export');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/settings', [SettingsController::class, 'edit'])->middleware('permission:settings.view')->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->middleware('permission:settings.update')->name('settings.update');
});

Route::middleware('auth')->get('/dashboard/{path?}', function (?string $path = null) {
    return redirect('/'.ltrim((string) $path, '/'));
})->where('path', '.*');

Route::post('/logout', function (Request $request) {
    Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->middleware('auth')->name('logout');
