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
            $table->string("entity_type")->charset('latin1');
            $table->unsignedBigInteger("entity_id");
            $table->string('restriction');
            $table->unsignedTinyInteger('type')->default(RestrictionType::DENY);
            $table->boolean('enabled');
            $table->timestamps();

            $table->index(['restriction', 'enabled', 'type']);
            $table->index(["entity_type", "entity_id"]);
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
