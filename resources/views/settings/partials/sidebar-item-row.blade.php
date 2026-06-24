@php
    use App\Enums\SidebarNavItem;
    use App\Enums\UserRole;
    use App\Services\SidebarService;

    $isCustom = SidebarService::isCustomModuleKey($itemKey);
    $item = $isCustom ? null : SidebarNavItem::tryFrom($itemKey);
    $module = $isCustom ? ($customModules[$itemKey] ?? ['label' => '', 'url' => '']) : null;
    $defaultGroupId = $sidebarService->defaultGroupIdForKey($itemKey);
    $displayLabel = $isCustom
        ? ($module['label'] ?: __('pages.settings.sidebar_custom_module'))
        : ($item ? __($item->navLabelKey()) : $itemKey);
    $displayUrl = $isCustom
        ? ($module['url'] ?? '')
        : ($item?->settingsRoutePath() ?? '');
@endphp

<div
    class="sidebar-item-row menu-tree-node menu-tree-node--item overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"
    data-item="{{ $itemKey }}"
    data-custom="{{ $isCustom ? '1' : '0' }}"
    data-default-group="{{ $defaultGroupId }}"
    draggable="true"
>
    <input type="hidden" name="sidebar_groups[{{ $groupId }}][items][]" value="{{ $itemKey }}">

    <div class="menu-tree-row menu-tree-row--item flex items-center gap-2 px-3 py-2.5">
        <span class="sidebar-item-drag inline-flex shrink-0 cursor-grab items-center justify-center rounded p-1 text-slate-400 hover:bg-slate-100 active:cursor-grabbing" title="{{ __('pages.settings.sidebar_drag') }}">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path d="M7 2a2 2 0 11.001 3.001A2 2 0 017 2zm0 5.5a2 2 0 110 4 2 2 0 010-4zM7 14a2 2 0 110 4 2 2 0 010-4zm6-10.5a2 2 0 110 4 2 2 0 010-4zM13 7.5a2 2 0 110 4 2 2 0 010-4zm0 6.5a2 2 0 110 4 2 2 0 010-4z"/>
            </svg>
        </span>

        <span class="sidebar-item-label min-w-0 flex-1 truncate text-sm font-semibold text-slate-900">{{ $displayLabel }}</span>

        <button
            type="button"
            class="menu-tree-expand inline-flex shrink-0 items-center gap-2 rounded-md px-2 py-1 text-sm text-slate-500 hover:bg-slate-50 hover:text-slate-700"
            aria-expanded="false"
            aria-label="{{ __('pages.settings.sidebar_expand_link') }}"
        >
            <span>{{ __('pages.settings.sidebar_link') }}</span>
            <svg class="menu-tree-chevron h-4 w-4 shrink-0 text-slate-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
    </div>

    <div class="menu-tree-panel menu-tree-panel--item hidden border-t border-slate-200 bg-white px-3 py-3">
        <div class="sidebar-item-edit space-y-3">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('pages.settings.sidebar_field_label') }}</label>
                @if($isCustom)
                    <input
                        type="text"
                        name="sidebar_modules[{{ $itemKey }}][label]"
                        value="{{ $module['label'] ?? '' }}"
                        class="sidebar-module-label w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                        oninput="sidebarSyncModuleUrl(this)"
                        required
                    >
                @else
                    <input
                        type="text"
                        value="{{ $displayLabel }}"
                        class="w-full rounded-md border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700"
                        readonly
                    >
                @endif
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('pages.settings.sidebar_field_url') }}</label>
                @if($isCustom)
                    <input
                        type="text"
                        name="sidebar_modules[{{ $itemKey }}][url]"
                        value="{{ $module['url'] ?? '' }}"
                        class="sidebar-module-url w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                        placeholder="{{ __('pages.settings.sidebar_module_url_auto') }}"
                        data-auto-url="1"
                        data-manual-url="{{ ($module['url'] ?? '') !== '' ? '1' : '0' }}"
                        oninput="sidebarMarkManualUrl(this)"
                        required
                    >
                @else
                    <input
                        type="text"
                        value="{{ $displayUrl }}"
                        class="w-full rounded-md border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700"
                        readonly
                    >
                @endif
            </div>

            @if(! $isCustom)
                <p class="text-xs text-slate-500">{{ __('pages.settings.sidebar_module_system_hint') }}</p>
            @endif

            <div class="flex flex-wrap gap-2 border-t border-slate-100 pt-3">
                <button type="button" class="sidebar-delete-module text-sm font-medium text-red-600 hover:text-red-800">
                    {{ __('pages.settings.sidebar_delete') }}
                </button>
            </div>
        </div>

        <div class="mt-3 border-t border-slate-100 pt-3">
            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('pages.settings.sidebar_visibility_roles') }}</p>
            <div class="flex flex-wrap items-center gap-3">
                @foreach($roles as $role)
                    @php
                        $checked = $sidebarVisibility[$itemKey][$role->value] ?? true;
                        $isSuperAdmin = $role === UserRole::SuperAdmin;
                    @endphp
                    <label class="inline-flex items-center gap-1.5 text-xs text-slate-600">
                        <input
                            type="checkbox"
                            name="sidebar_visibility[{{ $role->value }}][]"
                            value="{{ $itemKey }}"
                            @checked($checked)
                            @disabled($isSuperAdmin)
                            class="h-3.5 w-3.5 rounded border-slate-300 text-teal-700 focus:ring-teal-600 disabled:opacity-60"
                        >
                        <span class="whitespace-nowrap">{{ $role->label() }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>
</div>
