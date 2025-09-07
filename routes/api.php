<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\JobApplicationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\FavoriteJobController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\FirebaseNotificationController;


// Public routes
Route::get('/app-version', [AuthController::class, 'getVersion']);
Route::post('/register', [AuthController::class, 'authentication']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forget-password', [AuthController::class, 'forgetPassword']);
Route::post('/verify-email-otp', [AuthController::class, 'verifyEmailOtp']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');

Route::post('/test-tranzila', [PaymentController::class, 'testPayment']);

// Protected routes (requires authentication)
Route::middleware('auth:api')->group(function () {

// Routes for onboarding
    Route::post('/onboarding-person', [AuthController::class, 'onboardingPerson']);
    Route::post('/onboarding-business', [AuthController::class, 'onboardingBusiness']);

    Route::post('/add-bank', [AuthController::class, 'updateBankDetails']);

    Route::get('/home', [HomeController::class, 'homeJobs']);
    Route::post('/update-device-token', [HomeController::class, 'updateToken']);
    Route::get('/filtered-by-name', [HomeController::class, 'filteredJobsKeywords']);
    Route::get('/filtered-jobs', [HomeController::class, 'filteredJobs']);

    //////////////////////////POST///////////////////////////////
    Route::post('/job-post', [PostController::class, 'store']);
    Route::post('/job-post-edit', [PostController::class, 'update']);
    Route::post('/job-end/{postId}', [PostController::class, 'jobEnd']);
    Route::post('/job-delete/{postId}', [PostController::class, 'jobDelete']);
    Route::get('/job-details/{id}', [PostController::class, 'show']);

    ///////////////////////////Job Applications/////////////////
    Route::post('/apply-for-job/{postId}', [JobApplicationController::class, 'apply']);
    Route::post('/job-request-action/{jobId}', [JobApplicationController::class, 'jobRequestAction']);

    Route::get('/job-candidate-profile/{jobId}', [JobApplicationController::class, 'jobCandidateProfile']);

    Route::get('/user-works', [JobApplicationController::class, 'userWorks']);
    Route::get('/user-work-history', [JobApplicationController::class, 'userWorkHistory']);

    Route::get('/business-jobs', [JobApplicationController::class, 'businessJobPosts']);
    Route::get('/business-jobs-history', [JobApplicationController::class, 'businessJobPostsHistory']);

    Route::get('/business-job-candidates/{jobId}', [JobApplicationController::class, 'businessJobCandidates']);
    Route::get('/job-candidate-application/{applicationId}', [JobApplicationController::class, 'businessJobApplication']);

    ///////////////////////////////Profiles///////////////////////////////
    Route::get('/user-profile/{user_id?}', [ProfileController::class, 'getUserProfile']);
    Route::post('/profile-update', [ProfileController::class, 'profileUpdate']);
    Route::post('/toggle-notification', [ProfileController::class, 'toggleNotification']);

    ///////////////////////////////Favourits///////////////////////////////
    Route::post('/toggle-favorite-job/{postId}', [FavoriteJobController::class, 'toggleFavoriteJob']);
    Route::get('/user-favorite-job', [FavoriteJobController::class, 'userFavoriteJob']);

    ///////////////////////////////Reviews////////////////////////////////////
    Route::post('/person-submit-review/{postId}', [ReviewController::class, 'personStoreReview']);
    Route::post('/business-submit-review', [ReviewController::class, 'businessStoreReview']);

    Route::post('/switch-user', [HomeController::class, 'switchUserType']);

    Route::post('/create-room', [\App\Http\Controllers\ChatController::class, 'createRoom']);
    Route::post('/chats-list', [\App\Http\Controllers\ChatController::class, 'ChatsList']);
    Route::post('/chat-history', [\App\Http\Controllers\ChatController::class, 'ChatHistory']);
    Route::post('/chat-upload-file', [\App\Http\Controllers\ChatController::class, 'uploadFile']);


    Route::post('/send-notification', [\App\Http\Controllers\SendNotificationController::class, 'sendNotification']);
    Route::get('/get-notification', [\App\Http\Controllers\SendNotificationController::class, 'getNotification']);


    Route::post('/inactive-my-account', [AuthController::class, 'inactiveAccount']);
    Route::post('/delete-account', [AuthController::class, 'DeleteAccount']);

    Route::post('/payment/process', [PaymentController::class, 'Jobpayment']);
    Route::post('/my-card-detail/', [PaymentController::class, 'myCardDetails']);

    Route::post('/contact-us', [HomeController::class, 'contactReport']);


//working on red lines review
});



