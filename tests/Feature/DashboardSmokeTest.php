<?php

use App\Filament\Pages\Settings;
use App\Filament\Resources\Advertisements\Pages\CreateAdvertisement;
use App\Filament\Resources\Advertisements\Pages\ListAdvertisements;
use App\Models\Advertisement;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Forms\Components\FileUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

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
        test('the todays orders page returns a successful response', function () {
            get('/admin/todays-orders')->assertStatus(200);
        });
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

            test('the advertisement list page returns a successful response', function () {
                livewire(ListAdvertisements::class)->assertOk();
            });

            test('the orders list tabs works', function () {});

            test('the advertisement create page returns a successful response', function () {
                livewire(CreateAdvertisement::class)->assertOk();
            });

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
                        'type' => 1, // status
                        'link' => 3, // external
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

            test('the advertisement is viewable', function () {
                $newData = Advertisement::factory()->create();
                livewire(ListAdvertisements::class)->callAction(TestAction::make('view')->table($newData));
            });

            test('the advertisement is publishable', function () {
                $newData = Advertisement::factory()->create(['published_at' => null]);
                livewire(ListAdvertisements::class)->callAction(TestAction::make('publish')->table($newData));
            });

            test('the advertisement is unpublishable', function () {
                $newData = Advertisement::factory()->create();
                livewire(ListAdvertisements::class)->callAction(TestAction::make('unpublish')->table($newData));
            });
        });

        describe('Branch smoke test', function () {
            test('the branch list page returns a successful response', function () {});
            test('the branch is publishable', function () {});
            test('the branch is unpublishable', function () {});
            test('the branch is created and added to database', function () {});
            test('the branch is edited and saved to database', function () {});
            test('the branch relation manager are viewable', function () {});
            test('the branch user relation manager attach users', function () {});
            test('the branch user relation manager detach users', function () {});
        });

        describe('Category smoke test', function () {
            test('the category list page returns a successful response', function () {});
            test('the category categories list page returns a successful response', function () {});
            test('the category product list page returns a successful response', function () {});
            test('the category is publishable', function () {});
            test('the category is unpublishable', function () {});
            test('the child category is created and added to database', function () {});
            test('the category is created and added to database', function () {});
            test('the category product list page has go button and returns a successful response', function () {});
        });

        describe('City smoke test', function () {
            test('the city list page returns a successful response', function () {});
            test('the city is created and added to database', function () {});
            test('the city is viewable', function () {});
        });

        describe('Coupon smoke test', function () {
            test('the coupons list page returns a successful response', function () {});
            test('the coupon is created and added to database', function () {});
            test('the coupon code is viewable', function () {});
            test('the coupon is publishable', function () {});
            test('the coupon is unpublishable', function () {});
        });

        describe('Feedback smoke test', function () {
            test('the feedback list page returns a successful response', function () {});
            test('the feedback can be bulk deleted', function () {});
            test('the feedback is viewable', function () {});
            test('the feedback is deletable', function () {});
            test('the feedback can be marked as important', function () {});
            test('the feedback can be unmarked as important', function () {});
            test('the feedback can be processed', function () {});
        });

        describe('Archive order smoke test', function () {
            test('the Archive orders list page returns a successful response', function () {});
        });

        describe('Order smoke test', function () {
            test('the orders list page returns a successful response', function () {});
            test('the orders list tabs works', function () {});
            test('the order is viewable', function () {});
            test('the order can generate an invoice', function () {});
            test('the order is created and added to database', function () {});
            test('the order is edited and saved to database', function () {});
            test('the order has audit list and returns a successful response', function () {});
            test('the order has cart list and returns a successful response', function () {});
            test('the order cart is edited and saved to database', function () {});
            test('the order can be approved', function () {});
            test('the order can be cancelled', function () {});
            test('the order can be delivered', function () {});
            test('the order can be finished', function () {});
            test('the order can be archived', function () {});
        });

        describe('Payment methods smoke test', function () {
            test('the payment method list page returns a successful response', function () {});
            test('the payment method is created and added to database', function () {});
            test('the payment method is edited and saved to database', function () {});
            test('the payment method is viewable', function () {});
        });

        describe('Permissions smoke test', function () {
            test('the permissions list page returns a successful response', function () {});
            test('the permissions is created and added to database', function () {});
            test('the permissions is edited and saved to database', function () {});
            test('the permissions is viewable', function () {});
        });

        describe('Product smoke test', function () {
            test('the product list page returns a successful response', function () {});
            test('the product is created and added to database', function () {});
            test('the product is edited and saved to database', function () {});
            test('the product is viewable', function () {});
        });

        describe('Roles smoke test', function () {
            test('the roles list page returns a successful response', function () {});
            test('the roles is created and added to database', function () {});
            test('the roles is edited and saved to database', function () {});
            test('the roles is viewable', function () {});
        });

        describe('Ticket smoke test', function () {
            test('the ticket list page returns a successful response', function () {});
            test('the ticket can be bulk deleted', function () {});
            test('the ticket is viewable', function () {});
            test('the ticket is deletable', function () {});
            test('the ticket can be marked as important', function () {});
            test('the ticket can be unmarked as important', function () {});
            test('the ticket can be processed', function () {});
        });

        describe('Users smoke test', function () {
            test('the users list page returns a successful response', function () {});
            test('the users list tabs works', function () {});
            test('the users is created and added to database', function () {});
            test('the users is edited and saved to database', function () {});
            test('the users is viewable', function () {});
            test('the order can be approved', function () {});
            test('the order can be disapproved', function () {});
            test('the order can be blocked', function () {});
            test('the order can be unblocked', function () {});
        });
    });
})->group('dashboard-smoke-test');
