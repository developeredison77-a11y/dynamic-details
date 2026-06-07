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

Route::middleware('auth')->prefix('dashboard')->group(function (): void {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::resource('employees', EmployeeController::class)->except(['show']);
    Route::resource('assets', AssetController::class)->except(['show']);
    Route::get('/asset-brands', [AssetBrandController::class, 'index'])->name('asset-brands.index');
    Route::post('/asset-brands', [AssetBrandController::class, 'store'])->name('asset-brands.store');
    Route::get('/asset-brands/{assetBrand}/edit', [AssetBrandController::class, 'edit'])->name('asset-brands.edit');
    Route::put('/asset-brands/{assetBrand}', [AssetBrandController::class, 'update'])->name('asset-brands.update');
    Route::patch('/asset-brands/{assetBrand}/status', [AssetBrandController::class, 'toggleStatus'])->name('asset-brands.status');
    Route::delete('/asset-brands/{assetBrand}', [AssetBrandController::class, 'destroy'])->name('asset-brands.destroy');
    Route::get('/asset-categories', [AssetCategoryController::class, 'index'])->name('asset-categories.index');
    Route::post('/asset-categories', [AssetCategoryController::class, 'store'])->name('asset-categories.store');
    Route::get('/asset-handovers', [AssetHandoverController::class, 'index'])->name('asset-handovers.index');
    Route::get('/asset-handovers/create', [AssetHandoverController::class, 'create'])->name('asset-handovers.create');
    Route::post('/asset-handovers', [AssetHandoverController::class, 'store'])->name('asset-handovers.store');
    Route::get('/asset-handovers/{assetHandover}', [AssetHandoverController::class, 'show'])->name('asset-handovers.show');
    Route::get('/asset-handovers/{assetHandover}/print', [AssetHandoverController::class, 'print'])->name('asset-handovers.print');
    Route::get('/asset-returns', [AssetReturnController::class, 'index'])->name('asset-returns.index');
    Route::post('/asset-returns/{assignment}', [AssetReturnController::class, 'store'])->name('asset-returns.store');
    Route::get('/asset-returns/{assetReturn}/print', [AssetReturnController::class, 'print'])->name('asset-returns.print');
    Route::get('/declarations', [DeclarationController::class, 'index'])->name('declarations.index');
    Route::post('/declarations/{assignment}', [DeclarationController::class, 'store'])->name('declarations.store');
    Route::get('/declarations/{declaration}', [DeclarationController::class, 'show'])->name('declarations.show');
    Route::get('/declarations/{declaration}/print', [DeclarationController::class, 'print'])->name('declarations.print');
    Route::get('/imports', [ImportController::class, 'index'])->name('imports.index');
    Route::get('/imports/employees', [ImportController::class, 'employeeIndex'])->name('imports.employees.index');
    Route::get('/imports/assets', [ImportController::class, 'assetIndex'])->name('imports.assets.index');
    Route::get('/imports/template/{type}', [ImportController::class, 'template'])->name('imports.template');
    Route::post('/imports/employees', [ImportController::class, 'employees'])->name('imports.employees');
    Route::post('/imports/assets', [ImportController::class, 'assets'])->name('imports.assets');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/{type}', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
});

Route::post('/logout', function (Request $request) {
    Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->middleware('auth')->name('logout');
