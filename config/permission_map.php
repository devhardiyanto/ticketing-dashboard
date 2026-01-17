<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Permission Mapping
    |--------------------------------------------------------------------------
    |
    | This file maps Route Resource names (prefixes) to Permission Resource names.
    | Used by AutoCheckPermission middleware to determine which permission to check.
    |
    | Key: Route Prefix (e.g. 'user' from 'user.index')
    | Value: Permission Prefix (e.g. 'dashboard_users' for 'dashboard_users.read')
    |
    */

    'resources' => [
        'user' => 'dashboard_users',
        'role' => 'roles',
        'event' => 'events',
        'ticket_type' => 'tickets',
        'order' => 'orders',
        'organization' => 'organizations',
        'activity_log' => 'activity_logs',
        'system' => 'settings', // Assuming 'system' route maps to 'settings' permission
    ],

    /*
    |--------------------------------------------------------------------------
    | Action Mapping
    |--------------------------------------------------------------------------
    |
    | Maps Route Action suffixes to Permission Action suffixes.
    |
    */
    'actions' => [
        'index' => 'read',
        'show' => 'read',
        'data' => 'read',
        'check-slug' => 'read', // Custom read action
        'create' => 'create',
        'store' => 'create',
        'edit' => 'update',
        'update' => 'update',
        'toggle-status' => 'update', // Custom update action
        'destroy' => 'delete',
        'export' => 'export',
    ],
];
