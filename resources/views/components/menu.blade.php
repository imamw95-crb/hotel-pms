@php
    $menuItems = getMenuItemsWithPermissions();
@endphp

@foreach($menuItems as $item)
    {{-- Parent menu item --}}
    @if(isset($item['route']))
        <a href="{{ route($item['route']) }}"
           class="sidebar-item block py-2.5 px-4 rounded transition duration-200
                  @if(request()->routeIs($item['route']))
                      active
                  @else
                      hover:bg-blue-700
                  @endif">
            <i class="fas fa-{{ $item['icon'] ?? 'circle' }} w-5 mr-2"></i>
            {{ $item['label'] }}
        </a>
    @else
        <div class="py-2.5 px-4 text-blue-300 text-xs uppercase tracking-wider font-semibold mt-4">
            <i class="fas fa-{{ $item['icon'] ?? 'folder' }} w-5 mr-2"></i>
            {{ $item['label'] }}
        </div>
    @endif

    {{-- Child menu items — flat, no dropdown --}}
    @if(isset($item['children']) && count($item['children']) > 0)
        @foreach($item['children'] as $child)
            @if(!isset($child['permission']) || hasPermission($child['permission']))
                <a href="{{ route($child['route']) }}"
                   class="sidebar-item block py-2 pl-10 pr-4 rounded transition duration-200 text-sm
                          @if(request()->routeIs($child['route']))
                              active
                          @else
                              hover:bg-blue-700 text-blue-100
                          @endif">
                    <i class="fas fa-angle-right w-4 mr-2 text-blue-400"></i>
                    {{ $child['label'] }}
                </a>
            @endif
        @endforeach
    @endif
@endforeach

