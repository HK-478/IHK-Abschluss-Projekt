<x-layout title="Alarm setzen">
    <h2>Mein Team</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table">
        <thead>
        <tr>
            <th>Benutzername</th>
            <th>Status</th>
            <th>Zuletzt gesehen</th>
            <th>Aktion</th>
        </tr>
        </thead>
        <tbody>
        @foreach($teamMembers as $member)
            @php
                $latestStatus = $member->onlineStatus->first();
            @endphp
            <tr>
                <td>{{ $member->username }}</td>
                <td>
                    {{ $latestStatus ? $latestStatus->status : 'Unbekannt' }}
                </td>
                <td>
                    {{ $latestStatus && $latestStatus->last_seen_at ? $latestStatus->last_seen_at->diffForHumans() : '-' }}
                </td>
                <td>
                    {{-- Formular zum Erstellen eines Alarms --}}
                    <form action="{{ route('alarms.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="watched_user_id" value="{{ $member->id }}">
                        <button type="submit" class="btn btn-sm btn-primary">
                            Alarm setzen
                        </button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</x-layout>
