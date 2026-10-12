<div class="drawer-side z-50">
    <!-- Diese Label-Zeile ist wichtig, damit man die Sidebar auf Handys per Klick ins Leere schließen kann -->
    <label for="my-drawer-2" aria-label="close sidebar" class="drawer-overlay"></label>

    <!-- Das Hauptmenü (bg-base-200 macht es leicht grau, passend zum Theme) -->
    <ul class="menu p-4 w-64 min-h-full bg-base-200 text-base-content border-r border-base-300">

        <!-- Die Überschrift -->
        <li class="mb-4 text-xl font-bold px-4 py-2 border-b border-base-300 pb-4">Menü</li>

        <!-- Die Navigationspunkte -->
        <li>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                Dashboard
            </a>
        </li>
        <li>
            <a href="{{ route('alarms.index') }}" class="{{ request()->routeIs('alarms.index') ? 'active' : '' }}">
                Meine Alarme
            </a>
        </li>
        <li>
            <!-- target="_blank" öffnet das Portal in einem neuen Tab -->
            <a href="https://portal.conix.at" target="_blank" rel="noopener noreferrer">
                OpenDesk
            </a>
        </li>
    </ul>
</div>
