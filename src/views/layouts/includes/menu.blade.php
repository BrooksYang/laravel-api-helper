<aside class="menu">

    <ul class="menu-list">
        <li>
            <a href="{{ url("api/docs") }}" class="{{ Request::path() == 'api/docs' ? 'is-active' : '' }}">
                全部 ({{ $total }})
            </a>
        </li>
    </ul>

    @foreach ($modules as $group => $module)
        {{-- Group --}}
        @if (count($modules) > 1)
            <p class="menu-label">
                {{ $group }}
            </p>
        @endif

        {{-- Module --}}
        <ul class="menu-list">
            @foreach ($module as $item)
                <li>
                    <a href="{{ url("api/docs/{$item['group']}/{$item['module']}") }}"
                       class="{{ Request::route()->parameter('group') == $item['group'] && Request::route()->parameter('module') == $item['module'] ? 'is-active' : '' }}">
                        {{ $item['module'] }}
                    </a>
                </li>
            @endforeach
        </ul>

    @endforeach
</aside>
