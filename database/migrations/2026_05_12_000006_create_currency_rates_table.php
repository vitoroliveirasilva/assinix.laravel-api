<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currency_rates', function (Blueprint $table): void {
            $table->id();

            $table->string('base_currency', 3);
            $table->string('target_currency', 3)->default('BRL');
            $table->decimal('rate', 18, 8);
            $table->string('source', 50)->default('awesomeapi');
            $table->timestamp('quoted_at')->nullable();
            $table->json('payload')->nullable();

            $table->timestamps();

            $table->index(['base_currency', 'target_currency']);
            $table->index(['base_currency', 'target_currency', 'source']);
            $table->index(['base_currency', 'target_currency', 'quoted_at']);

            $table->unique(
                ['base_currency', 'target_currency', 'source', 'quoted_at'],
                'currency_rates_unique_quote'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currency_rates');
    }
};
