<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         Schema::create('order_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->nullable();
            $table->string('reference')->nullable();
            $table->integer('payment_term')->nullable()
            ->comment('1 : a reception , 2 : 30 jours, 3 : 30 jours fin de mois, 4 : 60 jours, 5 : 60 jours fin de mois, 6 : a commande, 7 : a livraison, 8 : 50/50, 9 : 10 jours, 10 : 10 jours fin de mois, 11 : 14 jours, 12 : 14 jours fin de mois');
            $table->foreignId('paid_method_id')
            ->nullable()
            ->constrained('mode_payemnts');
            $table->date('due_date')->nullable();

       
            $table->integer('status')->default(1)
            ->comment('1 : pending, 2 : wait payment, 3 : wait fulfillment, 4 : wait shipment, 5 : wait pickup, 6 : partial shipped, 7 : completed, 8 : shipped, 9 : cancelled, 10 : declined, 11 : refunded, 12 : disputed, 13 : manual verification required, 14 : partial refunded');

            $table->foreignId('store_id')
                ->nullable()
                ->constrained('stores');

            $table->foreignId('supplier_id')
                ->nullable()
                ->constrained('suppliers');

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users');

            $table->text('public_note')->nullable();
            $table->text('private_note')->nullable();   

            $table->decimal('discount', 20, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_purchases');
    }
};
