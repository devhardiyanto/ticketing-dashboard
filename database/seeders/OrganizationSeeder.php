<?php

namespace Database\Seeders;

use App\Models\Core\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seed test organizations to core.organizations table (cross-schema)
     */
    public function run(): void
    {
        $organizations = [
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Konser Organizer Indonesia',
                'business_type' => 'company',
                'email' => 'contact@konserorganizer.com',
                'phone_number' => '+62812345678',
                'address' => 'Jl. Sudirman No. 123, Jakarta Selatan',
                'tax_id' => '01.234.567.8-901.000',
                'status' => 'active',
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Sport Events Pro',
                'business_type' => 'company',
                'email' => 'info@sportevents.com',
                'phone_number' => '+62823456789',
                'address' => 'Jl. Gatot Subroto No. 45, Jakarta Pusat',
                'tax_id' => '02.345.678.9-012.000',
                'status' => 'active',
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Festival Nusantara',
                'business_type' => 'individual',
                'email' => 'hello@festivalnusantara.id',
                'phone_number' => '+62834567890',
                'address' => 'Jl. Thamrin No. 78, Jakarta Pusat',
                'tax_id' => null,
                'status' => 'active',
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Tech Conference Indonesia',
                'business_type' => 'company',
                'email' => 'contact@techconference.id',
                'phone_number' => '+62845678901',
                'address' => 'Jl. HR Rasuna Said No. 90, Jakarta Selatan',
                'tax_id' => '03.456.789.0-123.000',
                'status' => 'active',
            ],
        ];

        foreach ($organizations as $orgData) {
            Organization::firstOrCreate(
                ['email' => $orgData['email']],
                $orgData
            );
        }

        $this->command->info('Organizations seeded successfully to core.organizations!');
    }
}
