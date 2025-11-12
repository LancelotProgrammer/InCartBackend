<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->string('code')->after('id');
            $table->date('published_at')->nullable()->after('code');
        });

        $cities = DB::table('cities')->select('id', 'name->en as en_name')->get();
        foreach ($cities as $city) {
            if (! empty($city->en_name)) {
                DB::table('cities')->where('id', $city->id)->update(['code' => Str::slug($city->en_name)]);
            }
        }

        Permission::insert([
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Publish City', 'نشر المدينة']), JSON_UNESCAPED_UNICODE),
                'code' => 'publish-city',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Unpublish City', 'إلغاء نشر المدينة']), JSON_UNESCAPED_UNICODE),
                'code' => 'unpublish-city',
            ],
        ]);

        DB::table('role_permission')->insert([
            'role_id' => Role::where('code', '=', Role::ROLE_SUPER_ADMIN_CODE)->first()->id,
            'permission_id' => Permission::where('code', '=', 'publish-city')->first()->id,
        ]);
        DB::table('role_permission')->insert([
            'role_id' => Role::where('code', '=', Role::ROLE_SUPER_ADMIN_CODE)->first()->id,
            'permission_id' => Permission::where('code', '=', 'unpublish-city')->first()->id,
        ]);
        DB::table('role_permission')->insert([
            'role_id' => Role::where('code', '=', Role::ROLE_DEVELOPER_CODE)->first()->id,
            'permission_id' => Permission::where('code', '=', 'publish-city')->first()->id,
        ]);
        DB::table('role_permission')->insert([
            'role_id' => Role::where('code', '=', Role::ROLE_DEVELOPER_CODE)->first()->id,
            'permission_id' => Permission::where('code', '=', 'unpublish-city')->first()->id,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn(['code', 'published_at']);
        });
    }
};
