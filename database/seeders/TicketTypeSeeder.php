<?php

namespace Database\Seeders;

use App\Models\Core\Event;
use App\Models\Core\TicketType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TicketTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all published parent events
        $events = Event::where('status', 'published')
            ->whereNull('parent_event_id')
            ->get();

        if ($events->isEmpty()) {
            $this->command->error('No events found. Please run EventSeeder first.');
            return;
        }

        $ticketCount = 0;

        foreach ($events as $event) {
            $this->createTicketsForEvent($event);
            $ticketCount++;
        }

        $this->command->info("Ticket types seeded for {$ticketCount} events.");
    }

    private function createTicketsForEvent(Event $event): void
    {
        $eventName = $event->name;

        // Different ticket configurations based on event type
        if (str_contains(strtolower($eventName), 'music') || str_contains(strtolower($eventName), 'festival')) {
            $this->createMusicFestivalTickets($event);
        } elseif (str_contains(strtolower($eventName), 'tech') || str_contains(strtolower($eventName), 'summit')) {
            $this->createConferenceTickets($event);
        } elseif (str_contains(strtolower($eventName), 'marathon') || str_contains(strtolower($eventName), 'run')) {
            $this->createSportsTickets($event);
        } else {
            $this->createGenericTickets($event);
        }
    }

    /**
     * Music Festival Tickets - Multiple tiers with varying availability
     */
    private function createMusicFestivalTickets(Event $event): void
    {
        // Early Bird - Sold Out
        TicketType::create([
            'id' => Str::uuid(),
            'event_id' => $event->id,
            'name' => 'Early Bird',
            'category' => 'General Admission',
            'description' => 'Limited early bird tickets. Best price!',
            'price' => 350000,
            'quantity' => 500,
            'quantity_available' => 0,
            'quantity_sold' => 500,
            'max_per_order' => 4,
            'start_sale_date' => now()->subMonths(3),
            'end_sale_date' => now()->subMonths(2),
            'status' => 'active',
            'is_hidden' => false,
            'sort_order' => 1,
        ]);

        // Regular - Available
        TicketType::create([
            'id' => Str::uuid(),
            'event_id' => $event->id,
            'name' => 'Regular',
            'category' => 'General Admission',
            'description' => 'Standard festival access. Includes all main stage performances.',
            'price' => 550000,
            'quantity' => 2000,
            'quantity_available' => 1200,
            'quantity_sold' => 800,
            'max_per_order' => 6,
            'start_sale_date' => now()->subMonths(2),
            'end_sale_date' => $event->start_date,
            'status' => 'active',
            'is_hidden' => false,
            'sort_order' => 2,
        ]);

        // VIP - Limited (20% remaining)
        TicketType::create([
            'id' => Str::uuid(),
            'event_id' => $event->id,
            'name' => 'VIP',
            'category' => 'VIP Access',
            'description' => 'VIP lounge access, premium viewing area, complimentary drinks.',
            'price' => 1250000,
            'quantity' => 500,
            'quantity_available' => 80,
            'quantity_sold' => 420,
            'max_per_order' => 4,
            'start_sale_date' => now()->subMonths(2),
            'end_sale_date' => $event->start_date,
            'status' => 'active',
            'is_hidden' => false,
            'sort_order' => 3,
        ]);

        // VVIP - Sold Out
        TicketType::create([
            'id' => Str::uuid(),
            'event_id' => $event->id,
            'name' => 'VVIP',
            'category' => 'VIP Access',
            'description' => 'Backstage access, meet & greet with artists, exclusive merchandise.',
            'price' => 3500000,
            'quantity' => 50,
            'quantity_available' => 0,
            'quantity_sold' => 50,
            'max_per_order' => 2,
            'start_sale_date' => now()->subMonths(2),
            'end_sale_date' => $event->start_date,
            'status' => 'active',
            'is_hidden' => false,
            'sort_order' => 4,
        ]);

        // Student Discount - Hidden (not yet released)
        TicketType::create([
            'id' => Str::uuid(),
            'event_id' => $event->id,
            'name' => 'Student Discount',
            'category' => 'Student',
            'description' => 'Special price for students with valid ID. Must show ID at entrance.',
            'price' => 300000,
            'quantity' => 200,
            'quantity_available' => 200,
            'quantity_sold' => 0,
            'max_per_order' => 2,
            'start_sale_date' => now()->addDays(7),
            'end_sale_date' => $event->start_date,
            'status' => 'active',
            'is_hidden' => true,
            'sort_order' => 5,
        ]);
    }

    /**
     * Conference/Summit Tickets
     */
    private function createConferenceTickets(Event $event): void
    {
        // Full Conference Pass
        TicketType::create([
            'id' => Str::uuid(),
            'event_id' => $event->id,
            'name' => 'Full Conference',
            'category' => 'General Admission',
            'description' => 'Access to all sessions, workshops, and networking events.',
            'price' => 2500000,
            'quantity' => 300,
            'quantity_available' => 150,
            'quantity_sold' => 150,
            'max_per_order' => 5,
            'start_sale_date' => now()->subMonths(2),
            'end_sale_date' => $event->start_date,
            'status' => 'active',
            'is_hidden' => false,
            'sort_order' => 1,
        ]);

        // Workshop Only
        TicketType::create([
            'id' => Str::uuid(),
            'event_id' => $event->id,
            'name' => 'Workshop Only',
            'category' => 'Workshop',
            'description' => 'Access to hands-on workshop sessions only.',
            'price' => 750000,
            'quantity' => 100,
            'quantity_available' => 45,
            'quantity_sold' => 55,
            'max_per_order' => 3,
            'start_sale_date' => now()->subMonths(1),
            'end_sale_date' => $event->start_date,
            'status' => 'active',
            'is_hidden' => false,
            'sort_order' => 2,
        ]);

        // Virtual Pass (FREE)
        TicketType::create([
            'id' => Str::uuid(),
            'event_id' => $event->id,
            'name' => 'Virtual Pass',
            'category' => 'Free Entry',
            'description' => 'Free online streaming access to keynote sessions.',
            'price' => 0,
            'quantity' => 1000,
            'quantity_available' => 650,
            'quantity_sold' => 350,
            'max_per_order' => 1,
            'start_sale_date' => now()->subMonths(1),
            'end_sale_date' => $event->start_date,
            'status' => 'active',
            'is_hidden' => false,
            'sort_order' => 3,
        ]);

        // VIP Executive
        TicketType::create([
            'id' => Str::uuid(),
            'event_id' => $event->id,
            'name' => 'VIP Executive',
            'category' => 'VIP Access',
            'description' => 'Priority seating, exclusive networking dinner, 1-on-1 speaker sessions.',
            'price' => 7500000,
            'quantity' => 30,
            'quantity_available' => 5,
            'quantity_sold' => 25,
            'max_per_order' => 2,
            'start_sale_date' => now()->subMonths(2),
            'end_sale_date' => $event->start_date,
            'status' => 'active',
            'is_hidden' => false,
            'sort_order' => 4,
        ]);
    }

    /**
     * Sports/Marathon Tickets
     */
    private function createSportsTickets(Event $event): void
    {
        // 5K Fun Run
        TicketType::create([
            'id' => Str::uuid(),
            'event_id' => $event->id,
            'name' => '5K Fun Run',
            'category' => 'General Admission',
            'description' => 'Casual 5K run for all ages. Includes finisher medal and t-shirt.',
            'price' => 150000,
            'quantity' => 1000,
            'quantity_available' => 400,
            'quantity_sold' => 600,
            'max_per_order' => 5,
            'start_sale_date' => now()->subMonths(3),
            'end_sale_date' => $event->start_date->subDays(3),
            'status' => 'active',
            'is_hidden' => false,
            'sort_order' => 1,
        ]);

        // 10K Race
        TicketType::create([
            'id' => Str::uuid(),
            'event_id' => $event->id,
            'name' => '10K Race',
            'category' => 'General Admission',
            'description' => 'Timed 10K race. Includes timing chip, medal, and race kit.',
            'price' => 250000,
            'quantity' => 800,
            'quantity_available' => 200,
            'quantity_sold' => 600,
            'max_per_order' => 3,
            'start_sale_date' => now()->subMonths(3),
            'end_sale_date' => $event->start_date->subDays(3),
            'status' => 'active',
            'is_hidden' => false,
            'sort_order' => 2,
        ]);

        // Full Marathon - Almost Sold Out
        TicketType::create([
            'id' => Str::uuid(),
            'event_id' => $event->id,
            'name' => 'Full Marathon',
            'category' => 'General Admission',
            'description' => '42.195km full marathon. Includes premium race kit and finisher jacket.',
            'price' => 450000,
            'quantity' => 500,
            'quantity_available' => 50,
            'quantity_sold' => 450,
            'max_per_order' => 2,
            'start_sale_date' => now()->subMonths(3),
            'end_sale_date' => $event->start_date->subDays(3),
            'status' => 'active',
            'is_hidden' => false,
            'sort_order' => 3,
        ]);

        // Kids Run (FREE)
        TicketType::create([
            'id' => Str::uuid(),
            'event_id' => $event->id,
            'name' => 'Kids Run',
            'category' => 'Free Entry',
            'description' => 'Free 1K run for children under 12. Parent supervision required.',
            'price' => 0,
            'quantity' => 300,
            'quantity_available' => 150,
            'quantity_sold' => 150,
            'max_per_order' => 3,
            'start_sale_date' => now()->subMonths(2),
            'end_sale_date' => $event->start_date->subDays(3),
            'status' => 'active',
            'is_hidden' => false,
            'sort_order' => 4,
        ]);

        // Elite Runner (Invitation)
        TicketType::create([
            'id' => Str::uuid(),
            'event_id' => $event->id,
            'name' => 'Elite Runner',
            'category' => 'Invitation Only',
            'description' => 'For professional athletes. Prize money eligible.',
            'price' => 0,
            'quantity' => 50,
            'quantity_available' => 20,
            'quantity_sold' => 30,
            'max_per_order' => 1,
            'start_sale_date' => now()->subMonths(4),
            'end_sale_date' => $event->start_date->subDays(7),
            'status' => 'active',
            'is_hidden' => true,
            'sort_order' => 5,
        ]);
    }

    /**
     * Generic Event Tickets
     */
    private function createGenericTickets(Event $event): void
    {
        TicketType::create([
            'id' => Str::uuid(),
            'event_id' => $event->id,
            'name' => 'Regular',
            'category' => 'General Admission',
            'description' => 'Standard event access.',
            'price' => 100000,
            'quantity' => 500,
            'quantity_available' => 300,
            'quantity_sold' => 200,
            'max_per_order' => 5,
            'start_sale_date' => now()->subMonth(),
            'end_sale_date' => $event->start_date,
            'status' => 'active',
            'is_hidden' => false,
            'sort_order' => 1,
        ]);

        TicketType::create([
            'id' => Str::uuid(),
            'event_id' => $event->id,
            'name' => 'VIP',
            'category' => 'VIP Access',
            'description' => 'Premium access with exclusive benefits.',
            'price' => 300000,
            'quantity' => 100,
            'quantity_available' => 50,
            'quantity_sold' => 50,
            'max_per_order' => 3,
            'start_sale_date' => now()->subMonth(),
            'end_sale_date' => $event->start_date,
            'status' => 'active',
            'is_hidden' => false,
            'sort_order' => 2,
        ]);
    }
}
