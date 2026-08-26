<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->string('focus_keyword')->nullable()->after('slug');
            $table->string('image_alt')->nullable()->after('featured_image');
            $table->json('tags')->nullable()->after('image_alt');
            $table->string('author_name')->nullable()->after('tags');
            $table->string('canonical_url')->nullable()->after('author_name');
            $table->string('og_title')->nullable()->after('canonical_url');
            $table->text('og_description')->nullable()->after('og_title');
            $table->string('og_image')->nullable()->after('og_description');
            $table->json('faqs')->nullable()->after('og_image');
            $table->longText('schema_markup')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn([
                'focus_keyword',
                'image_alt',
                'tags',
                'author_name',
                'canonical_url',
                'og_title',
                'og_description',
                'og_image',
                'faqs',
            ]);
        });
    }
};
