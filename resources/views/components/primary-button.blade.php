<button
    {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center text-center px-4 py-2 bg-primary-600 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-500 active:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:ring-offset-slate-800 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
