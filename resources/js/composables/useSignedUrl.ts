import { ref } from 'vue'
import axios from 'axios'

interface SignedUrlResponse {
  success: boolean
  url?: string
  path?: string
  expires_at?: string
  message?: string
}

interface SignedUrlsResponse {
  success: boolean
  urls: Record<string, SignedUrlResponse>
}

interface CachedUrl {
  url: string
  expiresAt: Date
}

// Cache storage for signed URLs
const urlCache = new Map<string, CachedUrl>()

// Buffer time before expiry to refresh (5 minutes)
const REFRESH_BUFFER_MS = 5 * 60 * 1000

/**
 * Check if a cached URL is still valid
 */
function isUrlValid(cached: CachedUrl | undefined): boolean {
  if (!cached) return false
  return new Date().getTime() < cached.expiresAt.getTime() - REFRESH_BUFFER_MS
}

/**
 * Composable for managing signed URLs from MinIO/S3 storage
 */
export function useSignedUrl() {
  const loading = ref(false)
  const error = ref<string | null>(null)

  /**
   * Get a signed URL for a single storage path
   */
  async function getSignedUrl(path: string, forceRefresh = false): Promise<string | null> {
    if (!path) return null

    // Check cache first
    const cached = urlCache.get(path)
    if (!forceRefresh && isUrlValid(cached)) {
      return cached!.url
    }

    loading.value = true
    error.value = null

    try {
      const response = await axios.post<SignedUrlResponse>('/upload/signed-url', { path })

      if (response.data.success && response.data.url) {
        // Cache the URL
        urlCache.set(path, {
          url: response.data.url,
          expiresAt: new Date(response.data.expires_at || Date.now() + 3600000),
        })
        return response.data.url
      } else {
        error.value = response.data.message || 'Failed to get signed URL'
        return null
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Failed to get signed URL'
      return null
    } finally {
      loading.value = false
    }
  }

  /**
   * Get signed URLs for multiple storage paths (batch)
   */
  async function getSignedUrls(
    paths: string[],
    forceRefresh = false
  ): Promise<Record<string, string | null>> {
    const results: Record<string, string | null> = {}
    const pathsToFetch: string[] = []

    // Check cache first for each path
    for (const path of paths) {
      if (!path) {
        results[path] = null
        continue
      }

      const cached = urlCache.get(path)
      if (!forceRefresh && isUrlValid(cached)) {
        results[path] = cached!.url
      } else {
        pathsToFetch.push(path)
      }
    }

    // If all paths are cached, return immediately
    if (pathsToFetch.length === 0) {
      return results
    }

    loading.value = true
    error.value = null

    try {
      const response = await axios.post<SignedUrlsResponse>('/upload/signed-urls', {
        paths: pathsToFetch,
      })

      if (response.data.success && response.data.urls) {
        for (const [path, data] of Object.entries(response.data.urls)) {
          if (data.success && data.url) {
            urlCache.set(path, {
              url: data.url,
              expiresAt: new Date(data.expires_at || Date.now() + 3600000),
            })
            results[path] = data.url
          } else {
            results[path] = null
          }
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Failed to get signed URLs'
      // Mark all unfetched paths as null
      for (const path of pathsToFetch) {
        results[path] = null
      }
    } finally {
      loading.value = false
    }

    return results
  }

  /**
   * Clear cached URL for a path
   */
  function clearCache(path?: string) {
    if (path) {
      urlCache.delete(path)
    } else {
      urlCache.clear()
    }
  }

  /**
   * Upload a file and return the storage path
   */
  async function uploadFile(
    file: File,
    folder = 'uploads'
  ): Promise<{ success: boolean; path?: string; error?: string }> {
    loading.value = true
    error.value = null

    try {
      const formData = new FormData()
      formData.append('file', file)
      formData.append('folder', folder)

      const response = await axios.post('/upload', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      })

      if (response.data.success) {
        return { success: true, path: response.data.path }
      } else {
        error.value = response.data.message || 'Upload failed'
        return { success: false, error: error.value || 'Upload failed' }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Upload failed'
      return { success: false, error: error.value || 'Upload failed' }
    } finally {
      loading.value = false
    }
  }

  /**
   * Delete a file from storage
   */
  async function deleteFile(path: string): Promise<boolean> {
    if (!path) return false

    loading.value = true
    error.value = null

    try {
      const response = await axios.delete('/upload', { data: { path } })
      if (response.data.success) {
        clearCache(path)
        return true
      }
      error.value = response.data.message || 'Delete failed'
      return false
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Delete failed'
      return false
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    error,
    getSignedUrl,
    getSignedUrls,
    uploadFile,
    deleteFile,
    clearCache,
  }
}
