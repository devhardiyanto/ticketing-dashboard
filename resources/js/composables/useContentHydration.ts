import { useSignedUrl } from '@/composables/useSignedUrl'

export function useContentHydration() {
  const { getSignedUrls } = useSignedUrl()

  /**
   * Parse HTML and replace signed URLs for images with data-storage-path
   */
  const hydrateContent = async (html: string): Promise<string> => {
    if (!html) return html

    // Find all paths needing hydration
    // We do this by parsing DOM directly now to handle both attributes and fallback src


    const parser = new DOMParser()
    const doc = parser.parseFromString(html, 'text/html')
    const images = doc.querySelectorAll('img')
    const pathsToFetch: string[] = []

    // First pass: identify paths
    images.forEach((img) => {
      let path = img.getAttribute('data-storage-path')

      // Fallback: try to extract from src if it looks like a storage URL
      if (!path && img.src) {
        try {
          if (img.src.includes('editor-images/')) {
            const match = img.src.match(/(editor-images\/[^?]+)/)
            if (match) {
              path = match[1]
              // Auto-fix the attribute for future
              img.setAttribute('data-storage-path', path)
            }
          }
        } catch {
          // ignore invalid urls
        }
      }

      if (path) {
        pathsToFetch.push(path)
      }
    })

    if (pathsToFetch.length === 0) return html

    // Dedup
    const uniquePaths = [...new Set(pathsToFetch)]

    // Fetch new signed URLs
    const signedUrls = await getSignedUrls(uniquePaths) as Record<string, { success: boolean, url: string }>

    // Second pass: Replace src
    let hasChanges = false
    images.forEach((img) => {
      const path = img.getAttribute('data-storage-path')
      if (path && signedUrls[path]?.success && signedUrls[path]?.url) {
        // Only replace if URL supports it (prevent loop if same)
        if (img.src !== signedUrls[path].url) {
          img.setAttribute('src', signedUrls[path].url)
          hasChanges = true
        }
      }
    })

    return hasChanges ? doc.body.innerHTML : html
  }

  return {
    hydrateContent
  }
}
