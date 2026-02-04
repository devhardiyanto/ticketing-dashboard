<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useEchoPublic } from '@laravel/echo-vue';
import AppLayout from '@/layouts/AppLayout.vue';
import QRReader from '@/components/scanner/QRReader.vue';
import ScanResult from '@/components/scanner/ScanResult.vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ScanQrCode, Keyboard, Volume2, VolumeX } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

interface ScanResultData {
	status: 'success' | 'duplicate' | 'invalid';
	ticketCode?: string;
	attendeeName?: string;
	ticketType?: string;
	eventName?: string;
	firstScannedAt?: string;
	reason?: string;
}

interface RealtimeScanEvent {
	eventId: string;
	ticketCode: string;
	status: 'success' | 'duplicate' | 'invalid';
	attendeeName?: string;
	ticketType?: string;
	scannedAt?: string;
	reason?: string;
}

// Props from Inertia (optional: for event-specific scanning)
const props = defineProps<{
	eventId?: string;
}>();

const breadcrumbs = [
	{ title: 'Scanner', href: '/scanner' },
];

const scanResult = ref<ScanResultData | null>(null);
const isLoading = ref(false);
const manualInput = ref('');
const showManualInput = ref(false);
const soundEnabled = ref(true);
const scanFeedback = ref<'idle' | 'success' | 'error'>('idle');
const recentScans = ref<RealtimeScanEvent[]>([]);
const realtimeConnected = ref(false);

// HID Scanner Buffer
let hidBuffer = '';
let hidLastKeyTime = 0;
const HID_latency_threshold = 50; // ms

// Realtime listener for scan events from other devices
if (props.eventId) {
	useEchoPublic<RealtimeScanEvent>(
		`scanner.${props.eventId}`,
		'.ticket.scanned', // Note: prefixed with dot for custom event names
		(data) => {
			// Add to recent scans (keep last 10)
			recentScans.value = [data, ...recentScans.value.slice(0, 9)];

			// Show toast for realtime updates
			if (data.status === 'success') {
				toast.info(`${data.attendeeName || 'Guest'} checked in`);
			}
		}
	);
	realtimeConnected.value = true;
}

// Audio for feedback
const playSound = (type: 'success' | 'error') => {
	if (!soundEnabled.value) return;

	try {
		const audioContext = new (window.AudioContext || (window as any).webkitAudioContext)();
		const oscillator = audioContext.createOscillator();
		const gainNode = audioContext.createGain();

		oscillator.connect(gainNode);
		gainNode.connect(audioContext.destination);

		if (type === 'success') {
			oscillator.frequency.value = 800;
			oscillator.type = 'sine';
			gainNode.gain.value = 0.3;
		} else {
			oscillator.frequency.value = 300;
			oscillator.type = 'square';
			gainNode.gain.value = 0.2;
		}

		oscillator.start();
		setTimeout(() => {
			oscillator.stop();
			audioContext.close();
		}, 200);
	} catch (error) {
		console.error('Audio error:', error);
		// Audio not supported
	}
};

const showFeedback = (type: 'success' | 'error') => {
	scanFeedback.value = type;
	setTimeout(() => {
		scanFeedback.value = 'idle';
	}, 1000);
};

const validateTicket = async (payload: string) => {
	if (isLoading.value) return;

	isLoading.value = true;
	scanResult.value = null;

	try {
		const response = await fetch('/scanner/validate', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
			},
			body: JSON.stringify({
				qrPayload: payload,
				eventId: props.eventId, // Include eventId for broadcasting
			}),
		});

		const data = await response.json();

		if (data.success && data.data) {
			scanResult.value = data.data;

			if (data.data.status === 'success') {
				playSound('success');
				showFeedback('success');
				toast.success('Ticket validated successfully');
			} else {
				playSound('error');
				showFeedback('error');
				if (data.data.status === 'duplicate') {
					toast.warning('Ticket already scanned');
				} else {
					toast.error('Invalid ticket');
				}
			}
		} else {
			scanResult.value = {
				status: 'invalid',
				reason: data.message || 'Validation failed',
			};
			playSound('error');
			showFeedback('error');
			toast.error('Validation failed');
		}
	} catch (error) {
		scanResult.value = {
			status: 'invalid',
			reason: 'Network error. Please try again.',
		};
		playSound('error');
		showFeedback('error');
		toast.error('Network error');
	} finally {
		isLoading.value = false;
	}
};

const handleScan = (payload: string) => {
	validateTicket(payload);
};

const handleManualSubmit = () => {
	if (manualInput.value.trim()) {
		validateTicket(manualInput.value.trim());
		manualInput.value = '';
	}
};

const handleError = (error: Error) => {
	console.error('QR Reader error:', error);
	showManualInput.value = true;
};

const toggleSound = () => {
	soundEnabled.value = !soundEnabled.value;
};

// HID Scanner Listener
const onKeyDown = (e: KeyboardEvent) => {
	// Ignore if user is typing in an input field
	const target = e.target as HTMLElement;
	if (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA') return;

	const currentTime = Date.now();

	// If it's been a while since the last key, reset buffer (assuming new scan or manual stray key)
	if (currentTime - hidLastKeyTime > HID_latency_threshold * 2 && hidBuffer.length > 0) {
		hidBuffer = '';
	}

	hidLastKeyTime = currentTime;

	if (e.key === 'Enter') {
		if (hidBuffer.length > 0) {
			handleScan(hidBuffer);
			hidBuffer = '';
		}
	} else if (e.key.length === 1) { // Only capture printable characters
		hidBuffer += e.key;
	}
};

onMounted(() => {
	window.addEventListener('keydown', onKeyDown);
});

onUnmounted(() => {
	window.removeEventListener('keydown', onKeyDown);
});
</script>

<template>
	<Head title="Scanner" />

	<AppLayout :breadcrumbs="breadcrumbs">
		<div class="flex h-full flex-1 flex-col gap-4 p-4 relative">
			<!-- Visual Feedback Overlay -->
			<div
				v-if="scanFeedback !== 'idle'"
				class="absolute inset-0 z-50 flex items-center justify-center bg-opacity-80 transition-opacity duration-300 pointer-events-none"
				:class="{
					'bg-green-500/80': scanFeedback === 'success',
					'bg-red-500/80': scanFeedback === 'error'
				}"
			>
				<div class="text-white text-6xl font-bold animate-bounce">
					{{ scanFeedback === 'success' ? 'SUCCESS' : 'ERROR' }}
				</div>
			</div>

			<div class="flex items-center justify-between">
				<h1 class="text-2xl font-semibold">Ticket Scanner</h1>
				<Button variant="ghost" size="icon" @click="toggleSound">
					<Volume2 v-if="soundEnabled" class="h-5 w-5" />
					<VolumeX v-else class="h-5 w-5" />
				</Button>
			</div>

			<div class="grid gap-4 lg:grid-cols-2">
				<!-- Scanner Section -->
				<Card>
					<CardHeader>
						<CardTitle class="flex items-center gap-2">
							<ScanQrCode class="h-5 w-5" />
							QR Code Scanner
						</CardTitle>
						<CardDescription>
							Point your camera at a ticket QR code OR use a handheld scanner.
						</CardDescription>
					</CardHeader>
					<CardContent class="space-y-4">
						<!-- QR Reader -->
						<QRReader
							v-if="!showManualInput"
							@scan="handleScan"
							@error="handleError"
						/>

						<!-- Toggle to manual input -->
						<div class="flex justify-center">
							<Button
								variant="outline"
								size="sm"
								@click="showManualInput = !showManualInput"
							>
								<Keyboard class="mr-2 h-4 w-4" />
								{{ showManualInput ? 'Use Camera' : 'Manual Input' }}
							</Button>
						</div>

						<!-- Manual input -->
						<div v-if="showManualInput" class="space-y-2">
							<Label for="manual-code">Enter QR Payload</Label>
							<div class="flex gap-2">
								<Input
									id="manual-code"
									v-model="manualInput"
									placeholder="Paste or type the QR code content..."
									@keyup.enter="handleManualSubmit"
								/>
								<Button
									@click="handleManualSubmit"
									:disabled="isLoading || !manualInput.trim()"
								>
									Validate
								</Button>
							</div>
						</div>
					</CardContent>
				</Card>

				<!-- Result Section -->
				<div class="space-y-4">
					<h2 class="text-lg font-medium">Scan Result</h2>
					<ScanResult :result="scanResult" />

					<!-- Loading state -->
					<div
						v-if="isLoading"
						class="flex items-center justify-center py-8"
					>
						<div class="border-primary h-8 w-8 animate-spin rounded-full border-4 border-t-transparent" />
					</div>
				</div>
			</div>
		</div>
	</AppLayout>
</template>
