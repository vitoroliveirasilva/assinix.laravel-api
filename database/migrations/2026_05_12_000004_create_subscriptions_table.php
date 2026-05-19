<?php

use App\Enums\CurrencyCode;
use App\Enums\RecurrenceType;
use App\Enums\SubscriptionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('category_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('payment_method_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('name', 120);
            $table->text('description')->nullable();

            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default(CurrencyCode::BRL->value);

            $table->decimal('amount_brl', 12, 2)->nullable();
            $table->decimal('exchange_rate', 18, 8)->nullable();
            $table->date('exchange_rate_date')->nullable();

            $table->string('status', 30)->default(SubscriptionStatus::Active->value);
            $table->string('recurrence', 30)->default(RecurrenceType::Monthly->value);

            $table->unsignedSmallInteger('interval')->default(1);
            $table->unsignedInteger('interval_in_days')->nullable();

            $table->date('starts_at');
            $table->date('next_billing_at');
            $table->date('ends_at')->nullable();
            $table->date('last_charged_at')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'recurrence']);
            $table->index(['user_id', 'next_billing_at']);
            $table->index(['user_id', 'category_id']);
            $table->index(['user_id', 'payment_method_id']);
            $table->index(['user_id', 'deleted_at']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE subscriptions ADD CONSTRAINT subscriptions_amount_positive CHECK (amount > 0)');
            DB::statement('ALTER TABLE subscriptions ADD CONSTRAINT subscriptions_interval_positive CHECK (interval > 0)');
            DB::statement('ALTER TABLE subscriptions ADD CONSTRAINT subscriptions_interval_in_days_positive CHECK (interval_in_days IS NULL OR interval_in_days > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
