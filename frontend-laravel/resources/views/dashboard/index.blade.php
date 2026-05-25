<x-layouts.app title="Dashboard Lab Asset">
    <div class="app-shell">
        <header class="topbar">
            <div class="container topbar-inner">
                <div>
                    <p class="page-kicker">Lab Asset</p>
                    <h1>Dashboard {{ $dashboard['role_label'] ?? ($user['role_label'] ?? '') }}</h1>
                </div>

                <div class="user-menu">
                    <div class="user-copy">
                        <p>{{ $user['name'] ?? 'User' }}</p>
                        <span>{{ $user['email'] ?? '' }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="button-secondary">Logout</button>
                    </form>
                </div>
            </div>
        </header>

        <main class="container content">
            @if ($error)
                <div class="notice danger">{{ $error }}</div>
            @else
                <section class="dashboard-intro">
                    <p>{{ $dashboard['description'] }}</p>
                    <div class="action-tags">
                        @foreach ($dashboard['actions'] as $action)
                            <span>{{ $action }}</span>
                        @endforeach
                    </div>
                </section>

                <section class="stats-grid">
                    @foreach ($dashboard['stats'] as $stat)
                        <div class="stat-card">
                            <p>{{ $stat['label'] }}</p>
                            <strong>{{ $stat['value'] }}</strong>
                        </div>
                    @endforeach
                </section>

                <section class="panel-grid">
                    @foreach ($dashboard['sections'] as $section)
                        <div class="data-panel">
                            <div class="panel-header">
                                <h2>{{ $section['title'] }}</h2>
                            </div>
                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr>
                                            @foreach ($section['columns'] as $column)
                                                <th>{{ $column }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($section['rows'] as $row)
                                            <tr>
                                                @foreach ($row as $cell)
                                                    <td>{{ $cell ?: '-' }}</td>
                                                @endforeach
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ count($section['columns']) }}" class="empty-cell">Belum ada data.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </section>
            @endif
        </main>
    </div>
</x-layouts.app>
