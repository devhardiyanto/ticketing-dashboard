import { usePage } from '@inertiajs/vue3';
import { useAuthCache } from './useAuthCache';

export function usePermission() {
  const page = usePage();
  const { getPermissions, hasCache, isExpired } = useAuthCache();

  const hasPermission = (name: string) => {
    // Try cache first (if valid)
    if (hasCache() && !isExpired()) {
      const cachedPermissions = getPermissions();
      if (cachedPermissions.length > 0) {
        return cachedPermissions.includes(name);
      }
    }

    // Fallback to Inertia props
    const user = page.props.auth.user as any;
    if (!user || !user.permissions) return false;

    return user.permissions.includes(name);
  };

  const can = (name: string) => hasPermission(name);

  return {
    hasPermission,
    can
  };
}

