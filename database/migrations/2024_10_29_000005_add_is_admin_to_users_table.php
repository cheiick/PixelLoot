<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint as SchemaBlueprint;
use Illuminate\Support\Facades\Schema;

class AddIsAdminToUsersTable extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (SchemaBlueprint $table) {
            $table->boolean('is_admin')->default(false)->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (SchemaBlueprint $table) {
            $table->dropColumn('is_admin');
        });
    }
}
