<?php

namespace Tests\Feature;

use App\Support\HandlesAvatarUploads;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HandlesAvatarUploadsTest extends TestCase
{
    public function test_store_avatar_saves_file_to_public_disk(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('avatar.jpg', 600, 600);

        $uploader = new class {
            use HandlesAvatarUploads {
                storeAvatar as public store;
            }
        };

        $path = $uploader->store($file);

        $this->assertNotEmpty($path);
        Storage::disk('public')->assertExists($path);
    }
}
