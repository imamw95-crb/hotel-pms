<?php

/**
 * Check apakah user login memiliki permission
 */
if (! function_exists('hasPermission')) {
    function hasPermission($permission)
    {
        if (! auth()->check()) {
            return false;
        }

        return auth()->user()->hasPermission($permission);
    }
}

/**
 * Check apakah user login memiliki semua permissions
 */
if (! function_exists('hasAllPermissions')) {
    function hasAllPermissions($permissions)
    {
        if (! auth()->check()) {
            return false;
        }

        return auth()->user()->hasAllPermissions($permissions);
    }
}

/**
 * Check apakah user login memiliki salah satu permission
 */
if (! function_exists('hasAnyPermission')) {
    function hasAnyPermission($permissions)
    {
        if (! auth()->check()) {
            return false;
        }

        return auth()->user()->hasAnyPermission($permissions);
    }
}

/**
 * Get available menu items for current user
 */
if (! function_exists('getMenuItems')) {
    function getMenuItems($role = null)
    {
        if (! auth()->check() && ! $role) {
            return [];
        }

        $userRole = $role ?? auth()->user()->role;
        $menus = config('menus.items', []);

        return array_filter($menus, function ($menu) use ($userRole) {
            if (! isset($menu['roles'])) {
                return true;
            }
            $allowedRoles = is_array($menu['roles']) ? $menu['roles'] : [$menu['roles']];

            return in_array($userRole, $allowedRoles);
        });
    }
}

/**
 * Get menu items with permission checks and role filtering
 */
if (! function_exists('getMenuItemsWithPermissions')) {
    function getMenuItemsWithPermissions()
    {
        if (! auth()->check()) {
            return [];
        }

        $userRole = auth()->user()->role;
        $menus = config('menus.items', []);

        // Filter by role — hide items that specify roles not matching current user
        return array_values(array_filter($menus, function ($menu) use ($userRole) {
            if (isset($menu['roles'])) {
                $allowedRoles = is_array($menu['roles']) ? $menu['roles'] : [$menu['roles']];
                if (! in_array($userRole, $allowedRoles)) {
                    return false;
                }
            }
            // Also filter children by role
            if (isset($menu['children'])) {
                $menu['children'] = array_values(array_filter($menu['children'], function ($child) use ($userRole) {
                    if (isset($child['roles'])) {
                        $allowedRoles = is_array($child['roles']) ? $child['roles'] : [$child['roles']];

                        return in_array($userRole, $allowedRoles);
                    }

                    return true;
                }));
            }

            return true;
        }));
    }
}

/**
 * Convert number to Indonesian words (terbilang).
 */
if (! function_exists('terbilang')) {
    function terbilang($number)
    {
        $number = abs((int) $number);
        $words = [
            '', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan',
            'Sepuluh', 'Sebelas', 'Dua Belas', 'Tiga Belas', 'Empat Belas', 'Lima Belas',
            'Enam Belas', 'Tujuh Belas', 'Delapan Belas', 'Sembilan Belas',
        ];

        if ($number < 20) {
            return $words[$number];
        }

        if ($number < 100) {
            $tens = (int) ($number / 10);
            $remainder = $number % 10;
            $result = '';
            if ($tens === 1) {
                $result = 'Sepuluh';
            } else {
                $result = $words[$tens].' Puluh';
            }
            if ($remainder > 0) {
                $result .= ' '.$words[$remainder];
            }

            return $result;
        }

        if ($number < 200) {
            return 'Seratus '.terbilang($number - 100);
        }

        if ($number < 1000) {
            $hundreds = (int) ($number / 100);
            $remainder = $number % 100;
            $result = $words[$hundreds].' Ratus';
            if ($remainder > 0) {
                $result .= ' '.terbilang($remainder);
            }

            return $result;
        }

        if ($number < 2000) {
            return 'Seribu '.terbilang($number - 1000);
        }

        if ($number < 1000000) {
            $thousands = (int) ($number / 1000);
            $remainder = $number % 1000;
            $result = terbilang($thousands).' Ribu';
            if ($remainder > 0) {
                $result .= ' '.terbilang($remainder);
            }

            return $result;
        }

        if ($number < 1000000000) {
            $millions = (int) ($number / 1000000);
            $remainder = $number % 1000000;
            $result = terbilang($millions).' Juta';
            if ($remainder > 0) {
                $result .= ' '.terbilang($remainder);
            }

            return $result;
        }

        if ($number < 1000000000000) {
            $billions = (int) ($number / 1000000000);
            $remainder = $number % 1000000000;
            $result = terbilang($billions).' Miliar';
            if ($remainder > 0) {
                $result .= ' '.terbilang($remainder);
            }

            return $result;
        }

        return $number;
    }
}
