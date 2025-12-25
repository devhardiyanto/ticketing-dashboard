<?php

namespace Database\Seeders;

use App\Models\Core\Event;
use App\Models\Core\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Get 'Konser Organizer' (created in OrganizationSeeder)
    $org = Organization::where('email', 'contact@konserorganizer.com')->first();

    if (!$org) {
      $this->command->error('Organization "Konser Organizer" not found. Please run OrganizationSeeder first.');
      return;
    }

    // 1. Summer Music Festival 2025 (Parent)
    $musicFest = Event::create([
      'id' => Str::uuid(),
      'organization_id' => $org->id,
      'name' => 'Summer Music Festival 2025',
      'description' => 'The biggest summer music festival in Jakarta.',
      'start_date' => '2025-07-20 10:00:00',
      'end_date' => '2025-07-22 23:00:00',
      'location' => 'GBK Senayan',
      'address' => 'Jl. Pintu Satu Senayan, Jakarta Pusat',
      'status' => 'published',
      'currency' => 'IDR',
      'is_parent' => true,
    ]);

    $this->createChildEvent($musicFest, 'Day 1 Pass', '2025-07-20 10:00:00', '2025-07-20 23:00:00');
    $this->createChildEvent($musicFest, 'Day 2 Pass', '2025-07-21 10:00:00', '2025-07-21 23:00:00');
    $this->createChildEvent($musicFest, 'VIP Weekend Pass', '2025-07-20 10:00:00', '2025-07-22 23:00:00');

    // 2. Tech Innovation Summit 2025 (Parent)
    $techSummit = Event::create([
      'id' => Str::uuid(),
      'organization_id' => $org->id,
      'name' => 'Tech Innovation Summit 2025',
      'description' => 'Exploring the future of AI and Technology.',
      'start_date' => '2025-09-15 09:00:00',
      'end_date' => '2025-09-16 17:00:00',
      'location' => 'Convention Center BSD',
      'address' => 'BSD City, Tangerang',
      'status' => 'published',
      'currency' => 'IDR',
      'is_parent' => true,
    ]);

    $this->createChildEvent($techSummit, 'Workshop: Generative AI', '2025-09-15 09:00:00', '2025-09-15 12:00:00');
    $this->createChildEvent($techSummit, 'Main Conference', '2025-09-15 13:00:00', '2025-09-16 17:00:00');

    // 3. Jakarta City Marathon 2025 (Parent)
    $marathon = Event::create([
      'id' => Str::uuid(),
      'organization_id' => $org->id,
      'name' => 'Jakarta City Marathon 2025',
      'description' => 'Run through the heart of the city.',
      'start_date' => '2025-10-26 05:00:00',
      'end_date' => '2025-10-26 12:00:00',
      'location' => 'Monas',
      'address' => 'Medan Merdeka, Jakarta Pusat',
      'status' => 'published',
      'currency' => 'IDR',
      'is_parent' => true,
    ]);

    $this->createChildEvent($marathon, '5K Fun Run', '2025-10-26 06:00:00', '2025-10-26 08:00:00');
    $this->createChildEvent($marathon, '10K Race', '2025-10-26 05:30:00', '2025-10-26 09:00:00');
    $this->createChildEvent($marathon, 'Full Marathon', '2025-10-26 05:00:00', '2025-10-26 12:00:00');

    $this->command->info('Events seeded: 3 Parents, 8 Children.');
  }

  private function createChildEvent(Event $parent, string $name, string $start, string $end)
  {
    Event::create([
      'id' => Str::uuid(),
      'organization_id' => $parent->organization_id,
      'parent_event_id' => $parent->id,
      'name' => $name,
      'description' => "Sub-event of {$parent->name}",
      'start_date' => $start,
      'end_date' => $end,
      'location' => $parent->location,
      'address' => $parent->address,
      'status' => 'published',
      'currency' => $parent->currency,
      'is_parent' => false,
    ]);
  }
}
