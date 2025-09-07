<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\Post;
use App\Models\Review;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    public function personStoreReview(Request $request, $postId)
    {
        DB::beginTransaction();  // Start a DB transaction

        try {
            $user = auth()->user();
            $post = Post::find($postId);

            //setting type if reviewing user is person then 0 if business then 1
            if ($user->type == 'person'){
                $reviewType = '0';
            }else{
                return response()->json([
                    'status' => 404,
                    'message' => 'Business can not review a Business',
                    'data' => (object)[],
                ], 404);
            }

            if (!$post) {
                return response()->json([
                    'status' => 404,
                    'message' => 'The specified job post does not exist.',
                    'data' => (object)[],
                ], 404);
            }

            // Ensure the job is finished before reviewing
            if ($post->status != '0') {
                return response()->json([
                    'status' => 400,
                    'message' => 'You can only submit a review after the job is marked as finished.',
                    'data' => (object)[],
                ], 400);
            }

            // Validate the review input
            $validator = Validator::make($request->all(), [
                'stars' => 'required|numeric|min:0|max:5',
                'comment' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 422,
                    'message' => $validator->errors()->first(),
                    'data' => (object)[],
                ], 422);
            }

            $validated = $validator->validated();

            // Check if the user has already reviewed this job
            if (Review::where('user_id', $user->id)->where('post_id', $postId)->exists()) {
                return response()->json([
                    'status' => 400,
                    'message' => 'You have already reviewed this job.',
                    'data' => (object)[],
                ], 400);
            }

            $reviewed_user = User::find($post->user_id);
            if (!$reviewed_user) {
                return response()->json([
                    'status' => 404,
                    'message' => 'The user associated with this job does not exist.',
                    'data' => (object)[],
                ], 404);
            }


            // Create the review
            $review = Review::create([
                'type' => $reviewType,
                'user_id' => $user->id,
                'post_id' => $postId,
                'reviewed_user_id' => $reviewed_user->id,
                'stars' => $validated['stars'],
                'comment' => $validated['comment'] ?? null,
            ]);

            // Calculate and update the user's average rating
            $newAvgRating = $this->calculateAverageRating($reviewed_user->id, $validated['stars']);

            // Save the updated user average rating
            $reviewed_user->avg_rating = $newAvgRating;
            $reviewed_user->save();

            $reviewed_user->increment('rating_count');


            // Commit the transaction
            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Review submitted successfully.',
                'data' => [
                    'review' => $review,
                    'avg_rating' => $reviewed_user->avg_rating,
                ],
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();  // Rollback the transaction on error

            Log::error("Error submitting job review for post $postId by user $user->id: " . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'An error occurred while submitting your review. Please try again.',
                'data' => (object)[],
            ], 500);
        }
    }

    public function businessStoreReview(Request $request)
    {
        DB::beginTransaction();

        try {
            $user = auth()->user();

            // Validate the incoming request
            $validator = Validator::make($request->all(), [
                'post_id' => 'required|exists:posts,id',
                'reviewed_user_id' => 'required|exists:users,id',
                'stars' => 'required|numeric|min:0|max:5',
                'comment' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 422,
                    'message' => $validator->errors()->first(),
                    'data' => (object)[],
                ], 422);
            }

            $validated = $validator->validated();

            $post = Post::find($validated['post_id']);
            $reviewedUser = User::find($validated['reviewed_user_id']);

            if (!$post || !$reviewedUser) {
                return response()->json([
                    'status' => 404,
                    'message' => 'The specified post or user does not exist.',
                    'data' => (object)[],
                ], 404);
            }

            // Ensure the job is finished before reviewing
            if ($post->status != '0') {
                return response()->json([
                    'status' => 400,
                    'message' => 'You can only submit a review after the job is marked as finished.',
                    'data' => (object)[],
                ], 400);
            }

            // Check if the business has already reviewed this person for the same post
            if (Review::where('user_id', $user->id)
                ->where('post_id', $validated['post_id'])
                ->where('reviewed_user_id', $validated['reviewed_user_id'])
                ->exists()) {
                return response()->json([
                    'status' => 400,
                    'message' => 'You have already reviewed this user for the specified job.',
                    'data' => (object)[],
                ], 400);
            }

            // Determine the review type (1 for business)
            $reviewType = '1';

            // Create the review
            $review = Review::create([
                'type' => $reviewType,
                'user_id' => $user->id,
                'post_id' => $validated['post_id'],
                'reviewed_user_id' => $validated['reviewed_user_id'],
                'stars' => $validated['stars'],
                'comment' => $validated['comment'] ?? null,
            ]);

            // Calculate and update the reviewed user's average rating
            $newAvgRating = $this->calculateAverageRating($validated['reviewed_user_id'], $validated['stars']);
            $reviewedUser->avg_rating = $newAvgRating;
            $reviewedUser->save();

            $reviewedUser->increment('rating_count');


            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Review submitted successfully.',
                'data' => [
                    'review' => $review,
                    'avg_rating' => $reviewedUser->avg_rating,
                ],
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Error submitting business review for post {$request->post_id} by user {$user->id}: " . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'An error occurred while submitting your review. Please try again.',
                'data' => (object)[],
            ], 500);
        }
    }
    /**
     * Calculate average rating for a reviewed user.
     */
    protected function calculateAverageRating($reviewedUserId)
    {
        $totalStars = Review::where('reviewed_user_id', $reviewedUserId)->sum('stars');
        $totalReviews = Review::where('reviewed_user_id', $reviewedUserId)->count();

        return $totalReviews > 0 ? round($totalStars / $totalReviews, 2) : 0;
    }

}
