<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketScanned implements ShouldBroadcast
{
	use Dispatchable, InteractsWithSockets, SerializesModels;

	/**
	 * Create a new event instance.
	 */
	public function __construct(
		public string $eventId,
		public string $ticketCode,
		public string $status, // 'success' | 'duplicate' | 'invalid'
		public ?string $attendeeName = null,
		public ?string $item = null,
		public ?string $scannedAt = null,
		public ?string $reason = null,
	) {
		//
	}

	/**
	 * Get the channels the event should broadcast on.
	 *
	 * @return array<int, \Illuminate\Broadcasting\Channel>
	 */
	public function broadcastOn(): array
	{
		return [
			new Channel("scanner.{$this->eventId}"),
		];
	}

	/**
	 * The event's broadcast name.
	 */
	public function broadcastAs(): string
	{
		return 'ticket.scanned';
	}
}

