<?php

use App\Http\Controllers\API\BannerFormController;
use App\Http\Controllers\API\BlogCategoryController;
use App\Http\Controllers\API\BlogController;
use App\Http\Controllers\API\CareerController;
use App\Http\Controllers\API\CaseStudyController;
use App\Http\Controllers\API\ContactFormController;
use App\Http\Controllers\API\FaqController;
use App\Http\Controllers\API\GalleryController;
use App\Http\Controllers\API\IndustryReportController;
use App\Http\Controllers\API\LocationController;
use App\Http\Controllers\API\PageController;
use App\Http\Controllers\API\PartnerController;
use App\Http\Controllers\API\ProjectController;
use App\Http\Controllers\GoPartnersLoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\API\PropertyTypeController;
use App\Http\Controllers\API\TestimonialController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\WebinarController;
use App\Models\BannerForm;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', action: [AuthController::class, 'login']);
Route::post(uri: '/register', action: [AuthController::class, 'registerUser']);
Route::post(uri: 'delete-user', action: [AuthController::class, 'deleteUser']);


Route::apiResource(name: 'property-types', controller: PropertyTypeController::class);


Route::apiResource('locations', controller: LocationController::class);

Route::apiResource('projects', controller: ProjectController::class);

Route::apiResource(name: 'blog-categories', controller: BlogCategoryController::class);

Route::apiResource(name: 'blogs', controller: BlogController::class);


Route::apiResource('testimonials', controller: TestimonialController::class);

// FAQ routes
Route::apiResource('faqs', controller: FaqController::class);

// Banner Form routes
Route::apiResource('banner-forms', controller: BannerFormController::class);

// Contact Form routes
Route::apiResource('Contact-form', controller: ContactFormController::class);

// Page routes
Route::apiResource('pages', controller: PageController::class);


// Gallery routes
Route::apiResource(name: 'gallery', controller: GalleryController::class);

// Career
Route::apiResource(name: 'career', controller: CareerController::class);

// partner
Route::apiResource(name: 'partner', controller: PartnerController::class);


Route::apiResource(name: 'industry_reports', controller: IndustryReportController::class);

Route::apiResource('case-study', controller: CaseStudyController::class);


Route::apiResource('webinars', WebinarController::class);

// CRUD Routes
Route::post('subscribe', action: [SubscriptionController::class, 'subscribe']);        // Create
Route::get('subscriptions', action: [SubscriptionController::class, 'index']);         // Read all
Route::get('subscriptions/{id}', action: [SubscriptionController::class, 'show']);     // Read single
Route::put('subscriptions/{id}', action: [SubscriptionController::class, 'update']);   // Update
Route::delete('subscriptions/{id}', action: [SubscriptionController::class, 'destroy']);// Delete

// Additional Route
Route::get(uri: 'subscribe', action: [SubscriptionController::class, 'checkSubscription']);  // Check subscription status


Route::post('go-partners-register', [GoPartnersLoginController::class, 'register']);
Route::post('go-partners-verify-email', [GoPartnersLoginController::class, 'verifyEmail']);
Route::post('go-partners-login', action: [GoPartnersLoginController::class, 'login']);
Route::post('go-partners-verify-phone', [GoPartnersLoginController::class, 'verifyPhone']);
Route::middleware('auth:sanctum')->post('upload-document', [GoPartnersLoginController::class, 'uploadDocument']);


Route::apiResource('Videos_url', controller: VideoController::class);

Route::get('dashboard', [DashboardController::class, 'index']);
