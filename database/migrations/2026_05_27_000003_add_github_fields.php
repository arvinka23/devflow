<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('github_repo')->nullable()->after('status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->text('github_token')->nullable()->after('profile_picture');
        });
    }

    public function down(): void
    {
        Schema::table('projects', fn(Blueprint $t) => $t->dropColumn('github_repo'));
        Schema::table('users', fn(Blueprint $t) => $t->dropColumn('github_token'));
    }
};
