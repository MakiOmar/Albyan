<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Section;
use Illuminate\Database\Seeder;

/**
 * Seeds only Site FAQs permissions (sections 1195-1199) for role_id 2.
 * Run after SectionsTableSeeder. Use when full PermissionsTableSeeder fails.
 */
class SiteFaqPermissionsSeeder extends Seeder
{
    public function run()
    {
        $sectionIds = [1195, 1196, 1197, 1198, 1199];
        $roleId = 2;

        foreach ($sectionIds as $sectionId) {
            if (!Section::where('id', $sectionId)->exists()) {
                $this->command->warn("Section {$sectionId} does not exist. Run: php artisan db:seed --class=SectionsTableSeeder");
                continue;
            }
            Permission::updateOrCreate(
                ['id' => $sectionId],
                ['role_id' => $roleId, 'section_id' => $sectionId, 'allow' => 1]
            );
        }

        $this->command->info('Site FAQs permissions seeded for role_id ' . $roleId . '.');
    }
}
