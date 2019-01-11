<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateRestrictionsRulesTable.
 */
class CreateRestrictionsRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('restrictions_rules', function (Blueprint $table) {
            $table->unsignedInteger('restriction_id');
            $table->unsignedInteger('rule_id');

            $table->primary(['restriction_id', 'rule_id']);

            $table->foreign('restriction_id')
                ->references('id')->on('restrictions')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('restrictions_rules');
    }
}
