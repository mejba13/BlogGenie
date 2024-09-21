<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('post_titles', function (Blueprint $table) {
            $table->id();
            $table->string('title');  // Store the post title
            $table->date('publish_date');  // Store the publish date
            $table->timestamps();  // Track creation and updates
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_titles');
    }
};
