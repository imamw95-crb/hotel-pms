<?php

/**
 * Check apakah user login memiliki permission
 */
if (!function_exists('hasPermission')) {
    function hasPermission($permission)
    {
        if (!auth()->check()) {
            return false;
        }
        return auth()->user()->hasPermission($permission);
    }
}

/**
 * Check apakah user login memiliki semua permissions
 */
if (!function_exists('hasAllPermissions')) {
    function hasAllPermissions($permissions)
    {
        if (!auth()->check()) {
            return false;
        }
        return auth()->user()->hasAllPermissions($permissions);
    }
}

/**
 * Check apakah user login memiliki salah satu permission
 */
if (!function_exists('hasAnyPermission')) {
    function hasAnyPermission($permissions)
    {
        if (!auth()->check()) {
            return false;
        }
        return auth()->user()->hasAnyPermission($permissions);
    }
}

/**
 * Get available menu items for current user
 */
if (!function_exists('getMenuItems')) {
    function getMenuItems($role = null)
    {
        if (!auth()->check() && !$role) {
            return [];
        }

        $userRole = $role ?? auth()->user()->role;
        $menus = config('menus.items', []);

        return array_filter($menus, function ($menu) use ($userRole) {
            if (!isset($menu['roles'])) {
                return true;
            }
            $allowedRoles = is_array($menu['roles']) ? $menu['roles'] : [$menu['roles']];
            return in_array($userRole, $allowedRoles);
        });
    }
}

/**
 * Get menu items with permission checks
 */
if (!function_exists('getMenuItemsWithPermissions')) {
    function getMenuItemsWithPermissions()
    {
        if (!auth()->check()) {
            return [];
        }

        $menus = config('menus.items', []);

        // Return all menu items — no role filtering
        // Permission checks are handled at the route middleware level
        return $menus;
    }
}
