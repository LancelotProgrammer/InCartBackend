<?php

use App\Filament\Pages\Settings;
use App\Filament\Resources\Advertisements\Pages\CreateAdvertisement;
use App\Filament\Resources\Advertisements\Pages\ListAdvertisements;
use App\Models\Advertisement;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Forms\Components\FileUpload;
use function Pest\Laravel\{actingAs, get};
use function Pest\Livewire\livewire;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

function assertModelData(string $table, array $data): void
{
    $model = DB::table($table)->latest()->first();
    expect($model)->not()->toBeNull();
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            expect($model->{$key})->toBe(json_encode($value, JSON_UNESCAPED_UNICODE));
        } else {
            expect($model->{$key})->toBe($value);
        }
    }
}

describe('Dashboard smoke test', function () {

    test('the login page returns a successful response', function () {
        get('/admin/login')->assertStatus(200);
    });

    describe('Dashboard pages smoke test', function () {

        beforeEach(function () {
            actingAs(User::where('email', 'owner@owner.com')->first());
        });

        test('the application returns a successful response', function () {
            get('/')->assertStatus(200);
        });
        test('the login page returns a redirect if user logged in', function () {
            get('/admin/login')->assertStatus(302);
        });
        test('the admin page returns a successful response', function () {
            get('/admin')->assertStatus(200);
        });
        // TODO: add action test
        test('the todays orders page returns a successful response', function () {
            get('/admin/todays-orders')->assertStatus(200);
        });
        // TODO: add action test
        test('the tickets orders page returns a successful response', function () {
            get('/admin/todays-tickets')->assertStatus(200);
        });
        test('the settings page returns a successful response', function () {
            get('/admin/settings')->assertStatus(200);
        });
        test('the settings are saved returns a successful response', function () {
            livewire(Settings::class)->callAction(TestAction::make('save'));
        });

        test('the telescope page returns a forbidden response', function () {
            get('/telescope')->assertStatus(403);
        });
        test('the api docs page returns a forbidden response', function () {
            get('/docs')->assertStatus(403);
        });
        test('the permissions list page returns a forbidden response', function () {
            get('/admin/permissions')->assertStatus(403);
        });
        test('the payment methods list page returns a forbidden response', function () {
            get('/admin/payment-methods')->assertStatus(403);
        });
    });

    describe('Resources smoke test', function () {

        beforeEach(function () {
            actingAs(User::where('email', 'owner@owner.com')->first());
        });

        describe('Advertisement smoke test', function () {

            test('the advertisements list page returns a successful response', function () {
                livewire(ListAdvertisements::class)->assertOk();
            });

            test('the advertisements create page returns a successful response', function () {
                livewire(CreateAdvertisement::class)->assertOk();
            });

            // TODO: test all advertisements types and test preview logic
            test('the advertisement is created and added to database', function () {

                FileUpload::configureUsing(function (FileUpload $component) {
                    $component->preserveFilenames();
                });

                livewire(CreateAdvertisement::class)
                    ->fillForm([
                        'title' => [['key' => 'en', 'value' => 'test'], ['key' => 'ar', 'value' => 'تجربه']],
                        'description' => [['key' => 'en', 'value' => 'test'], ['key' => 'ar', 'value' => 'تجربه']],
                        'order' => 1,
                        'branch_id' => 1,
                        'type' => 1,
                        'link' => 3,
                        'url' => 'http://example.com',
                        'product_id' => null,
                        'category_id' => null,
                    ])
                    ->set('data.file', UploadedFile::fake()->image('test_image.jpg', 300, 300))
                    ->call('create')
                    ->assertHasNoFormErrors();

                assertModelData('advertisements', [
                    'title' => json_decode('{"en":"test","ar":"تجربه"}', true),
                    'description' => json_decode('{"en":"test","ar":"تجربه"}', true),
                    'order' => 1,
                    'branch_id' => 1,
                    'type' => 1,
                    'url' => 'http://example.com',
                    'product_id' => null,
                    'category_id' => null,
                ]);
            });

            test('the advertisements are viewable', function () {
                $newData = Advertisement::factory()->create();
                livewire(ListAdvertisements::class)->callAction(TestAction::make('view')->table($newData));
            });

            test('the advertisements are publishable', function () {
                $newData = Advertisement::factory()->create(['published_at' => null]);
                livewire(ListAdvertisements::class)->callAction(TestAction::make('publish')->table($newData));
            });

            test('the advertisements are unpublishable', function () {
                $newData = Advertisement::factory()->create();
                livewire(ListAdvertisements::class)->callAction(TestAction::make('unpublish')->table($newData));
            });
        });
    });
})->group('dashboard-smoke-test');
