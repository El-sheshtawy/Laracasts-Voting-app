<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->text('body');
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('idea_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('status_id')->default('1')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->integer('spam_reports')->default(0);
            $table->boolean('is_status_update')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
