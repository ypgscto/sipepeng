<?php

use App\Http\Controllers\Admin\Lppm\CommunityServiceSchemeController;
use App\Http\Controllers\Admin\Lppm\DocumentCategoryController;
use App\Http\Controllers\Admin\Lppm\DocumentTemplateController;
use App\Http\Controllers\Admin\Lppm\FocusAreaController;
use App\Http\Controllers\Admin\Lppm\FundingSourceController;
use App\Http\Controllers\Admin\Lppm\IpTypeController;
use App\Http\Controllers\Admin\Lppm\LetterTypeController;
use App\Http\Controllers\Admin\Lppm\MasterDataController;
use App\Http\Controllers\Admin\Lppm\OutputTypeController;
use App\Http\Controllers\Admin\Lppm\PartnerTypeController;
use App\Http\Controllers\Admin\Lppm\ProposalStatusController;
use App\Http\Controllers\Admin\Lppm\PublicationTypeController;
use App\Http\Controllers\Admin\Lppm\ResearchSchemeController;
use App\Http\Controllers\Admin\Lppm\ReviewerController;
use App\Http\Controllers\Admin\Lppm\ScienceClusterController;
use Illuminate\Support\Facades\Route;

$viewRoles = implode('|', config('sipepeng_master.view_roles', []));
$manageRoles = implode('|', config('sipepeng_master.manage_roles', []));

Route::get('/', [MasterDataController::class, 'index'])->name('index');

$resources = [
    'research-schemes' => ResearchSchemeController::class,
    'community-service-schemes' => CommunityServiceSchemeController::class,
    'output-types' => OutputTypeController::class,
    'funding-sources' => FundingSourceController::class,
    'focus-areas' => FocusAreaController::class,
    'science-clusters' => ScienceClusterController::class,
    'partner-types' => PartnerTypeController::class,
    'document-templates' => DocumentTemplateController::class,
    'reviewers' => ReviewerController::class,
    'document-categories' => DocumentCategoryController::class,
    'ip-types' => IpTypeController::class,
    'publication-types' => PublicationTypeController::class,
    'letter-types' => LetterTypeController::class,
    'proposal-statuses' => ProposalStatusController::class,
];

foreach ($resources as $uri => $controller) {
    Route::middleware("role:{$viewRoles}")->group(function () use ($uri, $controller): void {
        Route::get($uri, [$controller, 'index'])->name("{$uri}.index");
        Route::get("{$uri}/{record}", [$controller, 'show'])->name("{$uri}.show")->whereNumber('record');

        if ($uri === 'document-templates') {
            Route::get("{$uri}/{record}/download", [$controller, 'download'])
                ->name("{$uri}.download")
                ->whereNumber('record');
        }
    });

    Route::middleware("role:{$manageRoles}")->group(function () use ($uri, $controller): void {
        Route::get("{$uri}/create", [$controller, 'create'])->name("{$uri}.create");
        Route::post($uri, [$controller, 'store'])->name("{$uri}.store");
        Route::get("{$uri}/{record}/edit", [$controller, 'edit'])->name("{$uri}.edit")->whereNumber('record');
        Route::put("{$uri}/{record}", [$controller, 'update'])->name("{$uri}.update")->whereNumber('record');
        Route::delete("{$uri}/{record}", [$controller, 'destroy'])->name("{$uri}.destroy")->whereNumber('record');
        Route::patch("{$uri}/{record}/toggle-active", [$controller, 'toggleActive'])
            ->name("{$uri}.toggle-active")
            ->whereNumber('record');
        Route::patch("{$uri}/{record}/restore", [$controller, 'restore'])
            ->name("{$uri}.restore")
            ->whereNumber('record');
    });
}
