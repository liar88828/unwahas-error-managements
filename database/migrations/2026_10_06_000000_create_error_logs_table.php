<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('error-redirect.table', 'error_logs'), function (Blueprint $table): void {
            $table->id();
            $table->string('app')->nullable();
            $table->text('class');
            $table->text('message');
            $table->text('file');
            $table->unsignedInteger('line');
            $table->char('deduplication_key', 64)->index();
            $table->longText('trace');
            $table->json('context')->nullable();
            $table->unsignedInteger('count')->default(1);
            $table->text('url')->nullable();
            $table->string('method', 16)->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('ip', 45)->nullable();
            $table->timestamp('resolved_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('error-redirect.table', 'error_logs'));
    }
};
