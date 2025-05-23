<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Leaderboard</title>
    <link rel="stylesheet" href="{{ asset('css/leaderboard.css') }}">
</head>

<body>
    <div class="container" style="max-width: 800px; margin: 20px auto; padding: 0 15px;">
        <form method="GET" action="{{ route('leaderboard.index') }}" class="filter-form">
            <select name="filter" onchange="this.form.submit()" class="filter-select">
                <option value="all" {{ request('filter') == 'all' ? 'selected' : '' }}>All</option>
                <option value="day" {{ request('filter') == 'day' ? 'selected' : '' }}>Today</option>
                <option value="month" {{ request('filter') == 'month' ? 'selected' : '' }}>This Month</option>
                <option value="year" {{ request('filter') == 'year' ? 'selected' : '' }}>This Year</option>
            </select>

            <input type="text" name="search" placeholder="Search by User ID" value="{{ request('search') }}"
                class="search-input" />
            <button type="submit" class="search-btn">Search</button>
        </form>

        <form method="POST" action="{{ route('leaderboard.recalculate') }}" class="recalculate-form">
            @csrf
            <button type="submit" class="recalculate-btn">Recalculate</button>
        </form>

        <table class="leaderboard-table">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Total Points</th>
                    <th>Rank</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($leaderboards as $entry)
                    <tr class="{{ request('search') && request('search') == $entry->user->id ? 'highlighted' : '' }}">
                        
                        <td class="entry-id">{{ $entry->user->id }}</td>
                        <td class="entry-name">{{ $entry->user->name }}</td>
                        <td class="entry-points">{{ $entry->total_points }}</td>
                        <td
                            class="entry-rank">
                            {{ $entry->rank }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>