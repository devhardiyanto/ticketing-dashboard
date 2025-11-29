import { ref } from 'vue';

const timezones = ref<string[]>([]);
const loading = ref(false);
const error = ref<string | null>(null);

export function useTimezones() {
  const fetchTimezones = async () => {
    if (timezones.value.length > 0) return; // Return cached if available

    loading.value = true;
    error.value = null;
    try {
      const response = await fetch('https://timeapi.io/api/TimeZone/AvailableTimeZones');
      if (!response.ok) {
        throw new Error('Failed to fetch timezones');
      }
      timezones.value = await response.json();
    } catch (e: any) {
      error.value = e.message;
      console.error('Error fetching timezones:', e);
    } finally {
      loading.value = false;
    }
  };

  return {
    timezones,
    loading,
    error,
    fetchTimezones,
  };
}
