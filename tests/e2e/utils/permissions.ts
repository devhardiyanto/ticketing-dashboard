import { RoleType } from './auth';

/**
 * Permission matrix for each role
 * Based on PermissionSeeder.php and config/sidebar.php
 *
 * This is the source of truth for expected permissions and sidebar items per role.
 * Use this to verify UI elements match user's actual permissions.
 */

export interface RolePermissionConfig {
  permissions: string[];
  sidebar: string[];
  events: {
    canCreate: boolean;
    canEdit: boolean;
    canDelete: boolean;
    canPublish: boolean;
  };
}

/**
 * Complete permission matrix for all roles
 * Sidebar items are the menu titles that should be visible for each role
 */
export const rolePermissions: Record<RoleType, RolePermissionConfig> = {
  super_admin: {
    permissions: ['*'], // All permissions
    sidebar: [
      'Dashboard',
      'Analytics',
      'Users',
      'Roles',
      'Organizations',
      'User Assignment',
      'Events',
      'Ticket Types',
      'Orders',
      'Banners',
      'Platform Fee',
      'Activity Logs',
    ],
    events: {
      canCreate: true,
      canEdit: true,
      canDelete: true,
      canPublish: true,
    },
  },

  platform_staff: {
    permissions: [
      'events.read',
      'tickets.read',
      'orders.read',
      'orders.update',
      'orders.export',
      'organizations.view_any',
      'organizations.read',
      'users.read',
      'roles.read',
      'activity_logs.read',
      'activity_logs.export',
      'reports.read',
      'reports.export',
      'settings.read',
    ],
    sidebar: [
      'Dashboard',
      'Analytics',
      'Users',
      'Roles',
      'Organizations',
      'Events',
      'Ticket Types',
      'Orders',
      'Activity Logs',
    ],
    events: {
      canCreate: false,
      canEdit: false,
      canDelete: false,
      canPublish: false,
    },
  },

  org_admin: {
    permissions: [
      'events.create',
      'events.read',
      'events.update',
      'events.delete',
      'events.publish',
      'tickets.create',
      'tickets.read',
      'tickets.update',
      'tickets.delete',
      'orders.read',
      'orders.update',
      'orders.export',
      'organizations.read',
      'organizations.users.manage',
      'organizations.users.create',
      'organizations.users.update',
      'organizations.users.delete',
      'reports.read',
      'reports.export',
      'settings.read',
      'settings.update',
    ],
    sidebar: [
      'Dashboard',
      'Analytics',
      'User Assignment',
      'Events',
      'Ticket Types',
      'Orders',
      'Banners',
      'Platform Fee',
    ],
    events: {
      canCreate: true,
      canEdit: true,
      canDelete: true,
      canPublish: true,
    },
  },

  org_staff: {
    permissions: [
      'events.read',
      'events.update',
      'tickets.read',
      'tickets.update',
      'orders.read',
      'orders.update',
      'users.read',
      'reports.read',
      'settings.read',
    ],
    sidebar: [
      'Dashboard',
      'Analytics',
      'Users',
      'Events',
      'Ticket Types',
      'Orders',
    ],
    events: {
      canCreate: false,
      canEdit: true,
      canDelete: false,
      canPublish: false,
    },
  },
};

/**
 * Get expected sidebar items for a role
 */
export function getExpectedSidebar(role: RoleType): string[] {
  return rolePermissions[role].sidebar;
}

/**
 * Get event permissions for a role
 */
export function getEventPermissions(role: RoleType): RolePermissionConfig['events'] {
  return rolePermissions[role].events;
}

/**
 * Check if a role has a specific permission
 */
export function roleHasPermission(role: RoleType, permission: string): boolean {
  const config = rolePermissions[role];
  if (config.permissions.includes('*')) return true;
  return config.permissions.includes(permission);
}
