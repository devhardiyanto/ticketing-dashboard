import { ref } from 'vue';
import { Roles } from '@/types';

/**
 * Sidebar menu item structure from backend
 */
export interface SidebarMenuItem {
  title: string;
  url: string;
  icon: string;
  href: string;
  permission?: string;
}

export interface SidebarMenuGroup {
  group: string;
  items: SidebarMenuItem[];
}

/**
 * Auth cache structure stored in localStorage
 */
export interface AuthCache {
  permissions: string[];
  roles: Roles[];
  sidebar_menu: SidebarMenuGroup[];
  cached_at: string;
  ttl: number; // milliseconds
}

const CACHE_KEY = 'auth_cache';
const DEFAULT_TTL = 30 * 60 * 1000; // 30 minutes in milliseconds

/**
 * Composable for managing auth cache in localStorage
 * Implements hybrid strategy: clear on logout + TTL auto-refresh
 */
export function useAuthCache() {
  const isLoading = ref(false);

  /**
   * Check if cache exists and is not expired
   */
  const isExpired = (): boolean => {
    const cache = getCache();
    if (!cache) return true;

    const cachedTime = new Date(cache.cached_at).getTime();
    const now = Date.now();
    return now - cachedTime > cache.ttl;
  };

  /**
   * Check if cache exists (regardless of expiry)
   */
  const hasCache = (): boolean => {
    try {
      return localStorage.getItem(CACHE_KEY) !== null;
    } catch {
      return false;
    }
  };

  /**
   * Get cache from localStorage
   */
  const getCache = (): AuthCache | null => {
    try {
      const cached = localStorage.getItem(CACHE_KEY);
      if (!cached) return null;
      return JSON.parse(cached) as AuthCache;
    } catch {
      // Invalid or corrupted cache
      clearCache();
      return null;
    }
  };

  /**
   * Save cache to localStorage
   */
  const setCache = (data: Omit<AuthCache, 'cached_at' | 'ttl'>): void => {
    try {
      const cache: AuthCache = {
        ...data,
        cached_at: new Date().toISOString(),
        ttl: DEFAULT_TTL,
      };
      localStorage.setItem(CACHE_KEY, JSON.stringify(cache));
    } catch (e) {
      console.warn('Failed to save auth cache to localStorage:', e);
    }
  };

  /**
   * Clear cache from localStorage (called on logout)
   */
  const clearCache = (): void => {
    try {
      localStorage.removeItem(CACHE_KEY);
    } catch (e) {
      console.warn('Failed to clear auth cache:', e);
    }
  };

  /**
   * Refresh cache from API
   */
  const refreshCache = async (): Promise<AuthCache | null> => {
    isLoading.value = true;
    try {
      const response = await fetch('/api/auth/session-data', {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        credentials: 'same-origin',
      });

      if (!response.ok) {
        throw new Error('Failed to fetch session data');
      }

      const data = await response.json();
      setCache({
        permissions: data.permissions,
        roles: data.roles,
        sidebar_menu: data.sidebar_menu,
      });

      return getCache();
    } catch (e) {
      console.warn('Failed to refresh auth cache:', e);
      return null;
    } finally {
      isLoading.value = false;
    }
  };

  /**
   * Get cached permissions or empty array
   */
  const getPermissions = (): string[] => {
    const cache = getCache();
    return cache?.permissions ?? [];
  };

  /**
   * Get cached roles or empty array
   */
  const getRoles = (): Roles[] => {
    const cache = getCache();
    return cache?.roles ?? [];
  };

  /**
   * Get cached sidebar menu or empty array
   */
  const getSidebarMenu = (): SidebarMenuGroup[] => {
    const cache = getCache();
    return cache?.sidebar_menu ?? [];
  };

  /**
   * Initialize cache from Inertia props if not cached
   * Call this on app mount or after login
   */
  const initFromProps = (props: {
    permissions?: string[];
    roles?: Roles[];
    sidebar_menu?: SidebarMenuGroup[];
  }): void => {
    // Only set cache if we have data from props
    if (props.permissions && props.roles && props.sidebar_menu) {
      setCache({
        permissions: props.permissions,
        roles: props.roles,
        sidebar_menu: props.sidebar_menu,
      });
    }
  };

  /**
   * Ensure cache is valid, refresh if expired
   * Returns cached data or null if refresh needed and failed
   */
  const ensureCache = async (props?: {
    permissions?: string[];
    roles?: Roles[];
    sidebar_menu?: SidebarMenuGroup[];
  }): Promise<AuthCache | null> => {
    // If cache exists and not expired, return it
    if (hasCache() && !isExpired()) {
      return getCache();
    }

    // If we have fresh props, use them to populate cache
    if (props?.permissions && props?.roles && props?.sidebar_menu) {
      initFromProps(props);
      return getCache();
    }

    // Otherwise, refresh from API
    return await refreshCache();
  };

  return {
    isLoading,
    isExpired,
    hasCache,
    getCache,
    setCache,
    clearCache,
    refreshCache,
    getPermissions,
    getRoles,
    getSidebarMenu,
    initFromProps,
    ensureCache,
  };
}
