<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMediaToPostsTable extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('featured_image_url')->nullable()->after('content');
            $table->string('video_url')->nullable()->after('featured_image_url');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('featured_image_url');
            $table->dropColumn('video_url');
        });
    }
}
