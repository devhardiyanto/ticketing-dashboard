export const ROLES = {
  SUPER_ADMIN: 'super_admin',
  PLATFORM_STAFF: 'platform_staff',
  ORG_ADMIN: 'org_admin',
  ORG_STAFF: 'org_staff',
} as const;

export type RoleName = (typeof ROLES)[keyof typeof ROLES];
