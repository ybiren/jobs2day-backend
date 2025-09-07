<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FavoriteJob;
use App\Models\JobApplication;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteJobController extends Controller
{
    public function toggleFavoriteJob(Request $request, $postId)
    {
        try {
            // Check if the post exists
            $postExists = Post::find($postId);
            if (!$postExists) {
                return response()->json([
                    'status' => 404,
                    'message' => 'לא נמצא משרה מתאימה',
                    'data' => (object)[],
                ], 404);
            }

            // Get the authenticated user
            $user = auth()->user();

            // Check if the job is already in the user's favorites
            $favoriteJob = FavoriteJob::where('user_id', $user->id)
                ->where('post_id', $postId)
                ->first();

            if ($favoriteJob) {
                // If found, remove it
                $favoriteJob->delete();

                return response()->json([
                    'status' => 200,
                    'message' => 'Job removed from favorites',
                    'data' => (object)[],
                ], 200);
            } else {
                // Otherwise, add it
                FavoriteJob::create([
                    'user_id' => $user->id,
                    'post_id' => $postId,
                    'type' => $request->type ?? 'default', // Optional type
                ]);

                return response()->json([
                    'status' => 200,
                    'message' => 'Job added to favorites',
                    'data' => (object)[],
                ], 200);
            }
        } catch (\Exception $e) {
            // Handle any errors
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred while toggling favorite job',
                'data' => (object)[],
            ], 500);
        }
    }

    public function userFavoriteJob()
    {
        try {
            // Get the authenticated user
            $user = auth()->user();

            // Fetch all favorite jobs for the user, including related post and user details
            $favoriteJobs = FavoriteJob::where('user_id', $user->id)
                ->orderBy('id', 'desc')
                ->with(['post.user', 'post.availabilitySpecificDates', 'post.availabilityDays']) // Load necessary relationships
                ->get();

            if ($favoriteJobs->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No favorite jobs found',
                    'data' => [],
                ], 404);
            }

            // Transform the data
            $transformedJobs = $favoriteJobs->map(function ($favoriteJob) {
                $post = $favoriteJob->post;

                if ($post) { // Ensure post exists
                    // Map availability_specific_dates to availability_dates array
                    $post->availability_dates = $post->availabilitySpecificDates->pluck('availability_date')->toArray();
                    unset($post->availabilitySpecificDates);

                    // Convert availability days to a list format
                    $availabilityDays = [];
                    if ($post->availability == 0 && $post->availabilityDays) {
                        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
                            if ($post->availabilityDays->$day == "1") {
                                $availabilityDays[] = $day;
                            }
                        }
                    }
                    $post->availability_days = $availabilityDays; // Now in list format
                    unset($post->availabilityDays);

                    $post->is_favorite = 1;
                }

                return $post;
            });

            return response()->json([
                'status' => 200,
                'message' => 'Favorite jobs retrieved successfully',
                'data' => $transformedJobs,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred while retrieving favorite jobs',
                'data' => (object)[],
            ], 500);
        }
    }

}
