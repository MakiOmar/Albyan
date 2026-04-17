<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminBlogExportTest extends TestCase
{
    public function test_blog_export_requires_authentication(): void
    {
        $response = $this->get('/admin/blog/export');

        $response->assertRedirect();
    }
}
