<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-danger border border-transparent rounded-md font-semibold text-xs text-danger-foreground uppercase tracking-widest hover:bg-danger-hover active:bg-danger focus:outline-none focus:ring-2 focus:ring-danger focus:ring-offset-2 focus:ring-offset-surface transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
