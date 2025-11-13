<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Permission::insert([
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Impersonate User', 'تسجيل دخول كمستخدم']), JSON_UNESCAPED_UNICODE),
                'code' => 'impersonate-user',
            ],
        ]);

        DB::table('role_permission')->insert([
            'role_id' => Role::where('code', '=', Role::ROLE_DEVELOPER_CODE)->first()->id,
            'permission_id' => Permission::where('code', '=', 'impersonate-user')->first()->id,
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
