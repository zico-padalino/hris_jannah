@extends('layouts.app')
@section('title', __('pages.settings.title'))
@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-6" id="settings-form">
        @csrf
        @method('PUT')

        <section class="rounded-xl border bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900">{{ __('pages.settings.branding_title') }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ __('pages.settings.branding_hint') }}</p>

            <div class="mt-4 grid gap-6 lg:grid-cols-2">
                <div class="space-y-4">
                    <label class="block text-sm">
                        {{ __('pages.settings.branding_name') }}
                        <input
                            name="app_name"
                            type="text"
                            value="{{ old('app_name', $settings['branding']['name'] !== '' ? $settings['branding']['name'] : __('app.name')) }}"
                            maxlength="100"
                            required
                            class="mt-1 w-full rounded-lg border px-3 py-2"
                        >
                    </label>
                    @error('app_name')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <label class="block text-sm">
                        {{ __('pages.settings.branding_logo') }}
                        <input
                            name="app_logo"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            class="mt-1 block w-full text-sm text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-teal-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-teal-800 hover:file:bg-teal-100"
                        >
                    </label>
                    <p class="text-xs text-slate-500">{{ __('pages.settings.branding_logo_hint') }}</p>
                    @error('app_logo')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    @if($appBranding->hasLogo())
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="remove_app_logo" value="1" @checked(old('remove_app_logo')) class="h-4 w-4 rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                            {{ __('pages.settings.branding_logo_remove') }}
                        </label>
                    @endif
                </div>

                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('pages.settings.branding_preview') }}</p>
                    <div class="mt-4 rounded-lg border border-slate-200 bg-white p-4">
                        <p class="mb-3 text-xs font-medium text-slate-500">{{ __('pages.settings.branding_preview_login') }}</p>
                        @include('partials.app-branding', [
                            'align' => 'center',
                            'nameClass' => 'text-2xl font-extrabold text-slate-900',
                            'logoClass' => 'h-14 w-auto max-w-[200px] object-contain',
                        ])
                        <p class="mt-2 text-center text-sm text-slate-500">{{ __('auth.subtitle') }}</p>
                    </div>
                    <div class="mt-4 rounded-lg p-4 text-white" style="background-color: var(--app-sidebar-bg, #0f766e)">
                        <p class="mb-3 text-xs font-medium" style="color: var(--app-sidebar-text-muted, #99f6e4)">{{ __('pages.settings.branding_preview_sidebar') }}</p>
                        @include('partials.app-branding', [
                            'nameClass' => 'text-xl font-bold text-white',
                            'logoClass' => 'h-10 w-auto max-w-[160px] object-contain',
                        ])
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-xl border bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900">{{ __('pages.settings.general_title') }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ __('pages.settings.general_hint') }}</p>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <label class="block text-sm">
                    {{ __('pages.settings.face_threshold') }}
                    <input name="face_match_threshold" type="number" step="0.01" value="{{ $settings['face_match_threshold'] }}" class="mt-1 w-full rounded-lg border px-3 py-2">
                </label>
                <label class="block text-sm">
                    {{ __('pages.settings.location_buffer') }}
                    <input name="location_buffer_meters" type="number" value="{{ $settings['location_buffer_meters'] }}" class="mt-1 w-full rounded-lg border px-3 py-2">
                </label>
            </div>
            <p class="mt-3 text-xs text-slate-500">
                {{ __('pages.settings.payroll_deduction_moved') }}
                @perm('payroll.manage')
                    <a href="{{ route('potongan.index') }}" class="font-semibold text-teal-700 hover:underline">{{ __('pages.potongan.title') }}</a>
                @endperm
            </p>

            <div class="mt-6 border-t border-slate-200 pt-6">
                <h3 class="text-base font-bold text-slate-900">{{ __('pages.settings.user_password_title') }}</h3>
                <p class="mt-1 text-sm text-slate-500">{{ __('pages.settings.user_password_hint') }}</p>

                <div class="mt-4 space-y-3">
                    <label class="flex items-start gap-3 rounded-lg border border-slate-200 p-4 hover:bg-slate-50">
                        <input
                            type="radio"
                            name="user_default_password_mode"
                            value="employee_number"
                            @checked(old('user_default_password_mode', $settings['user_default_password']['mode']) === 'employee_number')
                            class="mt-1 h-4 w-4 border-slate-300 text-teal-700 focus:ring-teal-600"
                        >
                        <span>
                            <span class="block text-sm font-semibold text-slate-900">{{ __('pages.settings.user_password_mode_employee') }}</span>
                            <span class="mt-0.5 block text-xs text-slate-500">{{ __('pages.settings.user_password_mode_employee_hint') }}</span>
                        </span>
                    </label>

                    <label class="flex items-start gap-3 rounded-lg border border-slate-200 p-4 hover:bg-slate-50">
                        <input
                            type="radio"
                            name="user_default_password_mode"
                            value="custom"
                            @checked(old('user_default_password_mode', $settings['user_default_password']['mode']) === 'custom')
                            class="mt-1 h-4 w-4 border-slate-300 text-teal-700 focus:ring-teal-600"
                        >
                        <span class="w-full">
                            <span class="block text-sm font-semibold text-slate-900">{{ __('pages.settings.user_password_mode_custom') }}</span>
                            <span class="mt-0.5 block text-xs text-slate-500">{{ __('pages.settings.user_password_mode_custom_hint') }}</span>
                            <div class="mt-3 max-w-sm">
                            @include('partials.password-field', [
                                'name' => 'user_default_password_custom',
                                'value' => old('user_default_password_custom', $settings['user_default_password']['custom']),
                                'maxlength' => 64,
                                'placeholder' => __('pages.settings.user_password_custom_placeholder'),
                                'autocomplete' => 'new-password',
                                'inputClass' => 'w-full rounded-lg border px-3 py-2 text-sm',
                            ])
                            </div>
                        </span>
                    </label>
                </div>

                @error('user_default_password_custom')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </section>

        <section class="rounded-xl border bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900">{{ __('pages.settings.attendance_method_title') }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ __('pages.settings.attendance_method_hint') }}</p>

            <div class="mt-4 space-y-4">
                <label class="flex items-start gap-3 rounded-lg border border-slate-200 p-4 hover:bg-slate-50">
                    <input
                        type="checkbox"
                        name="attendance_method_fingerprint"
                        value="1"
                        @checked(old('attendance_method_fingerprint', $settings['attendance_methods']['fingerprint']))
                        class="mt-1 h-4 w-4 rounded border-slate-300 text-teal-700 focus:ring-teal-600"
                    >
                    <span>
                        <span class="block text-sm font-semibold text-slate-900">{{ __('pages.settings.attendance_method_fingerprint') }}</span>
                        <span class="mt-0.5 block text-xs text-slate-500">{{ __('pages.settings.attendance_method_fingerprint_hint') }}</span>
                    </span>
                </label>

                <label class="flex items-start gap-3 rounded-lg border border-slate-200 p-4 hover:bg-slate-50">
                    <input
                        type="checkbox"
                        name="attendance_method_photo"
                        value="1"
                        @checked(old('attendance_method_photo', $settings['attendance_methods']['photo']))
                        class="mt-1 h-4 w-4 rounded border-slate-300 text-teal-700 focus:ring-teal-600"
                    >
                    <span>
                        <span class="block text-sm font-semibold text-slate-900">{{ __('pages.settings.attendance_method_photo') }}</span>
                        <span class="mt-0.5 block text-xs text-slate-500">{{ __('pages.settings.attendance_method_photo_hint') }}</span>
                    </span>
                </label>

                <label class="app-notice flex items-start gap-3 hover:opacity-95">
                    <input
                        type="checkbox"
                        name="attendance_method_gps"
                        value="1"
                        @checked(old('attendance_method_gps', $settings['attendance_methods']['gps']))
                        class="mt-1 h-4 w-4 rounded border-slate-300 text-teal-700 focus:ring-teal-600"
                    >
                    <span>
                        <span class="block text-sm font-semibold text-slate-900">{{ __('pages.settings.attendance_method_gps') }}</span>
                        <span class="mt-0.5 block text-xs text-slate-500">{{ __('pages.settings.attendance_method_gps_hint') }}</span>
                    </span>
                </label>
            </div>

            @error('attendance_method_fingerprint')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </section>

        <section class="rounded-xl border bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900">{{ __('pages.settings.maintenance_title') }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ __('pages.settings.maintenance_hint') }}</p>

            <label class="mt-4 block text-sm">
                {{ __('pages.settings.maintenance_message') }}
                <textarea
                    name="maintenance_message"
                    rows="3"
                    maxlength="500"
                    placeholder="{{ __('pages.settings.maintenance_message_placeholder') }}"
                    class="mt-1 w-full rounded-lg border px-3 py-2"
                >{{ old('maintenance_message', $settings['maintenance_message']) }}</textarea>
            </label>
            <p class="mt-1 text-xs text-slate-500">{{ __('pages.settings.maintenance_message_hint') }}</p>

            @php
                $maintenanceEnabled = old('maintenance_modules', $settings['maintenance_modules']);
                $maintenanceBySection = collect($maintenanceModuleOptions)->groupBy('section');
            @endphp

            <div class="mt-6 space-y-5">
                @foreach($maintenanceBySection as $sectionLabel => $modules)
                    <div class="rounded-xl border border-slate-200 p-4">
                        @if($sectionLabel !== '')
                            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">{{ $sectionLabel }}</h3>
                        @endif
                        <div @class(['grid gap-3 sm:grid-cols-2 lg:grid-cols-3', 'mt-3' => $sectionLabel !== ''])>
                            @foreach($modules as $module)
                                <label class="flex items-start gap-3 rounded-lg border border-slate-200 p-3 hover:bg-slate-50">
                                    <input
                                        type="checkbox"
                                        name="maintenance_modules[]"
                                        value="{{ $module['value'] }}"
                                        @checked(in_array($module['value'], $maintenanceEnabled ?? [], true))
                                        class="mt-1 h-4 w-4 rounded border-slate-300 text-teal-700 focus:ring-teal-600"
                                    >
                                    <span class="text-sm font-medium text-slate-900">{{ $module['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <p class="mt-4 text-sm text-slate-500">{{ __('pages.settings.maintenance_bypass_hint') }}</p>
        </section>

        <section class="rounded-xl border bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900">{{ __('pages.settings.sidebar_title') }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ __('pages.settings.sidebar_hint') }}</p>

            <div class="mt-4 max-w-xs">
                <label class="block text-sm font-medium text-slate-700">
                    {{ __('pages.settings.sidebar_position') }}
                    <select name="sidebar_position" class="mt-1 w-full rounded-lg border px-3 py-2">
                        <option value="left" @selected($settings['sidebar_position'] === 'left')>{{ __('pages.settings.sidebar_left') }}</option>
                        <option value="right" @selected($settings['sidebar_position'] === 'right')>{{ __('pages.settings.sidebar_right') }}</option>
                    </select>
                </label>
            </div>

            <div class="menu-tree mt-6 rounded-xl border-2 border-slate-200 bg-slate-50/80 p-4 sm:p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">{{ __('pages.settings.sidebar_structure_title') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('pages.settings.sidebar_structure_hint') }}</p>
                    </div>
                    <button type="button" id="add-sidebar-group" class="shrink-0 rounded-lg border-2 border-teal-600 bg-white px-4 py-2 text-sm font-semibold text-teal-800 hover:bg-teal-50">
                        {{ __('pages.settings.sidebar_add_group') }}
                    </button>
                </div>

                <div id="sidebar-groups" class="menu-tree-list mt-4 space-y-2">
                @foreach($sidebarGroups as $group)
                    @include('settings.partials.sidebar-group', [
                        'group' => $group,
                        'roles' => $roles,
                        'sidebarVisibility' => $sidebarVisibility,
                        'sidebarService' => $sidebarService,
                        'customModules' => $customModules,
                        'builtinModuleOptions' => $builtinModuleOptions,
                    ])
                @endforeach
                </div>
            </div>

            <p class="mt-4 text-sm text-slate-500">{{ __('pages.settings.sidebar_visibility_hint') }}</p>
        </section>

        <div class="flex justify-end">
            <button type="submit" class="rounded-lg bg-teal-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-teal-800">
                {{ __('pages.settings.save') }}
            </button>
        </div>
    </form>
</div>

<div id="sidebar-builtin-templates" class="hidden" aria-hidden="true">
    @foreach($builtinModuleOptions as $option)
        <div data-template-item="{{ $option['value'] }}">
            @include('settings.partials.sidebar-item-row', [
                'groupId' => '__GROUP_ID__',
                'itemKey' => $option['value'],
                'roles' => $roles,
                'sidebarVisibility' => $sidebarVisibility,
                'sidebarService' => $sidebarService,
                'customModules' => $customModules,
            ])
        </div>
    @endforeach
</div>

<template id="sidebar-group-template">
    @include('settings.partials.sidebar-group', [
        'group' => ['id' => '__GROUP_ID__', 'builtin' => null, 'label' => '', 'items' => []],
        'roles' => $roles,
        'sidebarVisibility' => $sidebarVisibility,
        'sidebarService' => $sidebarService,
        'customModules' => $customModules,
        'builtinModuleOptions' => $builtinModuleOptions,
    ])
</template>

<template id="sidebar-custom-module-template">
    @include('settings.partials.sidebar-custom-module-row', [
        'moduleId' => 'mod____TEMPLATE__',
        'groupId' => '__GROUP_ID__',
        'module' => ['label' => '', 'url' => ''],
        'roles' => $roles,
    ])
</template>
@endsection

@push('scripts')
<script>
    function slugifyModuleLabel(label) {
        const slug = label
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .trim()
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');

        return slug ? '/' + slug : '';
    }

    function urlInputForLabelInput(labelInput) {
        const panel = labelInput.closest('.sidebar-add-module-panel');
        if (panel && labelInput.classList.contains('sidebar-custom-label')) {
            return panel.querySelector('.sidebar-custom-url');
        }

        const row = labelInput.closest('.sidebar-item-row[data-custom="1"]');
        if (row && labelInput.classList.contains('sidebar-module-label')) {
            return row.querySelector('.sidebar-module-url');
        }

        return null;
    }

    function sidebarSyncModuleUrl(labelInput) {
        if (!labelInput) return;

        const urlInput = urlInputForLabelInput(labelInput);
        if (!urlInput || urlInput.getAttribute('data-manual-url') === '1') {
            return;
        }

        urlInput.value = slugifyModuleLabel(labelInput.value);
        urlInput.setAttribute('data-auto-url', '1');
    }

    function sidebarMarkManualUrl(urlInput) {
        if (!urlInput) return;

        const labelInput = urlInput.closest('.sidebar-add-module-panel')?.querySelector('.sidebar-custom-label')
            ?? urlInput.closest('.sidebar-item-row[data-custom="1"]')?.querySelector('.sidebar-module-label');
        const autoUrl = labelInput ? slugifyModuleLabel(labelInput.value.trim()) : '';

        if (urlInput.value.trim() === '' || urlInput.value.trim() === autoUrl) {
            urlInput.setAttribute('data-manual-url', '0');
            urlInput.setAttribute('data-auto-url', '1');
            if (labelInput) {
                sidebarSyncModuleUrl(labelInput);
            }
            return;
        }

        urlInput.setAttribute('data-manual-url', '1');
    }

    window.sidebarSyncModuleUrl = sidebarSyncModuleUrl;
    window.sidebarMarkManualUrl = sidebarMarkManualUrl;

    function initSidebarSettings() {
        const settingsForm = document.getElementById('settings-form');
        const groupsContainer = document.getElementById('sidebar-groups');
        const addGroupBtn = document.getElementById('add-sidebar-group');
        const groupTemplate = document.getElementById('sidebar-group-template');
        const customModuleTemplate = document.getElementById('sidebar-custom-module-template');
        const builtinTemplates = document.getElementById('sidebar-builtin-templates');

        if (!settingsForm || !groupsContainer) return;

        let draggedGroup = null;
        let draggedItem = null;

        function makeId(prefix) {
            return prefix + Math.random().toString(36).slice(2, 10);
        }

        function clearDragStyles() {
            groupsContainer.querySelectorAll('.sidebar-group-card, .sidebar-item-row').forEach((el) => {
                el.classList.remove('opacity-50', 'ring-2', 'ring-teal-400');
            });
        }

        function findGroupCard(element) {
            return element?.closest('.sidebar-group-card') ?? null;
        }

        function findItemRow(element) {
            return element?.closest('.sidebar-item-row') ?? null;
        }

        function findItemsContainer(element) {
            return element?.closest('.sidebar-group-items') ?? null;
        }

        function resolveDropContainer(target, draggedRow) {
            const direct = target?.closest('.sidebar-group-items');
            if (direct) {
                return direct;
            }

            const groupCard = target?.closest('.sidebar-group-card');
            if (groupCard) {
                return groupCard.querySelector('.sidebar-group-items');
            }

            return findItemsContainer(draggedRow);
        }

        function findTreePanel(node) {
            return node?.querySelector(':scope > .menu-tree-panel')
                ?? node?.querySelector('.menu-tree-panel');
        }

        function findExpandButton(node) {
            return node?.querySelector('.menu-tree-row .menu-tree-expand');
        }

        function templateHtml(template) {
            if (!template) return '';
            if (template.content) {
                const wrapper = document.createElement('div');
                wrapper.appendChild(template.content.cloneNode(true));
                return wrapper.innerHTML;
            }
            return template.innerHTML;
        }

        function replacePlaceholders(html, replacements) {
            let output = html;
            Object.entries(replacements).forEach(([key, value]) => {
                output = output.replaceAll(key, value);
            });
            return output;
        }

        function reindexGroupInputs(groupCard, groupId) {
            groupCard.dataset.groupId = groupId;
            groupCard.querySelectorAll('[name]').forEach((input) => {
                input.name = input.name.replace(/sidebar_groups\[[^\]]+\]/, `sidebar_groups[${groupId}]`);
            });
            groupCard.querySelector('.sidebar-group-items')?.setAttribute('data-group-id', groupId);
        }

        function removeItemFromOtherGroups(itemKey, keepContainer) {
            groupsContainer.querySelectorAll(`.sidebar-item-row[data-item="${itemKey}"]`).forEach((row) => {
                const container = findItemsContainer(row);
                if (container && container !== keepContainer) {
                    row.remove();
                }
            });
        }

        function addGroup() {
            if (!groupTemplate) return;
            const groupId = makeId('custom_');
            const wrapper = document.createElement('div');
            wrapper.innerHTML = replacePlaceholders(templateHtml(groupTemplate).trim(), { __GROUP_ID__: groupId });
            const card = wrapper.firstElementChild;
            if (!card) return;
            card.dataset.groupId = groupId;
            card.dataset.custom = '1';
            card.dataset.dashboard = '0';
            reindexGroupInputs(card, groupId);
            groupsContainer.appendChild(card);
            openGroupPanel(card);
            card.querySelector('.sidebar-group-label-input')?.focus();
        }

        function removeGroup(card) {
            if (card.dataset.dashboard === '1') return;
            if (!confirm(@json(__('pages.settings.sidebar_confirm_delete_group')))) return;

            card.querySelectorAll('.sidebar-item-row').forEach((item) => {
                if (item.dataset.custom === '1') {
                    item.remove();
                    return;
                }
                const defaultGroupId = item.dataset.defaultGroup;
                const target = groupsContainer.querySelector(`.sidebar-group-card[data-group-id="${defaultGroupId}"] .sidebar-group-items`)
                    ?? groupsContainer.querySelector('.sidebar-group-items');
                if (target) {
                    removeItemFromOtherGroups(item.dataset.item, target);
                    target.appendChild(item);
                }
            });
            card.remove();
        }

        function cloneBuiltinRow(groupId, itemKey) {
            const source = builtinTemplates?.querySelector(`[data-template-item="${itemKey}"]`);
            if (!source) return null;
            const wrapper = document.createElement('div');
            wrapper.innerHTML = replacePlaceholders(source.innerHTML.trim(), { __GROUP_ID__: groupId });
            const row = wrapper.firstElementChild;
            if (!row) return null;
            row.dataset.item = itemKey;
            return row;
        }

        function applyCustomModulePlaceholders(row, groupId, moduleId) {
            row.dataset.item = moduleId;
            row.querySelectorAll('[name]').forEach((input) => {
                input.name = input.name
                    .replaceAll('mod____TEMPLATE__', moduleId)
                    .replaceAll('__GROUP_ID__', groupId);
                if (input.type === 'checkbox') {
                    input.value = moduleId;
                }
            });
            const hiddenItem = row.querySelector('input[name$="[items][]"]');
            if (hiddenItem) {
                hiddenItem.value = moduleId;
            }
        }

        function cloneCustomRow(groupId, moduleId, label, url) {
            if (!customModuleTemplate) return null;
            const wrapper = document.createElement('div');
            wrapper.innerHTML = replacePlaceholders(templateHtml(customModuleTemplate).trim(), {
                __GROUP_ID__: groupId,
                mod____TEMPLATE__: moduleId,
            });
            const row = wrapper.firstElementChild;
            if (!row) return null;

            applyCustomModulePlaceholders(row, groupId, moduleId);

            const labelInput = row.querySelector('.sidebar-module-label');
            const urlInput = row.querySelector('.sidebar-module-url');
            if (labelInput) labelInput.value = label;
            if (urlInput) urlInput.value = url;
            setNodeText(row, '.sidebar-item-label', label);

            return row;
        }

        function syncGroupLabelDisplay(card) {
            const input = card?.querySelector('.sidebar-group-label-input');
            const display = card?.querySelector('.sidebar-group-label-display');
            if (!input || !display) return;
            const value = input.value.trim();
            if (value !== '') {
                display.textContent = value;
            }
        }

        function setNodeText(node, selector, text) {
            const el = node?.querySelector(selector);
            if (el) {
                el.textContent = text;
            }
        }

        function syncModuleLabelDisplay(row) {
            if (!row || row.dataset.custom !== '1') return;
            const label = row.querySelector('.sidebar-module-label')?.value.trim() ?? '';
            const fallback = @json(__('pages.settings.sidebar_custom_module'));
            setNodeText(row, '.sidebar-item-label', label || fallback);
        }

        function toggleTreePanel(node, expand) {
            const panel = findTreePanel(node);
            const btn = findExpandButton(node);
            if (!panel || !btn) return;
            panel.classList.toggle('hidden', !expand);
            btn.setAttribute('aria-expanded', expand ? 'true' : 'false');
            btn.querySelector('.menu-tree-chevron')?.classList.toggle('rotate-180', expand);
            node?.classList.toggle('menu-tree-node--open', expand);
        }

        function openGroupPanel(card) {
            toggleTreePanel(card, true);
        }

        function openAddModulePanel(card) {
            openGroupPanel(card);
            card?.querySelector('.sidebar-add-module-details')?.setAttribute('open', 'open');
        }

        let dragSource = null;

        addGroupBtn?.addEventListener('click', addGroup);

        groupsContainer.addEventListener('mousedown', (event) => {
            dragSource = null;
            if (event.target.closest('.sidebar-item-drag')) {
                dragSource = {
                    type: 'item',
                    el: event.target.closest('.sidebar-item-row'),
                };
                return;
            }
            if (event.target.closest('.sidebar-drag-handle')) {
                dragSource = {
                    type: 'group',
                    el: event.target.closest('.sidebar-group-card'),
                };
            }
        });

        groupsContainer.addEventListener('mouseup', () => {
            if (!draggedItem && !draggedGroup) {
                dragSource = null;
            }
        });

        groupsContainer.addEventListener('click', (event) => {
            const expandBtn = event.target.closest('.menu-tree-expand');
            if (expandBtn) {
                event.preventDefault();
                const node = expandBtn.closest('.menu-tree-node');
                const panel = findTreePanel(node);
                if (panel) {
                    const willOpen = panel.classList.contains('hidden');
                    toggleTreePanel(node, willOpen);
                }
                return;
            }

            const deleteGroupBtn = event.target.closest('.sidebar-delete-group');
            if (deleteGroupBtn) {
                const card = findGroupCard(deleteGroupBtn);
                if (card) removeGroup(card);
                return;
            }

            const addBuiltinBtn = event.target.closest('.sidebar-add-builtin-module');
            if (addBuiltinBtn) {
                const card = findGroupCard(addBuiltinBtn);
                const select = card?.querySelector('.sidebar-pick-builtin');
                const container = card?.querySelector('.sidebar-group-items');
                const itemKey = select?.value;
                if (!card || !container || !itemKey) return;
                if (container.querySelector(`[data-item="${itemKey}"]`)) {
                    alert(@json(__('pages.settings.sidebar_module_exists')));
                    return;
                }
                const row = cloneBuiltinRow(card.dataset.groupId, itemKey);
                if (!row) {
                    alert(@json(__('pages.settings.sidebar_module_add_failed')));
                    return;
                }
                removeItemFromOtherGroups(itemKey, container);
                container.appendChild(row);
                select.value = '';
                openAddModulePanel(card);
                return;
            }

            const addCustomBtn = event.target.closest('.sidebar-add-custom-module');
            if (addCustomBtn) {
                const card = findGroupCard(addCustomBtn);
                const container = card?.querySelector('.sidebar-group-items');
                const labelInput = card?.querySelector('.sidebar-custom-label');
                const urlInput = card?.querySelector('.sidebar-custom-url');
                const label = labelInput?.value.trim() ?? '';
                let url = urlInput?.value.trim() ?? '';
                if (label === '') {
                    alert(@json(__('pages.settings.sidebar_module_required')));
                    return;
                }
                if (url === '') {
                    url = slugifyModuleLabel(label);
                }
                if (!card || !container || url === '') {
                    alert(@json(__('pages.settings.sidebar_module_required')));
                    return;
                }
                const moduleId = makeId('mod_');
                const row = cloneCustomRow(card.dataset.groupId, moduleId, label, url);
                if (!row) {
                    alert(@json(__('pages.settings.sidebar_module_add_failed')));
                    return;
                }
                container.appendChild(row);
                openAddModulePanel(card);
                toggleTreePanel(row, true);
                row.querySelector('.sidebar-module-label')?.focus();
                labelInput.value = '';
                urlInput.value = '';
                urlInput.setAttribute('data-manual-url', '0');
                urlInput.setAttribute('data-auto-url', '1');
                return;
            }

            const deleteModuleBtn = event.target.closest('.sidebar-delete-module');
            if (deleteModuleBtn) {
                const row = findItemRow(deleteModuleBtn);
                if (!row) return;
                if (!confirm(@json(__('pages.settings.sidebar_confirm_delete_module')))) return;
                row.remove();
            }
        });

        settingsForm.addEventListener('input', (event) => {
            const groupLabelInput = event.target.closest('.sidebar-group-label-input');
            if (groupLabelInput) {
                syncGroupLabelDisplay(findGroupCard(groupLabelInput));
                return;
            }

            const labelInput = event.target.closest('.sidebar-custom-label, .sidebar-module-label');
            if (labelInput) {
                sidebarSyncModuleUrl(labelInput);
                const row = findItemRow(labelInput);
                if (row) {
                    syncModuleLabelDisplay(row);
                }
                return;
            }

            const urlInput = event.target.closest('.sidebar-custom-url, .sidebar-module-url');
            if (urlInput) {
                sidebarMarkManualUrl(urlInput);
                syncModuleLabelDisplay(findItemRow(urlInput));
            }
        });

        function clearDropHighlights() {
            groupsContainer.querySelectorAll('.sidebar-group-items').forEach((el) => {
                el.classList.remove('ring-2', 'ring-teal-400', 'ring-inset', 'bg-teal-50/40');
            });
        }

        groupsContainer.addEventListener('dragstart', (event) => {
            if (!dragSource?.el) {
                event.preventDefault();
                return;
            }

            if (dragSource.type === 'item') {
                draggedItem = dragSource.el;
                draggedGroup = null;
                draggedItem.classList.add('opacity-50', 'ring-2', 'ring-teal-400');
                event.dataTransfer.setData('text/plain', draggedItem.dataset.item ?? 'item');
                event.dataTransfer.effectAllowed = 'move';
                return;
            }

            if (dragSource.type === 'group') {
                draggedGroup = dragSource.el;
                draggedItem = null;
                draggedGroup.classList.add('opacity-50', 'ring-2', 'ring-teal-400');
                event.dataTransfer.setData('text/plain', draggedGroup.dataset.groupId ?? 'group');
                event.dataTransfer.effectAllowed = 'move';
                return;
            }

            event.preventDefault();
        });

        groupsContainer.addEventListener('dragend', () => {
            dragSource = null;
            draggedGroup = null;
            draggedItem = null;
            clearDragStyles();
            clearDropHighlights();
        });

        groupsContainer.addEventListener('dragover', (event) => {
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';

            if (draggedItem) {
                const container = resolveDropContainer(event.target, draggedItem);
                if (!container) return;

                clearDropHighlights();
                container.classList.add('ring-2', 'ring-teal-400', 'ring-inset', 'bg-teal-50/40');

                const targetRow = findItemRow(event.target);
                if (targetRow && targetRow !== draggedItem && container.contains(targetRow)) {
                    const rect = targetRow.getBoundingClientRect();
                    const after = event.clientY > rect.top + rect.height / 2;
                    if (after) {
                        targetRow.after(draggedItem);
                    } else {
                        targetRow.before(draggedItem);
                    }
                } else if (!targetRow || !container.contains(targetRow)) {
                    container.appendChild(draggedItem);
                }

                const groupCard = findGroupCard(container);
                if (groupCard) {
                    const groupId = groupCard.dataset.groupId;
                    const itemKey = draggedItem.dataset.item;
                    removeItemFromOtherGroups(itemKey, container);
                    draggedItem.querySelectorAll('[name]').forEach((input) => {
                        if (input.name.includes('[items][]')) {
                            input.name = `sidebar_groups[${groupId}][items][]`;
                        }
                    });
                    if (draggedItem.dataset.custom === '1') {
                        draggedItem.querySelectorAll('[name^="sidebar_modules"]').forEach((input) => {
                            input.name = input.name.replace(/sidebar_modules\[[^\]]+\]/, `sidebar_modules[${itemKey}]`);
                        });
                    }
                }
                return;
            }

            if (draggedGroup) {
                let targetCard = findGroupCard(event.target);
                if (!targetCard || targetCard === draggedGroup) {
                    return;
                }

                const rect = targetCard.getBoundingClientRect();
                const after = event.clientY > rect.top + rect.height / 2;
                if (after) {
                    targetCard.after(draggedGroup);
                } else {
                    targetCard.before(draggedGroup);
                }
            }
        });

        groupsContainer.addEventListener('drop', (event) => {
            event.preventDefault();
            clearDropHighlights();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSidebarSettings);
    } else {
        initSidebarSettings();
    }
</script>
@endpush
