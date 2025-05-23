<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Leaderboard;
use App\Models\User;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->input('filter');
        $searchId = $request->input('search');

        $activities = Activity::query();

        if ($filter == 'day') {
            $activities->whereDate('performed_at', today());
        } elseif ($filter == 'month') {
            $activities->whereMonth('performed_at', now()->month)
                ->whereYear('performed_at', now()->year);
        } elseif ($filter == 'year') {
            $activities->whereYear('performed_at', now()->year);
        }

        // Get total points per user
        $totals = [];
        foreach ($activities->get() as $activity) {
            $totals[$activity->user_id] = ($totals[$activity->user_id] ?? 0) + $activity->points;
        }

        // Sort users by total points
        arsort($totals);

        // Assign ranks with ties
        $ranked = [];
        $rank = 1;
        $prevPoints = null;
        $actualRank = 0;

        foreach ($totals as $userId => $totalPoints) {
            if ($prevPoints !== $totalPoints) {
                $actualRank = $rank;
            }

            $user = User::find($userId);
            if ($user) {
                Leaderboard::updateOrCreate(
                    ['user_id' => $userId],
                    ['total_points' => $totalPoints, 'rank' => $actualRank]
                );
            }

            $prevPoints = $totalPoints;
            $rank++;
        }

        // Prepare leaderboard for view
        $leaderboards = Leaderboard::with('user')->orderBy('rank');
        

        if ($searchId) {
            $leaderboards = $leaderboards->get()->sortBy(function ($entry) use ($searchId) {
                return $entry->user_id == $searchId ? -1 : $entry->rank;
            });
        } else {
            $leaderboards = $leaderboards->get();
        }

        return view('leaderboard', compact('leaderboards'));
    }

    public function recalculate(Request $request)
    {
        // Just redirect to index to perform recalculation inline
        return redirect()->route('leaderboard.index')->with('message', 'Leaderboard recalculated.');
    }
}
