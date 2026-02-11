<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\Section;
use Illuminate\Console\Command;

/**
 * Ensures Site FAQs sections and admin permissions exist (e.g. after deploy to production).
 * Run: php artisan site-faq:install
 */
class SiteFaqInstallCommand extends Command
{
    protected $signature = 'site-faq:install {--role=2 : Admin role ID to grant permissions}';

    protected $description = 'Ensure Site FAQs sections and permissions exist (for production deploy)';

    public function handle()
    {
        $roleId = (int) $this->option('role');

        // Sections 1195–1199 (must match SectionsTableSeeder)
        $sections = [
            1195 => ['name' => 'admin_site_faqs', 'caption' => 'site faqs'],
            1196 => ['name' => 'admin_site_faqs_list', 'section_group_id' => 1195, 'caption' => 'site faqs list'],
            1197 => ['name' => 'admin_site_faqs_create', 'section_group_id' => 1195, 'caption' => 'site faqs create'],
            1198 => ['name' => 'admin_site_faqs_edit', 'section_group_id' => 1195, 'caption' => 'site faqs edit'],
            1199 => ['name' => 'admin_site_faqs_delete', 'section_group_id' => 1195, 'caption' => 'site faqs delete'],
        ];

        foreach ($sections as $id => $attrs) {
            Section::updateOrCreate(['id' => $id], $attrs);
        }
        $this->info('Sections 1195–1199 (Site FAQs) ensured.');

        foreach (array_keys($sections) as $sectionId) {
            Permission::updateOrCreate(
                ['id' => $sectionId],
                ['role_id' => $roleId, 'section_id' => $sectionId, 'allow' => 1]
            );
        }
        $this->info("Permissions for role_id {$roleId} (Site FAQs) ensured.");

        $this->comment('Site FAQs should now appear in the admin sidebar. Clear cache if needed: php artisan cache:clear');

        return 0;
    }
}
