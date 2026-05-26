@php
    $menuItems = getMenuItemsWithPermissions();

    $isParentActive = function($item) {
        if (!isset($item['children'])) return false;
        foreach ($item['children'] as $child) {
            if (isset($child['route']) && request()->routeIs($child['route'])) {
                return true;
            }
        }
        return false;
    };

    $isChildActive = function($route) {
        return request()->routeIs($route);
    };
@endphp

<nav class="sidebar-nav" id="sidebarNav">
    <ul class="menu-list">
        @foreach($menuItems as $index => $item)
            @php
                $hasChildren = isset($item['children']) && count($item['children']) > 0;
                $parentActive = $hasChildren ? $isParentActive($item) : false;
                $itemId = 'menu-' . $index;
            @endphp

            {{-- Single menu item (no children) --}}
            @if(!$hasChildren)
                @if(!isset($item['permission']) || hasPermission($item['permission']))
                    <li class="menu-item {{ $isChildActive($item['route']) ? 'active' : '' }}">
                        <a href="{{ route($item['route']) }}" class="menu-link">
                            <span class="menu-icon">
                                <i class="fas fa-{{ $item['icon'] ?? 'circle' }}"></i>
                            </span>
                            <span class="menu-label">{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endif

            {{-- Parent menu item with children --}}
            @else
                @php
                    $hasVisibleChild = false;
                    foreach ($item['children'] as $child) {
                        if (!isset($child['permission']) || hasPermission($child['permission'])) {
                            $hasVisibleChild = true;
                            break;
                        }
                    }
                @endphp

                @if($hasVisibleChild)
                    <li class="menu-item has-submenu {{ $parentActive ? 'active open' : '' }}" id="{{ $itemId }}">
                        <button class="menu-link menu-toggle" onclick="toggleSubmenu('{{ $itemId }}')" type="button">
                            <span class="menu-icon">
                                <i class="fas fa-{{ $item['icon'] ?? 'folder' }}"></i>
                            </span>
                            <span class="menu-label">{{ $item['label'] }}</span>
                            <span class="menu-arrow">
                                <i class="fas fa-chevron-right"></i>
                            </span>
                        </button>
                        <ul class="submenu">
                            @foreach($item['children'] as $child)
                                @if(!isset($child['permission']) || hasPermission($child['permission']))
                                    <li class="submenu-item {{ $isChildActive($child['route']) ? 'active' : '' }}">
                                        <a href="{{ route($child['route']) }}" class="submenu-link">
                                            <span class="submenu-dot"></span>
                                            <span class="submenu-label">{{ $child['label'] }}</span>
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </li>
                @endif
            @endif
        @endforeach
    </ul>
</nav>

<style>
    /* ── Sidebar Navigation ── */
    .sidebar-nav {
        padding: 0.5rem 0;
    }

    .menu-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    /* ── Menu Item ── */
    .menu-item {
        margin: 0.15rem 0.5rem;
        border-radius: 0.5rem;
        overflow: hidden;
    }

    .menu-link {
        display: flex;
        align-items: center;
        width: 100%;
        padding: 0.65rem 0.85rem;
        color: #cbd5e1;
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
        border: none;
        background: none;
        cursor: pointer;
        transition: all 0.2s ease;
        border-radius: 0.5rem;
    }

    .menu-link:hover {
        color: #ffffff;
        background-color: rgba(255, 255, 255, 0.08);
    }

    .menu-item.active > .menu-link {
        color: #ffffff;
        background-color: rgba(59, 130, 246, 0.3);
        box-shadow: inset 3px 0 0 0 #60a5fa;
    }

    /* ── Menu Icon ── */
    .menu-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.5rem;
        margin-right: 0.65rem;
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    /* ── Menu Label ── */
    .menu-label {
        flex: 1;
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ── Menu Arrow ── */
    .menu-arrow {
        font-size: 0.7rem;
        transition: transform 0.3s ease;
        color: #94a3b8;
        flex-shrink: 0;
    }

    .menu-item.open .menu-arrow {
        transform: rotate(90deg);
    }

    /* ── Submenu ── */
    .submenu {
        list-style: none;
        margin: 0;
        padding: 0.25rem 0 0.25rem 2.2rem;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease, padding 0.3s ease;
    }

    .menu-item.open .submenu {
        max-height: 500px;
        padding-bottom: 0.4rem;
    }

    /* ── Submenu Item ── */
    .submenu-item {
        margin: 0.1rem 0;
    }

    .submenu-link {
        display: flex;
        align-items: center;
        padding: 0.45rem 0.75rem;
        color: #94a3b8;
        text-decoration: none;
        font-size: 0.813rem;
        font-weight: 400;
        border-radius: 0.375rem;
        transition: all 0.2s ease;
    }

    .submenu-link:hover {
        color: #e2e8f0;
        background-color: rgba(255, 255, 255, 0.05);
    }

    .submenu-item.active .submenu-link {
        color: #60a5fa;
        background-color: rgba(59, 130, 246, 0.15);
        font-weight: 500;
    }

    /* ── Submenu Dot ── */
    .submenu-dot {
        display: inline-block;
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background-color: #64748b;
        margin-right: 0.65rem;
        flex-shrink: 0;
        transition: background-color 0.2s ease;
    }

    .submenu-item.active .submenu-dot {
        background-color: #60a5fa;
    }

    .submenu-link:hover .submenu-dot {
        background-color: #94a3b8;
    }
</style>

<script>
    function toggleSubmenu(itemId) {
        const menuItem = document.getElementById(itemId);
        if (!menuItem) return;

        const isOpen = menuItem.classList.contains('open');

        // Close all other open submenus
        document.querySelectorAll('.menu-item.has-submenu.open').forEach(function(item) {
            if (item.id !== itemId) {
                item.classList.remove('open');
            }
        });

        // Toggle current submenu
        if (isOpen) {
            menuItem.classList.remove('open');
        } else {
            menuItem.classList.add('open');
        }
    }

    // Auto-open submenu if a child is active
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.menu-item.has-submenu.active').forEach(function(item) {
            item.classList.add('open');
        });
    });
</script>
