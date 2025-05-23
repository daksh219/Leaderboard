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

        $totals = [];
        foreach ($activities->get() as $activity) {
            $totals[$activity->user_id] = ($totals[$activity->user_id] ?? 0) + $activity->points;
        }

        if (empty($totals)) {

            $leaderboards = collect();
            return view('leaderboard', compact('leaderboards'));
        }

        $todayPoints = [];
        $todayActivities = Activity::whereDate('performed_at', today())->get();
        foreach ($todayActivities as $activity) {
            $todayPoints[$activity->user_id] = ($todayPoints[$activity->user_id] ?? 0) + $activity->points;
        }

        $monthPoints = [];
        $monthActivities = Activity::whereMonth('performed_at', now()->month)
            ->whereYear('performed_at', now()->year)
            ->get();
        foreach ($monthActivities as $activity) {
            $monthPoints[$activity->user_id] = ($monthPoints[$activity->user_id] ?? 0) + $activity->points;
        }

        uksort($totals, function ($a, $b) use ($totals, $todayPoints, $monthPoints) {

            if ($totals[$a] != $totals[$b]) {
                return $totals[$b] - $totals[$a];
            }
            

            $todayA = $todayPoints[$a] ?? 0;
            $todayB = $todayPoints[$b] ?? 0;
            if ($todayA != $todayB) {
                return $todayB - $todayA; 
            }

            $monthA = $monthPoints[$a] ?? 0;
            $monthB = $monthPoints[$b] ?? 0;
            return $monthB - $monthA; 
        });

        $currentRank = 1;
        $prevTotalPoints = null;

        foreach ($totals as $userId => $totalPoints) {

            if ($prevTotalPoints !== null && $totalPoints != $prevTotalPoints) {
                $currentRank++;
            }

            $user = User::find($userId);
            if ($user) {
                Leaderboard::updateOrCreate(
                    ['user_id' => $userId],
                    ['total_points' => $totalPoints, 'rank' => $currentRank]
                );
            }

            $prevTotalPoints = $totalPoints;
        }

        $leaderboardsQuery = Leaderboard::with('user')->orderBy('rank');

        if ($searchId) {

            $leaderboards = $leaderboardsQuery->get()->sortBy(function ($entry) use ($searchId) {
                return $entry->user_id == $searchId ? -1 : $entry->rank;
            });
        } else {

            $leaderboards = $leaderboardsQuery->get();
        }

        return view('leaderboard', compact('leaderboards'));
    }


    public function recalculate(Request $request)
    {

        return redirect()->route('leaderboard.index')->with('message', 'Leaderboard recalculated.');
    }
}
