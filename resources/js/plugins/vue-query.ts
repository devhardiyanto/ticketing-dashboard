import { VueQueryPlugin, type VueQueryPluginOptions } from '@tanstack/vue-query';
import { QueryClient } from '@tanstack/vue-query';

// Create a client
export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      refetchOnWindowFocus: false,
      retry: 1,
      staleTime: 5 * 60 * 1000, // 5 minutes
    },
  },
});

export const vueQueryOptions: VueQueryPluginOptions = {
  queryClient,
};

export { VueQueryPlugin };
