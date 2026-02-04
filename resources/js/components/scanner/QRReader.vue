<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';

interface Props {
  modelValue?: boolean;
}

interface Emits {
  (e: 'scan', payload: string): void;
  (e: 'error', error: Error): void;
  (e: 'update:modelValue', value: boolean): void;
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: true,
});

const emit = defineEmits<Emits>();

const videoRef = ref<HTMLVideoElement | null>(null);
const canvasRef = ref<HTMLCanvasElement | null>(null);
const stream = ref<MediaStream | null>(null);
const scanning = ref(false);
const errorMessage = ref<string | null>(null);
const hasCamera = ref(true);

let animationFrameId: number | null = null;

// Dynamically import QR code reader library
let jsQR: any = null;

const startCamera = async () => {
  try {
    errorMessage.value = null;

    // Dynamically import jsQR
    if (!jsQR) {
      const module = await import('jsqr');
      jsQR = module.default;
    }

    stream.value = await navigator.mediaDevices.getUserMedia({
      video: {
        facingMode: 'environment',
        width: { ideal: 1280 },
        height: { ideal: 720 },
      },
    });

    if (videoRef.value) {
      videoRef.value.srcObject = stream.value;
      await videoRef.value.play();
      scanning.value = true;
      scanFrame();
    }
  } catch (err) {
    hasCamera.value = false;
    errorMessage.value = err instanceof Error ? err.message : 'Failed to access camera';
    emit('error', err instanceof Error ? err : new Error('Failed to access camera'));
  }
};

const stopCamera = () => {
  scanning.value = false;
  if (animationFrameId) {
    cancelAnimationFrame(animationFrameId);
    animationFrameId = null;
  }
  if (stream.value) {
    stream.value.getTracks().forEach((track) => track.stop());
    stream.value = null;
  }
};

const scanFrame = () => {
  if (!scanning.value || !videoRef.value || !canvasRef.value || !jsQR) return;

  const video = videoRef.value;
  const canvas = canvasRef.value;
  const ctx = canvas.getContext('2d');

  if (!ctx || video.readyState !== video.HAVE_ENOUGH_DATA) {
    animationFrameId = requestAnimationFrame(scanFrame);
    return;
  }

  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;
  ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

  const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
  const code = jsQR(imageData.data, imageData.width, imageData.height);

  if (code?.data) {
    emit('scan', code.data);
    // Brief pause after successful scan
    scanning.value = false;
    setTimeout(() => {
      if (props.modelValue) {
        scanning.value = true;
        scanFrame();
      }
    }, 1500);
  } else {
    animationFrameId = requestAnimationFrame(scanFrame);
  }
};

onMounted(() => {
  if (props.modelValue) {
    startCamera();
  }
});

onUnmounted(() => {
  stopCamera();
});

defineExpose({
  startCamera,
  stopCamera,
});
</script>

<template>
	<div class="relative overflow-hidden rounded-lg bg-black">
		<!-- Video feed -->
		<video
			ref="videoRef"
			class="h-auto w-full"
			autoplay
			playsinline
			muted
		/>

		<!-- Hidden canvas for processing -->
		<canvas ref="canvasRef" class="hidden" />

		<!-- Scanning overlay -->
		<div
			v-if="scanning"
			class="pointer-events-none absolute inset-0 flex items-center justify-center"
		>
			<div class="border-primary h-48 w-48 rounded-lg border-2 opacity-50" />
		</div>

		<!-- Error state -->
		<div
			v-if="errorMessage"
			class="bg-destructive/10 text-destructive absolute inset-0 flex flex-col items-center justify-center p-4"
		>
			<p class="text-center text-sm">{{ errorMessage }}</p>
			<button
				class="bg-primary text-primary-foreground mt-4 rounded-md px-4 py-2 text-sm"
				@click="startCamera"
			>
				Retry
			</button>
		</div>

		<!-- No camera state -->
		<div
			v-if="!hasCamera && !errorMessage"
			class="bg-muted absolute inset-0 flex flex-col items-center justify-center p-4"
		>
			<p class="text-muted-foreground text-center text-sm">
				Camera not available. Please use manual input.
			</p>
		</div>
	</div>
</template>
