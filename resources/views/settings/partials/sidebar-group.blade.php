@php
    use App\Services\SidebarService;

    $groupId = $group['id'];
    $isCustomGroup = str_starts_with($groupId, 'custom_');
    $isDashboard = ($group['builtin'] ?? null) === 'dashboard';
    $displayLabel = $sidebarService->groupLabel($group) ?: ($isDashboard
        ? __('nav.dashboard')
        : ($isCustomGroup ? __('pages.settings.sidebar_custom_group') : __('pages.settings.sidebar_group')));
@endphp

<div
    class="sidebar-group-card menu-tree-node menu-tree-node--group"
    data-group-id="{{ $groupId }}"
    data-custom="{{ $isCustomGroup ? '1' : '0' }}"
    data-dashboard="{{ $isDashboard ? '1' : '0' }}"
    draggable="true"
>
    <input type="hidden" name="sidebar_groups_order[]" value="{{ $groupId }}">
    @if(! empty($group['builtin']))
        <input type="hidden" name="sidebar_groups[{{ $groupId }}][builtin]" value="{{ $group['builtin'] }}">
    @endif

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="menu-tree-row menu-tree-row--group flex items-center gap-2 px-3 py-2.5">
            <span class="sidebar-drag-handle inline-flex shrink-0 cursor-grab items-center justify-center rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 active:cursor-grabbing" title="{{ __('pages.settings.sidebar_drag_group') }}">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path d="M7 2a2 2 0 11.001 3.001A2 2 0 017 2zm0 5.5a2 2 0 110 4 2 2 0 010-4zM7 14a2 2 0 110 4 2 2 0 010-4zm6-10.5a2 2 0 110 4 2 2 0 010-4zM13 7.5a2 2 0 110 4 2 2 0 010-4zm0 6.5a2 2 0 110 4 2 2 0 010-4z"/>
                </svg>
            </span>

            <p class="sidebar-group-label-display min-w-0 flex-1 truncate text-sm font-semibold text-slate-900">{{ $displayLabel }}</p>

            <button
                type="button"
                class="menu-tree-expand inline-flex shrink-0 items-center gap-2 rounded-md px-2 py-1 text-sm text-slate-500 hover:bg-slate-50 hover:text-slate-700"
                aria-expanded="false"
                aria-label="{{ __('pages.settings.sidebar_expand_group') }}"
            >
                <span>{{ __('pages.settings.sidebar_group') }}</span>
                <svg class="menu-tree-chevron h-4 w-4 shrink-0 text-slate-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>

        <div class="menu-tree-panel menu-tree-panel--group hidden border-t border-slate-200 px-3 py-3">
            <div class="sidebar-group-edit space-y-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('pages.settings.sidebar_field_label') }}</label>
                    <input
                        type="text"
                        name="sidebar_groups[{{ $groupId }}][label]"
                        value="{{ $group['label'] ?? '' }}"
                        placeholder="{{ $isDashboard ? __('nav.dashboard') : __('pages.settings.sidebar_group_name') }}"
                        class="sidebar-group-label-input w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                        @disabled($isDashboard)
                    >
                </div>

                @if(! $isDashboard)
                    <button
                        type="button"
                        class="sidebar-delete-group text-sm font-medium text-red-600 hover:text-red-800"
                    >
                        {{ __('pages.settings.sidebar_delete') }}
                    </button>
                @endif
            </div>

            <details class="sidebar-add-module-details mt-3 border-t border-slate-100 pt-3">
                <summary class="sidebar-toggle-add-module inline-flex cursor-pointer list-none items-center gap-2 rounded-lg border border-teal-600 bg-teal-50 px-3 py-1.5 text-sm font-semibold text-teal-900 hover:bg-teal-100 [&::-webkit-details-marker]:hidden">
                    <span aria-hidden="true">+</span>
                    <span>{{ __('pages.settings.sidebar_add_module') }}</span>
                </summary>

                <div class="sidebar-add-module-panel mt-3 space-y-3 rounded-lg border border-dashed border-teal-200 bg-teal-50/50 p-3">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-600">{{ __('pages.settings.sidebar_add_system_module') }}</label>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <select class="sidebar-pick-builtin min-w-[12rem] flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">{{ __('pages.settings.sidebar_pick_module') }}</option>
                                @foreach($builtinModuleOptions as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="sidebar-add-builtin-module rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">
                                {{ __('pages.settings.sidebar_add') }}
                            </button>
                        </div>
                    </div>

                    <div class="border-t border-teal-200 pt-3">
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-600">{{ __('pages.settings.sidebar_add_custom_module') }}</label>
                        <div class="mt-2 grid gap-2 sm:grid-cols-2">
                            <input
                                type="text"
                                class="sidebar-custom-label rounded-lg border border-slate-300 px-3 py-2 text-sm"
                                placeholder="{{ __('pages.settings.sidebar_module_label') }}"
                                oninput="sidebarSyncModuleUrl(this)"
                            >
                            <input
                                type="text"
                                class="sidebar-custom-url rounded-lg border border-slate-300 px-3 py-2 text-sm"
                                placeholder="{{ __('pages.settings.sidebar_module_url_auto') }}"
                                data-auto-url="1"
                                data-manual-url="0"
                                oninput="sidebarMarkManualUrl(this)"
                            >
                        </div>
                        <p class="mt-1 text-xs text-slate-500">{{ __('pages.settings.sidebar_module_url_auto_hint') }}</p>
                        <button type="button" class="sidebar-add-custom-module mt-2 rounded-lg border border-teal-600 px-4 py-2 text-sm font-semibold text-teal-800 hover:bg-teal-100">
                            {{ __('pages.settings.sidebar_add_custom_module_btn') }}
                        </button>
                    </div>
                </div>
            </details>
        </div>
    </div>

    <div class="sidebar-group-items menu-tree-children ml-4 mt-1 space-y-1 border-l-2 border-slate-200 pl-3" data-group-id="{{ $groupId }}">
        @foreach($group['items'] as $itemKey)
            @if(SidebarService::isCustomModuleKey($itemKey) || \App\Enums\SidebarNavItem::tryFrom($itemKey) !== null)
                @include('settings.partials.sidebar-item-row', [
                    'groupId' => $groupId,
                    'itemKey' => $itemKey,
                    'roles' => $roles,
                    'sidebarVisibility' => $sidebarVisibility,
                    'sidebarService' => $sidebarService,
                    'customModules' => $customModules,
                ])
            @endif
        @endforeach
    </div>
</div>
