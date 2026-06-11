<aside id="sidebar"
    class="fixed left-0 top-0 h-screen w-64 text-white flex flex-col z-50 overflow-y-auto
           transition-transform duration-300 ease-in-out
           -translate-x-full lg:translate-x-0"
    style="background-color: #1e3c72;">

    <!-- HEADER -->
    <div class="flex flex-col items-center py-5 border-b border-indigo-700">

        <!-- Close Button (Mobile) -->
        <button id="closeSidebar"
            class="lg:hidden absolute top-3 right-3 p-1.5 rounded-md hover:bg-indigo-700/60 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-200"
                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <h1 class="text-sm font-semibold text-center">APPROVAL</h1>
        <p class="text-xs text-indigo-300">Document System</p>
    </div>

    <!-- NAVIGATION -->
    <nav class="px-3 mt-4 flex-1">
        <ul class="space-y-3">

            <!-- Dashboard -->
            <li>
                <details class="group" {{ request()->routeIs('dashboard.*') ? 'open' : '' }}>
                    <summary class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-indigo-700 cursor-pointer">
                        <div class="flex items-center gap-3">
                            <i class="bi bi-speedometer2 text-xl"></i>
                            <span class="font-medium text-sm">Dashboard</span>
                        </div>
                        <i class="bi bi-chevron-right text-xs transition-transform duration-200 group-open:rotate-90"></i>
                    </summary>
                    <ul class="mt-1 ml-6 space-y-1">
                        <li>
                            <a href="{{ route('dashboard.inbox.index') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-indigo-700/60 {{ request()->routeIs('dashboard.inbox.*') ? 'bg-indigo-700/70 font-semibold' : '' }}">
                                Inbox
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('dashboard.report.index') }}"
                                class="block px-3 py-2 rounded-md text-sm hover:bg-indigo-700/60 {{ request()->routeIs('dashboard.report.*') ? 'bg-indigo-700/70 font-semibold' : '' }}">
                                Report
                            </a>
                        </li>
                    </ul>
                </details>
            </li>

            <!-- Master Data -->
            @if (Auth::check() && Auth::user()->hasRole('admin'))
            <li>
                <details class="group" {{ request()->routeIs('admin.master_data.*') ? 'open' : '' }}>
                    <summary class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-indigo-700 cursor-pointer">
                        <div class="flex items-center gap-3">
                            <i class="bi bi-folder2-open text-xl"></i>
                            <span class="font-medium text-sm">Master Data</span>
                        </div>
                        <i class="bi bi-chevron-right text-xs transition-transform duration-200 group-open:rotate-90"></i>
                    </summary>
                    <ul class="mt-1 ml-6 space-y-1">
                        <li><a href="{{ route('admin.master_data.departement.index') }}" class="block px-3 py-2 rounded-md text-sm hover:bg-indigo-700/60">Departements</a></li>
                        <li><a href="{{ route('admin.master_data.position.index') }}" class="block px-3 py-2 rounded-md text-sm hover:bg-indigo-700/60">Positions</a></li>
                        <li><a href="{{ route('admin.master_data.employee.index') }}" class="block px-3 py-2 rounded-md text-sm hover:bg-indigo-700/60">Employees</a></li>
                        <li><a href="{{ route('admin.master_data.document_type.index') }}" class="block px-3 py-2 rounded-md text-sm hover:bg-indigo-700/60">Document Types</a></li>
                        <li><a href="{{ route('admin.master_data.signature.index') }}" class="block px-3 py-2 rounded-md text-sm hover:bg-indigo-700/60">Signature</a></li>
                        <li><a href="{{ route('admin.master_data.user.index') }}" class="block px-3 py-2 rounded-md text-sm hover:bg-indigo-700/60">User</a></li>
                    </ul>
                </details>
            </li>
            @endif

            <!-- Documents -->
            <li>
                <details class="group" {{ request()->routeIs('document.*') ? 'open' : '' }}>
                    <summary class="flex items-center justify-between px-3 py-2 rounded-md hover:bg-indigo-700 cursor-pointer">
                        <div class="flex items-center gap-3">
                            <i class="bi bi-file-earmark-text text-xl"></i>
                            <span class="font-medium text-sm">Documents</span>
                        </div>
                        <i class="bi bi-chevron-right text-xs transition-transform duration-200 group-open:rotate-90"></i>
                    </summary>
                    <ul class="mt-1 ml-6 space-y-1">
                        <li><a href="{{ route('document.index') }}" class="block px-3 py-2 rounded-md text-sm hover:bg-indigo-700/60">List Document</a></li>
                        <li><a target="_blank" href="{{ route('email.document.approval') }}" class="block px-3 py-2 rounded-md text-sm hover:bg-indigo-700/60">(Dev) Email Body</a></li>
                    </ul>
                </details>
            </li>

        </ul>

        <!-- ACCOUNT -->
        <div class="mt-8 py-4 border-t border-indigo-700 text-sm space-y-2">
            <a href="{{ route('profile.edit') }}"
                class="flex items-center gap-2 px-3 py-2 rounded-md text-sm hover:bg-indigo-700/60">
                <i class="bi bi-person-circle text-lg"></i>
                <span>Profile</span>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="flex items-center gap-2 w-full text-left px-3 py-2 rounded-md text-sm hover:bg-red-700/60">
                    <i class="bi bi-box-arrow-right text-lg text-red-300"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </nav>

    <!-- FOOTER -->
    <div class="px-4 py-4 border-t border-indigo-700 text-sm">
        <div class="text-xs text-indigo-300">Signed in as</div>
        <div class="font-medium">{{ auth()->user()?->name ?? 'Guest' }}</div>
    </div>
</aside>
