@extends('layouts.app')

@section('title', __('pages.roles.permissions_title', ['role' => $role->label()]))
@section('subtitle', __('pages.roles.permissions_subtitle'))

@section('content')
    @php
        $totalCustomModules = collect($permissionSections)->sum(fn ($section) => count($section['custom_modules']));
        $totalPermissions = count($permissions) + $totalCustomModules;
        $assignedCustomModules = $role->isProtected()
            ? collect($permissionSections)->flatMap(fn ($section) => $section['custom_modules'])->pluck('key')->all()
            : collect($permissionSections)
                ->flatMap(fn ($section) => $section['custom_modules'])
                ->filter(fn (array $module) => $sidebarService->isModuleVisibleForRole($module['key'], $role))
                ->pluck('key')
                ->all();
        $assignedCount = count($assigned) + count($assignedCustomModules);
        $totalModuleActions = $actionModules->sum(fn ($module) => $module->actions->count());
        $assignedModuleActionCount = count($visibleActionIds);
        $isProtected = $role->isProtected();
        $displayAssigned = $isProtected ? $permissions : $assigned;
        $displayVisibleActionIds = $isProtected
            ? $actionModules->flatMap(fn ($module) => $module->actions)->pluck('id')->all()
            : $visibleActionIds;
    @endphp

    <form
        method="POST"
        action="{{ route('roles.permissions.update', $role) }}"
        class="space-y-4"
        id="role-permissions-form"
        data-protected="{{ $isProtected ? '1' : '0' }}"
    >
        @csrf
        @method('PUT')

        @if($isProtected)
            <div class="app-notice">
                {{ __('pages.roles.super_admin_hint') }}
            </div>
        @endif

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm app-muted-text" id="permission-summary">
                {{ __('pages.roles.permissions_selected', ['selected' => $assignedCount, 'total' => $totalPermissions]) }}
            </p>

            @unless($isProtected)
                <div class="flex gap-4 text-sm font-semibold">
                    <button type="button" class="permission-select-all font-semibold text-[var(--app-link)] hover:text-[var(--app-link-hover)]">
                        {{ __('pages.roles.select_all') }}
                    </button>
                    <button type="button" class="permission-clear-all font-semibold app-muted-text hover:text-[var(--app-text)]">
                        {{ __('pages.roles.clear_all') }}
                    </button>
                </div>
            @endunless
        </div>

        <div class="permission-panel divide-y overflow-hidden rounded-xl border">
            @foreach($permissionSections as $group => $section)
                @php
                    $items = $section['permissions'];
                    $customModules = $section['custom_modules'];
                    $groupSlug = \Illuminate\Support\Str::slug($group);
                    $groupCheckedCount = collect($items)->filter(fn ($permission) => in_array($permission, $displayAssigned, true))->count()
                        + collect($customModules)->filter(fn (array $module) => in_array($module['key'], $assignedCustomModules, true))->count();
                    $groupTotal = count($items) + count($customModules);
                    $groupAllChecked = $groupTotal > 0 && $groupCheckedCount === $groupTotal;
                    $groupIndeterminate = $groupCheckedCount > 0 && $groupCheckedCount < $groupTotal;
                @endphp

                @if($groupTotal === 0)
                    @continue
                @endif

                <section class="permission-section px-4 py-4 sm:px-5" data-group="{{ $groupSlug }}">
                    <label class="permission-section__head {{ $isProtected ? 'cursor-default' : 'cursor-pointer' }}">
                        <input
                            type="checkbox"
                            class="permission-group-toggle h-5 w-5 shrink-0 rounded border-slate-300 text-teal-700 focus:ring-teal-600 disabled:opacity-60"
                            data-group="{{ $groupSlug }}"
                            @checked($groupAllChecked)
                            @disabled($isProtected)
                            @if($groupIndeterminate) data-indeterminate="1" @endif
                        >
                        <span class="permission-section__title">{{ $group }}</span>
                        <span
                            class="permission-section__badge"
                            data-group-count="{{ $groupSlug }}"
                        >{{ $groupCheckedCount }}/{{ $groupTotal }}</span>
                    </label>

                    <div class="permission-section__items mt-3 grid gap-2 sm:grid-cols-2">
                        @foreach($items as $permission)
                            @php $checked = in_array($permission, $displayAssigned, true); @endphp
                            <label class="permission-chip {{ $isProtected ? 'permission-chip--disabled' : '' }}">
                                <input
                                    type="checkbox"
                                    name="permissions[]"
                                    value="{{ $permission->value }}"
                                    class="permission-item-checkbox h-4 w-4 shrink-0 rounded border-slate-300 text-teal-700 focus:ring-teal-600 disabled:opacity-60"
                                    data-group="{{ $groupSlug }}"
                                    @checked($checked)
                                    @disabled($isProtected)
                                >
                                <span class="permission-chip__label">{{ $permission->label() }}</span>
                            </label>
                        @endforeach

                        @foreach($customModules as $module)
                            @php $moduleChecked = in_array($module['key'], $assignedCustomModules, true); @endphp
                            <label class="permission-chip {{ $isProtected ? 'permission-chip--disabled' : '' }}">
                                <input
                                    type="checkbox"
                                    name="custom_modules[]"
                                    value="{{ $module['key'] }}"
                                    class="permission-item-checkbox custom-module-checkbox h-4 w-4 shrink-0 rounded border-slate-300 text-teal-700 focus:ring-teal-600 disabled:opacity-60"
                                    data-group="{{ $groupSlug }}"
                                    @checked($moduleChecked)
                                    @disabled($isProtected)
                                >
                                <span class="permission-chip__label">{{ $module['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        <div class="space-y-3">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-[var(--app-text)]">{{ __('pages.roles.module_actions_title') }}</h2>
                    <p class="mt-1 text-sm app-muted-text">{{ __('pages.roles.module_actions_subtitle') }}</p>
                </div>

                <p class="text-sm app-muted-text" id="module-action-summary">
                    {{ __('pages.roles.module_actions_selected', ['selected' => $assignedModuleActionCount, 'total' => $totalModuleActions]) }}
                </p>
            </div>

            @unless($isProtected)
                <div class="flex gap-4 text-sm font-semibold">
                    <button type="button" class="module-action-select-all font-semibold text-[var(--app-link)] hover:text-[var(--app-link-hover)]">
                        {{ __('pages.roles.module_actions_select_all') }}
                    </button>
                    <button type="button" class="module-action-clear-all font-semibold app-muted-text hover:text-[var(--app-text)]">
                        {{ __('pages.roles.module_actions_clear_all') }}
                    </button>
                </div>
            @endunless

            <div class="module-action-list space-y-3">
                @forelse($actionModules as $module)
                    @php
                        $moduleActionIds = $module->actions->pluck('id')->all();
                        $moduleCheckedCount = collect($moduleActionIds)->filter(fn ($id) => in_array($id, $displayVisibleActionIds, true))->count();
                        $moduleTotal = $module->actions->count();
                        $moduleAllChecked = $moduleTotal > 0 && $moduleCheckedCount === $moduleTotal;
                        $moduleIndeterminate = $moduleCheckedCount > 0 && $moduleCheckedCount < $moduleTotal;
                    @endphp

                    <section
                        class="module-action-accordion overflow-hidden rounded-xl border"
                        data-module="{{ $module->module_key }}"
                    >
                        <div class="module-action-accordion__header">
                            <label class="module-action-accordion__head {{ $isProtected ? 'cursor-default' : 'cursor-pointer' }}">
                                <input
                                    type="checkbox"
                                    class="module-action-group-toggle h-5 w-5 shrink-0 rounded border-slate-300 text-teal-700 focus:ring-teal-600 disabled:opacity-60"
                                    data-module="{{ $module->module_key }}"
                                    @checked($moduleAllChecked)
                                    @disabled($isProtected)
                                    @if($moduleIndeterminate) data-indeterminate="1" @endif
                                >
                                <span class="module-action-accordion__title">{{ $module->label }}</span>
                                <span
                                    class="module-action-accordion__badge"
                                    data-module-count="{{ $module->module_key }}"
                                >{{ $moduleCheckedCount }}/{{ $moduleTotal }}</span>
                            </label>

                            <button
                                type="button"
                                class="module-action-accordion__toggle"
                                data-target="module-body-{{ $module->module_key }}"
                                aria-expanded="true"
                                aria-label="{{ __('pages.roles.module_actions_toggle') }}"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                        </div>

                        <div class="module-action-accordion__body" id="module-body-{{ $module->module_key }}">
                            <div class="module-action-accordion__items">
                                @foreach($module->actions as $action)
                                    @php $actionChecked = in_array($action->id, $displayVisibleActionIds, true); @endphp
                                    <label class="module-action-chip {{ $isProtected ? 'module-action-chip--disabled' : '' }}">
                                        <input
                                            type="checkbox"
                                            name="module_action_ids[]"
                                            value="{{ $action->id }}"
                                            class="module-action-item-checkbox h-4 w-4 shrink-0 rounded border-slate-300 text-teal-700 focus:ring-teal-600 disabled:opacity-60"
                                            data-module="{{ $module->module_key }}"
                                            @checked($actionChecked)
                                            @disabled($isProtected)
                                        >
                                        <span class="module-action-chip__label">{{ $action->label }}</span>
                                        <span class="module-action-chip__key">{{ $action->action_key }}</span>
                                    </label>
                                @endforeach
                            </div>

                            @unless($isProtected)
                                <button
                                    type="button"
                                    class="module-action-add-btn"
                                    data-panel="add-action-panel-{{ $module->module_key }}"
                                >
                                    {{ __('pages.roles.add_module_action') }}
                                </button>
                            @endunless
                        </div>
                    </section>
                @empty
                    <div class="rounded-xl border border-dashed px-4 py-8 text-center text-sm app-muted-text">
                        {{ __('pages.roles.module_actions_empty') }}
                    </div>
                @endforelse
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('roles.index') }}" class="btn-secondary text-center">{{ __('pages.roles.back') }}</a>

            @unless($isProtected)
                <button type="submit" class="btn-primary">{{ __('pages.roles.save_permissions') }}</button>
            @endunless
        </div>
    </form>

    @unless($isProtected)
        <div class="mt-6 space-y-4">
            @foreach($actionModules as $module)
                <div
                    id="add-action-panel-{{ $module->module_key }}"
                    class="module-action-add-panel hidden rounded-xl border bg-[var(--app-surface-muted)] p-4"
                >
                    <h3 class="mb-3 text-sm font-semibold text-[var(--app-text)]">
                        {{ __('pages.roles.add_module_action_for', ['module' => $module->label]) }}
                    </h3>
                    <form method="POST" action="{{ route('roles.module-actions.store', $module->module_key) }}" class="grid gap-3 sm:grid-cols-2">
                        @csrf
                        <input type="hidden" name="return_role" value="{{ $role->value }}">
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium">{{ __('pages.roles.module_action_key') }}</span>
                            <input type="text" name="action_key" required pattern="[a-z0-9_]+" class="w-full" placeholder="export_excel">
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium">{{ __('pages.roles.module_action_label') }}</span>
                            <input type="text" name="label" required class="w-full" placeholder="Export Excel">
                        </label>
                        <label class="block text-sm sm:col-span-2">
                            <span class="mb-1 block font-medium">{{ __('pages.roles.module_action_icon') }}</span>
                            <select name="icon_type" class="w-full">
                                <option value="extra">{{ __('pages.roles.icon_extra') }}</option>
                                <option value="view">{{ __('pages.roles.icon_view') }}</option>
                                <option value="edit">{{ __('pages.roles.icon_edit') }}</option>
                                <option value="delete">{{ __('pages.roles.icon_delete') }}</option>
                                <option value="location">{{ __('pages.roles.icon_location') }}</option>
                            </select>
                        </label>
                        <div class="flex gap-2 sm:col-span-2">
                            <button type="submit" class="btn-primary">{{ __('pages.roles.save_module_action') }}</button>
                            <button type="button" class="btn-secondary module-action-add-cancel" data-panel="add-action-panel-{{ $module->module_key }}">
                                {{ __('pages.roles.cancel') }}
                            </button>
                        </div>
                    </form>
                </div>
            @endforeach

            <div class="rounded-xl border p-4">
                <h3 class="mb-3 text-sm font-semibold text-[var(--app-text)]">{{ __('pages.roles.add_module_title') }}</h3>
                <form method="POST" action="{{ route('roles.action-modules.store') }}" class="grid gap-3 sm:grid-cols-2">
                    @csrf
                    <input type="hidden" name="return_role" value="{{ $role->value }}">
                    <label class="block text-sm">
                        <span class="mb-1 block font-medium">{{ __('pages.roles.module_key') }}</span>
                        <input type="text" name="module_key" required pattern="[a-z0-9_]+" class="w-full" placeholder="pengguna_beasiswa">
                    </label>
                    <label class="block text-sm">
                        <span class="mb-1 block font-medium">{{ __('pages.roles.module_label') }}</span>
                        <input type="text" name="label" required class="w-full" placeholder="Pengguna Beasiswa">
                    </label>
                    <div class="sm:col-span-2">
                        <button type="submit" class="btn-primary">{{ __('pages.roles.add_module') }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endunless
@endsection

@push('scripts')
<script>
    function initRolePermissionsForm() {
        const form = document.getElementById('role-permissions-form');
        if (!form) return;

        const isProtected = form.dataset.protected === '1';
        const summaryEl = document.getElementById('permission-summary');
        const itemCheckboxes = () => [...form.querySelectorAll('.permission-item-checkbox')];
        const groupToggles = () => [...form.querySelectorAll('.permission-group-toggle')];

        function groupItems(groupSlug) {
            return itemCheckboxes().filter((input) => input.dataset.group === groupSlug);
        }

        function syncGroupToggle(groupSlug) {
            const toggle = form.querySelector(`.permission-group-toggle[data-group="${groupSlug}"]`);
            const countEl = form.querySelector(`[data-group-count="${groupSlug}"]`);
            const items = groupItems(groupSlug);
            if (!toggle || items.length === 0) return;

            const checkedCount = items.filter((input) => input.checked).length;
            const total = items.length;

            toggle.checked = checkedCount === total;
            toggle.indeterminate = checkedCount > 0 && checkedCount < total;
            toggle.removeAttribute('data-indeterminate');

            if (countEl) {
                countEl.textContent = `${checkedCount}/${total}`;
            }
        }

        function syncSummary() {
            const items = itemCheckboxes();
            const checkedCount = items.filter((input) => input.checked).length;
            const total = items.length;

            if (summaryEl) {
                const template = @json(__('pages.roles.permissions_selected'));
                summaryEl.textContent = template
                    .replace(':selected', String(checkedCount))
                    .replace(':total', String(total));
            }
        }

        function syncAll() {
            groupToggles().forEach((toggle) => syncGroupToggle(toggle.dataset.group));
            syncSummary();
        }

        function setAllItems(checked) {
            if (isProtected) return;
            itemCheckboxes().forEach((input) => {
                input.checked = checked;
            });
            syncAll();
        }

        if (!isProtected) {
            form.addEventListener('change', (event) => {
                const groupToggle = event.target.closest('.permission-group-toggle');
                if (groupToggle) {
                    groupItems(groupToggle.dataset.group).forEach((input) => {
                        input.checked = groupToggle.checked;
                    });
                    syncAll();
                    return;
                }

                const itemCheckbox = event.target.closest('.permission-item-checkbox');
                if (itemCheckbox) {
                    syncGroupToggle(itemCheckbox.dataset.group);
                    syncSummary();
                }
            });

            form.querySelector('.permission-select-all')?.addEventListener('click', () => setAllItems(true));
            form.querySelector('.permission-clear-all')?.addEventListener('click', () => setAllItems(false));
        }

        groupToggles().forEach((toggle) => {
            if (toggle.getAttribute('data-indeterminate') === '1') {
                toggle.indeterminate = true;
            }
        });

        syncAll();
    }

    function initModuleActionControls() {
        const form = document.getElementById('role-permissions-form');
        if (!form) return;

        const isProtected = form.dataset.protected === '1';
        const summaryEl = document.getElementById('module-action-summary');
        const itemCheckboxes = () => [...form.querySelectorAll('.module-action-item-checkbox')];
        const groupToggles = () => [...form.querySelectorAll('.module-action-group-toggle')];

        function moduleItems(moduleKey) {
            return itemCheckboxes().filter((input) => input.dataset.module === moduleKey);
        }

        function syncModuleToggle(moduleKey) {
            const toggle = form.querySelector(`.module-action-group-toggle[data-module="${moduleKey}"]`);
            const countEl = form.querySelector(`[data-module-count="${moduleKey}"]`);
            const items = moduleItems(moduleKey);
            if (!toggle || items.length === 0) return;

            const checkedCount = items.filter((input) => input.checked).length;
            const total = items.length;

            toggle.checked = checkedCount === total;
            toggle.indeterminate = checkedCount > 0 && checkedCount < total;
            toggle.removeAttribute('data-indeterminate');

            if (countEl) {
                countEl.textContent = `${checkedCount}/${total}`;
            }
        }

        function syncSummary() {
            const items = itemCheckboxes();
            const checkedCount = items.filter((input) => input.checked).length;
            const total = items.length;

            if (summaryEl) {
                const template = @json(__('pages.roles.module_actions_selected'));
                summaryEl.textContent = template
                    .replace(':selected', String(checkedCount))
                    .replace(':total', String(total));
            }
        }

        function syncAll() {
            groupToggles().forEach((toggle) => syncModuleToggle(toggle.dataset.module));
            syncSummary();
        }

        if (!isProtected) {
            form.addEventListener('change', (event) => {
                const groupToggle = event.target.closest('.module-action-group-toggle');
                if (groupToggle) {
                    moduleItems(groupToggle.dataset.module).forEach((input) => {
                        input.checked = groupToggle.checked;
                    });
                    syncAll();
                    return;
                }

                if (event.target.closest('.module-action-item-checkbox')) {
                    syncModuleToggle(event.target.dataset.module);
                    syncSummary();
                }
            });

            form.querySelector('.module-action-select-all')?.addEventListener('click', () => {
                itemCheckboxes().forEach((input) => {
                    input.checked = true;
                });
                syncAll();
            });

            form.querySelector('.module-action-clear-all')?.addEventListener('click', () => {
                itemCheckboxes().forEach((input) => {
                    input.checked = false;
                });
                syncAll();
            });
        }

        groupToggles().forEach((toggle) => {
            if (toggle.getAttribute('data-indeterminate') === '1') {
                toggle.indeterminate = true;
            }
        });

        document.querySelectorAll('.module-action-accordion__toggle').forEach((button) => {
            button.addEventListener('click', () => {
                const body = document.getElementById(button.dataset.target);
                if (!body) return;

                const isOpen = !body.classList.contains('hidden');
                body.classList.toggle('hidden', isOpen);
                button.setAttribute('aria-expanded', String(!isOpen));
                button.classList.toggle('is-collapsed', isOpen);
            });
        });

        document.querySelectorAll('.module-action-add-btn').forEach((button) => {
            button.addEventListener('click', () => {
                const panel = document.getElementById(button.dataset.panel);
                if (!panel) return;

                document.querySelectorAll('.module-action-add-panel').forEach((item) => {
                    if (item !== panel) {
                        item.classList.add('hidden');
                    }
                });

                panel.classList.toggle('hidden');
            });
        });

        document.querySelectorAll('.module-action-add-cancel').forEach((button) => {
            button.addEventListener('click', () => {
                document.getElementById(button.dataset.panel)?.classList.add('hidden');
            });
        });

        syncAll();
    }

    function initRolePermissionsPage() {
        initRolePermissionsForm();
        initModuleActionControls();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRolePermissionsPage);
    } else {
        initRolePermissionsPage();
    }
</script>
@endpush
