<form method="POST" action="{{ route('attendances.status.update', $attendance) }}" class="attendance-status-update-form">
    @csrf
    @method('PATCH')
    <select name="status" class="attendance-status-update-form__select" aria-label="{{ __('pages.attendance_manage.update_status') }}">
        @foreach(['valid', 'late', 'invalid_face', 'invalid_location', 'invalid_both'] as $statusValue)
            <option value="{{ $statusValue }}" @selected($attendance->status->value === $statusValue)>
                {{ \App\Enums\AttendanceStatus::from($statusValue)->label() }}
            </option>
        @endforeach
    </select>
    <button type="submit" class="attendance-status-update-form__btn">{{ __('app.save') }}</button>
</form>
