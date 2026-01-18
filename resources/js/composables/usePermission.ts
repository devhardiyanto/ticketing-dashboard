import { usePage } from '@inertiajs/vue3';

export function usePermission() {
  const page = usePage();

  const hasPermission = (name: string) => {
    const user = page.props.auth.user as any;
    if (!user || !user.permissions) return false;

    // Super Admin usually has all permissions, but based on Seeder,
    // they are assigned explicitly.
    // If there's a wildcard or super admin role check needed, handle here.
    // For now, checking the permissions array is sufficient as Seeder syncs all.

    return user.permissions.includes(name);
  };

  const can = (name: string) => hasPermission(name);

  return {
    hasPermission,
    can
  };
}
