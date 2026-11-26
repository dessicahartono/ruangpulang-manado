@props(['group'])

@isset($group->description)
    <div class="group-desc">
        @foreach ($group->local('description') as $paragraph)
            <p>{{ $paragraph }}</p>
        @endforeach
    </div>
@endisset
<div class="grid grid--founders">
    @foreach ($group->members as $member)
        <x-member-card :member="$member" />
    @endforeach
</div>
