<?php

namespace Tests\Unit;

use App\Services\LfmWebpConverter;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;
use UniSharp\LaravelFilemanager\LfmPath;

class LfmWebpConverterTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_converts_png_upload_to_webp_and_removes_original(): void
    {
        if (! function_exists('imagewebp')) {
            $this->markTestSkipped('PHP WebP support (imagewebp) not available.');
        }

        Storage::fake('upload');
        Config::set('lfm.disk', 'upload');
        Config::set('lfm.webp.enabled', true);
        Config::set('lfm.webp.quality', 80);
        Config::set('lfm.webp.convert_extensions', ['jpg', 'jpeg', 'png']);

        $relative = 'image/1/test_convert.png';
        Storage::disk('upload')->makeDirectory(dirname($relative));
        $pngBinary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        Storage::disk('upload')->put($relative, $pngBinary);

        $absolute = Storage::disk('upload')->path($relative);

        $lfm = Mockery::mock(LfmPath::class);
        $lfm->shouldReceive('setName')->with('test_convert.png')->andReturnSelf();
        $lfm->shouldReceive('path')->with('storage')->andReturn($relative);
        $lfm->shouldReceive('path')->with('absolute')->andReturn($absolute);

        $converter = new LfmWebpConverter;
        $result = $converter->convertAfterUpload($lfm, 'test_convert.png');

        $this->assertSame('test_convert.webp', $result);
        Storage::disk('upload')->assertMissing($relative);
        Storage::disk('upload')->assertExists('image/1/test_convert.webp');
    }

    public function test_leaves_filename_unchanged_when_disabled(): void
    {
        Storage::fake('upload');
        Config::set('lfm.disk', 'upload');
        Config::set('lfm.webp.enabled', false);

        $lfm = Mockery::mock(LfmPath::class);

        $converter = new LfmWebpConverter;
        $result = $converter->convertAfterUpload($lfm, 'photo.jpg');

        $this->assertSame('photo.jpg', $result);
    }
}
