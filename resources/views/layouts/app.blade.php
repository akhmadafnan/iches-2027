<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main class="bg-[#f6f8f7] dark:bg-zinc-950">
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
