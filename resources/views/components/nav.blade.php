@auth
    @php
        // Holt alle Alarme, die für den eingeloggten User ausgelöst wurden
        $triggeredAlarms = \App\Models\Alarm::where('user_id', auth()->id())
            ->where('recipient_status', 'triggered')
            ->with('watchedUser')
            ->get();
    @endphp
@endauth

<div class="navbar bg-base-300 w-full shadow-sm z-50">
    <!-- Hamburger Menü (Nur auf Handys sichtbar) -->
    <div class="flex-none lg:hidden">
        <label for="my-drawer-2" aria-label="open sidebar" class="btn btn-square btn-ghost drawer-button">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block h-6 w-6 stroke-current">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </label>
    </div>

    <div class="mx-2 flex-1 px-2 font-bold text-lg">Opendesk</div>

    <!-- Die Navbar Items -->
    <div class="hidden flex-none lg:block">
        <ul class="menu menu-horizontal px-1 gap-2 z-50">

            <!-- Navbar Item 1: Benachrichtigungen (Glocke) -->
            @auth
                <li>
                    <details>
                        <summary class="relative">
                            <!-- Die Glocke (SVG) -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>

                            <!-- Roter Badge nur anzeigen, wenn es Alarme gibt -->
                            @if($triggeredAlarms->count() > 0)
                                <span class="badge badge-xs badge-error absolute top-2 right-2 indicator-item">{{ $triggeredAlarms->count() }}</span>
                            @endif
                        </summary>
                        <ul class="p-2 bg-base-200 rounded-t-none w-72 shadow-lg right-0 mt-3 z-[100]">
                            <li class="menu-title">Ausgelöste Alarme</li>

                            <!-- Dynamische Liste der Alarme -->
                            @forelse($triggeredAlarms as $alarm)
                                <li class="flex flex-row justify-between items-center pr-2">
                                    <a class="flex flex-col items-start flex-1 hover:bg-transparent cursor-default pointer-events-none">
                                        <span class="font-semibold text-sm">{{ $alarm->watchedUser->username }}</span>
                                        <span class="text-xs text-success">Ist jetzt ONLINE</span>
                                    </a>

                                    <!-- OK-Button zum Löschen/Wegklicken des Alarms -->
                                    <form action="{{ route('alarms.destroy', $alarm->id) }}" method="POST" class="m-0 p-0 ml-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-outline btn-success">OK</button>
                                    </form>
                                </li>
                            @empty
                                <!-- Das wird angezeigt, wenn alles ruhig ist -->
                                <li>
                                    <a class="text-gray-500 text-sm hover:bg-transparent cursor-default">
                                        Keine neuen Alarme
                                    </a>
                                </li>
                            @endforelse

                        </ul>
                    </details>
                </li>

                <!-- Navbar Item 2: Profil & Logout -->
                <li>
                    <details>
                        <summary>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            {{ auth()->user()->username ?? 'Profil' }}
                        </summary>
                        <ul class="p-2 bg-base-200 rounded-t-none w-56 shadow-lg right-0 mt-3 z-[100]">
                            <li class="menu-title">Mein Profil</li>

                            <!-- Rolle aus dem ERM -->
                            <li class="pointer-events-none">
                                <a class="flex flex-col items-start">
                                    <span class="text-xs text-gray-400">Rolle</span>
                                    <span class="font-medium">{{ auth()->user()->role ?? 'Benutzer' }}</span>
                                </a>
                            </li>

                            <div class="divider my-1"></div>

                            <!-- Logout -->
                            <li>
                                <form action="/logout" method="POST" class="m-0 p-0 block w-full">
                                    @csrf
                                    <button type="submit" class="text-error w-full text-left px-4 py-2 hover:bg-base-300 rounded-lg">
                                        Abmelden
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </details>
                </li>
            @endauth

        </ul>
    </div>
</div>
