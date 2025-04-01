<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tblProductData', function (Blueprint $table) {
            $table->integer('strProductStock')->after('strProductDesc')->comment('Product stock');
            $table->decimal('strProductCost', 10, 2)->after('strProductStock')->comment('Product cost');
        });
    }

    public function down(): void
    {
        Schema::table('tblProductData', function (Blueprint $table) {
            //
        });
    }
};
