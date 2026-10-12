<x-layout title="Dashboard">
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-6">Team Übersicht</h1>

        <!-- Ein responsives Grid: 1 Spalte auf Handy, 2 auf Tablet, 3-4 auf großen Bildschirmen -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">

            @foreach($teamMembers as $user)
                @php
                    // Prüfen, ob der User online ist (wie in der Alarm-Logik)
                    $lastSeen = $user->onlineStatus->first()?->last_seen_at;
                    $isOnline = $lastSeen && \Carbon\Carbon::parse($lastSeen)->addMinutes(5) >= now();
                @endphp

                    <!-- Deine angepasste DaisyUI Card -->
                <div class="card bg-base-100 card-sm shadow-md border border-base-200">
                    <div class="card-body">

                        <!-- Titelzeile: Username + Online-Punkt -->
                        <h2 class="card-title flex justify-between items-center">
                            {{ $user->username }}

                            <!-- Kleiner Status-Indikator (Grün/Rot) -->
                            <div class="h-3 w-3 rounded-full {{ $isOnline ? 'bg-success' : 'bg-error' }}"
                                 title="{{ $isOnline ? 'Online' : 'Offline' }}">
                            </div>
                        </h2>

                        <!-- Informationen -->
                        <div class="text-sm text-gray-500 mb-2">
                            {{ $user->email }}
                        </div>

                        <div class="flex flex-col gap-1 mt-2 text-sm">
                            <div class="flex justify-between">
                                <span class="font-semibold text-gray-600">Rolle:</span>
                                <span>{{ ucfirst($user->role) }}</span>
                            </div>

                            <div class="flex justify-between">
                                <span class="font-semibold text-gray-600">Team:</span>
                                <span>{{ $user->team->name ?? 'Kein Team' }}</span>
                            </div>

                            <div class="flex justify-between">
                                <span class="font-semibold text-gray-600">Zuletzt gesehen:</span>
                                <span>{{ $lastSeen ? \Carbon\Carbon::parse($lastSeen)->diffForHumans() : 'Nie' }}</span>
                            </div>
                        </div>

                    </div>
                </div>
            @endforeach

        </div>
    </div>
</x-layout>
