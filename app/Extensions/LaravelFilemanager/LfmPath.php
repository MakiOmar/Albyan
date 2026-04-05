<?php

namespace App\Extensions\LaravelFilemanager;

use App\Services\LfmWebpConverter;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use UniSharp\LaravelFilemanager\LfmPath as BaseLfmPath;

class LfmPath extends BaseLfmPath
{
    /**
     * @param  UploadedFile|string  $file
     */
    public function upload($file)
    {
        $newFileName = parent::upload($file);

        return app(LfmWebpConverter::class)->convertAfterUpload($this, $newFileName);
    }
}
