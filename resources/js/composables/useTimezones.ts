import { ref } from 'vue';
import timezonesData from '@/data/timezones.json';

const timezones = ref<string[]>(timezonesData);
const loading = ref(false);
const error = ref<string | null>(null);

export function useTimezones() {
  const fetchTimezones = async () => {
    // No-op, kept for compatibility or if we want to re-enable fetching later
    // timezones are already loaded from JSON
  };

  return {
    timezones,
    loading,
    error,
    fetchTimezones,
  };
}
