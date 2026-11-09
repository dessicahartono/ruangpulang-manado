@props(['member'])

<div class="admin-row">
    <div>
        <h3>{{ $member->full_name }}</h3>
        <small>{{ $member->nickname }}{{ $member->role ? ', '.$member->role : '' }}</small>
    </div>
    <div class="admin-meta">{{ $member->division->name }}</div>
    <div class="admin-meta">
        @if ($member->missingData())
            <span class="member-status--missing">Belum ada {{ implode(' dan ', $member->missingData()) }}</span>
        @else
            Lengkap
        @endif
    </div>
    <div class="admin-actions">
        <x-button variant="outline" class="btn--sm" :href="route('manage.members.show', $member)">Tinjau</x-button>
    </div>
</div>
