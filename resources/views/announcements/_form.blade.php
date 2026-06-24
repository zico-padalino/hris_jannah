@php($announcement = $announcement ?? null)
<div class="grid gap-4">
    <div>
        <label class="mb-1 block text-sm font-medium">{{ __('pages.announcements.branch') }}</label>
        <select name="branch_id" class="w-full rounded-lg border border-slate-300 px-3 py-2">
            <option value="">{{ __('pages.announcements.all_branches') }}</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}" @selected(old('branch_id', optional($announcement)->branch_id) == $branch->id)>
                    {{ $branch->name }}
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-slate-500">{{ __('pages.announcements.branch_hint') }}</p>
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">{{ __('pages.announcements.field_title') }}</label>
        <input name="title" value="{{ old('title', optional($announcement)->title) }}" required maxlength="255" placeholder="{{ __('pages.announcements.title_placeholder') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">{{ __('pages.announcements.field_content') }}</label>
        <textarea name="content" rows="5" required maxlength="5000" placeholder="{{ __('pages.announcements.content_placeholder') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">{{ old('content', optional($announcement)->content) }}</textarea>
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="mb-1 block text-sm font-medium">{{ __('pages.announcements.field_starts_at') }}</label>
            <input name="starts_at" type="date" value="{{ old('starts_at', optional($announcement)->starts_at?->format('Y-m-d')) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">{{ __('pages.announcements.field_ends_at') }}</label>
            <input name="ends_at" type="date" value="{{ old('ends_at', optional($announcement)->ends_at?->format('Y-m-d')) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
        </div>
    </div>
    <p class="text-xs text-slate-500">{{ __('pages.announcements.period_hint') }}</p>
    <div>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', optional($announcement)->is_active ?? true)) class="rounded border-slate-300">
            {{ __('pages.announcements.field_active') }}
        </label>
    </div>
</div>
