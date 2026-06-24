@if($announcements->isNotEmpty())
    <section class="dashboard-announcements mb-8 overflow-hidden rounded-2xl border-2 shadow-sm" style="background-color: var(--app-surface); border-color: var(--app-border);">
        <div class="flex items-center justify-between border-b-2 px-5 py-4 sm:px-6" style="border-color: var(--app-border); background-color: var(--app-surface-muted);">
            <div>
                <h2 class="text-lg font-extrabold" style="color: var(--app-text);">{{ __('pages.dashboard.announcements_title') }}</h2>
            </div>
            <span class="inline-flex h-9 min-w-9 items-center justify-center rounded-full px-3 text-sm font-bold" style="background-color: var(--app-primary-soft); color: var(--app-primary-soft-text);">
                {{ $announcements->count() }}
            </span>
        </div>

        <div class="divide-y-2" style="border-color: var(--app-border);">
            @foreach($announcements as $announcement)
                <article class="px-5 py-4 sm:px-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base font-extrabold" style="color: var(--app-text);">{{ $announcement->title }}</h3>
                            <p class="mt-2 whitespace-pre-line text-sm leading-relaxed app-muted-text">{{ $announcement->content }}</p>
                        </div>
                        <div class="shrink-0 text-right text-sm font-semibold sm:text-base app-muted-text">
                            <p>{{ $announcement->starts_at->translatedFormat('d M Y') }} – {{ $announcement->ends_at->translatedFormat('d M Y') }}</p>
                            @if($announcement->branch)
                                <p class="mt-1">{{ $announcement->branch->name }}</p>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endif
