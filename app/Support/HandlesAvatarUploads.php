<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use RuntimeException;

trait HandlesAvatarUploads
{
    /**
     * Store the uploaded avatar on the public disk and optionally replace the existing one.
     */
    protected function storeAvatar(UploadedFile $file, ?string $existingPath = null): string
    {
        $driver = extension_loaded('imagick') ? 'imagick' : 'gd';
        $manager = new ImageManager(['driver' => $driver]);

        $image = $manager->make($file->getRealPath())->fit(512, 512, function ($constraint): void {
            $constraint->upsize();
        });

        $extension = strtolower($file->extension() ?: 'jpg');
        $extension = $extension === 'jpeg' ? 'jpg' : $extension;

        $path = 'avatars/'.Str::uuid().'.'.$extension;

        $disk = Storage::disk('public');
        $disk->makeDirectory('avatars');

        $stored = $disk->put($path, (string) $image->encode($extension, 90));

        if ($stored === false) {
            throw new RuntimeException('Failed to store avatar on the public disk.');
        }

        if ($existingPath) {
            $disk->delete($existingPath);
        }

        return $path;
    }
}
