<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class ScannerController extends Controller
{
	private string $coreApiUrl;

	public function __construct()
	{
		$this->coreApiUrl = config('services.core.url', env('CORE_API_URL', 'http://localhost:3002'));
	}

	/**
	 * Display the scanner page
	 */
	public function index()
	{
		return Inertia::render('scanner/Index');
	}

	/**
	 * Validate a scanned ticket by calling Core API
	 */
	public function validate(Request $request)
	{
		$validated = $request->validate([
			'qrPayload' => 'required|string',
			'scanLocation' => 'nullable|string|max:255',
			'eventId' => 'nullable|string', // For broadcasting to specific event channel
			'mode' => 'nullable|string|in:checkin,checkout,redeem',
		]);

		$modeMap = [
			'checkin' => 'check-in',
			'checkout' => 'check-out',
			'redeem' => 'redeem',
		];
		$activityType = $modeMap[$validated['mode'] ?? 'checkin'] ?? 'check-in';

		try {
			$response = Http::post("{$this->coreApiUrl}/api/scanner/validate", [
				'qrPayload' => $validated['qrPayload'],
				'scanLocation' => $validated['scanLocation'] ?? null,
				'activityType' => $activityType,
			]);

			$responseData = $response->json();

			// Broadcast to realtime channel if eventId provided and scan was processed
			if (isset($validated['eventId']) && isset($responseData['data'])) {
				$data = $responseData['data'];
				event(new \App\Events\TicketScanned(
					eventId: $validated['eventId'],
					ticketCode: $data['ticketCode'] ?? $validated['qrPayload'],
					status: $data['status'] ?? 'invalid',
					attendeeName: $data['attendeeName'] ?? null,
					item: $data['item'] ?? $data['ticketType'] ?? null,
					scannedAt: now()->toIso8601String(),
					reason: $data['reason'] ?? null,
				));
			}

			return response()->json($responseData, $response->status());
		} catch (\Exception $e) {
			return response()->json([
				'success' => false,
				'data' => [
					'status' => 'invalid',
					'reason' => 'Failed to connect to validation service',
				],
			], 500);
		}
	}

	/**
	 * Display the scan history page
	 */
	public function history(Request $request)
	{
		return Inertia::render('scanner/History');
	}

	/**
	 * Fetch scan history data from Core API
	 */
	public function historyData(Request $request)
	{
		$params = $request->only(['eventId', 'status', 'startDate', 'endDate', 'page', 'limit']);

		try {
			$response = Http::get("{$this->coreApiUrl}/api/scanner/history", $params);
			return response()->json($response->json(), $response->status());
		} catch (\Exception $e) {
			return response()->json([
				'success' => false,
				'message' => 'Failed to fetch scan history',
			], 500);
		}
	}

	/**
	 * Display the attendance dashboard page
	 */
	public function attendance(Request $request)
	{
		return Inertia::render('scanner/Attendance');
	}

	/**
	 * Fetch attendance statistics from Core API
	 */
	public function attendanceData(Request $request)
	{
		$params = $request->only(['eventId']);

		try {
			$response = Http::get("{$this->coreApiUrl}/api/scanner/attendance", $params);
			return response()->json($response->json(), $response->status());
		} catch (\Exception $e) {
			return response()->json([
				'success' => false,
				'message' => 'Failed to fetch attendance data',
			], 500);
		}
	}
}
