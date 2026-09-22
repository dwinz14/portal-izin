<footer
    class="mt-auto flex-shrink-0 bg-white/75 dark:bg-slate-800/80 rounded-tl-xl backdrop-blur-xl shadow-md border-t border-gray-200/80 dark:border-gray-700/70 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 py-4">
        <div
            class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500 dark:text-gray-400">
            {{-- Left Section: Copyright & App Name --}}
            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-1.5 text-center sm:text-left">
                <span>&copy; {{ date('Y') }}</span>
                <span
                    class="font-semibold text-gray-700 dark:text-gray-200 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                    JustBoyz
                </span>
                <span class="hidden sm:inline text-gray-300 dark:text-gray-600">•</span>
                <span class="text-gray-500 dark:text-gray-400">All rights reserved.</span>
            </div>

            {{-- Right Section: Version & Status Badge --}}
            <div class="flex items-center gap-2.5">
                <span
                    class="font-semibold text-gray-700 dark:text-gray-200 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                    {{ config('app.name', 'SIMIKA') }}
                </span>
                <span
                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium font-mono bg-slate-100 dark:bg-slate-700/70 text-slate-600 dark:text-slate-300 border border-slate-200/90 dark:border-slate-600/60 shadow-xs">
                    <span
                        class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500/50"></span>
                    <span>V Beta-{{ config('app.version', '1.0.0') }}</span>
                </span>
            </div>
        </div>
    </div>
</footer>
