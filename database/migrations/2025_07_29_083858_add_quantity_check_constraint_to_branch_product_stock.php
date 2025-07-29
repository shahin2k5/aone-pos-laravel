<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add check constraint using raw SQL
        DB::statement('ALTER TABLE branch_product_stock ADD CONSTRAINT branch_product_stock_quantity_check CHECK (quantity >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove check constraint
        DB::statement('ALTER TABLE branch_product_stock DROP CONSTRAINT IF EXISTS branch_product_stock_quantity_check');
    }
};
