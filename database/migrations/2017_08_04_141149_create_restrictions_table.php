<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use DtKt\Restrictions\Enum\RestrictionType;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateRestrictionsTable.
 */
class CreateRestrictionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('restrictions', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('entity');
            $table->string('restriction');
            $table->unsignedTinyInteger('type')->default(RestrictionType::DENY);
            $table->boolean('enabled');
            $table->timestamps();

            $table->index(['restriction', 'enabled', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('restrictions');
    }
}
