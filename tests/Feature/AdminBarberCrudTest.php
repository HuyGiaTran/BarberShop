<?php

namespace Tests\Feature;

use App\Models\Barber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminBarberCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_barber_with_avatar_and_linked_user(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.barbers.store'), [
            'name' => 'Barber Demo',
            'email' => 'barber@example.com',
            'phone' => '0900000000',
            'password' => 'secret123',
            'bio' => 'Chuyên fade và uốn nam.',
            'avatar' => $this->makeImageUpload(),
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.barbers.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'barber@example.com',
            'role' => 'barber',
        ]);

        $barber = Barber::first();

        $this->assertNotNull($barber);
        $this->assertSame('Barber Demo', $barber->name);
        $this->assertTrue($barber->is_active);
        $this->assertNotNull($barber->avatar);
        Storage::disk('public')->assertExists($barber->avatar);
    }

    public function test_admin_can_update_and_delete_a_barber(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $barberUser = User::factory()->create([
            'role' => 'barber',
            'phone' => '0911111111',
        ]);

        Storage::disk('public')->put('barbers/old-avatar.jpg', 'avatar');

        $barber = Barber::create([
            'user_id' => $barberUser->id,
            'name' => 'Barber Cu',
            'phone' => '0911111111',
            'bio' => 'Bio cũ',
            'avatar' => 'barbers/old-avatar.jpg',
            'is_active' => true,
        ]);

        $updateResponse = $this->actingAs($admin)->put(route('admin.barbers.update', $barber), [
            'name' => 'Barber Moi',
            'email' => 'new-barber@example.com',
            'phone' => '0922222222',
            'bio' => 'Bio mới',
            'is_active' => '0',
        ]);

        $updateResponse->assertRedirect(route('admin.barbers.index'));

        $barber->refresh();
        $barberUser->refresh();

        $this->assertSame('Barber Moi', $barber->name);
        $this->assertSame('0922222222', $barber->phone);
        $this->assertFalse($barber->is_active);
        $this->assertSame('new-barber@example.com', $barberUser->email);

        $deleteResponse = $this->actingAs($admin)->delete(route('admin.barbers.destroy', $barber));

        $deleteResponse->assertRedirect(route('admin.barbers.index'));

        $this->assertDatabaseMissing('barbers', [
            'id' => $barber->id,
        ]);
        $this->assertDatabaseMissing('users', [
            'id' => $barberUser->id,
        ]);
        Storage::disk('public')->assertMissing('barbers/old-avatar.jpg');
    }

    private function makeImageUpload(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'barber-avatar');

        file_put_contents($path, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9pA4rxgAAAAASUVORK5CYII='
        ));

        return new UploadedFile(
            $path,
            'barber.png',
            'image/png',
            null,
            true
        );
    }
}
