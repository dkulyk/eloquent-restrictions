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
            $table->string("entity_type");
            $table->unsignedInteger("entity_id");
            $table->string('restriction');
            $table->unsignedTinyInteger('type')->default(RestrictionType::DENY);
            $table->boolean('enabled');
            $table->timestamps();

            $table->index(["entity_id", "entity_type"]);
            $table->index(['restriction', 'type', 'enabled']);
            $table->index(['entity_id', 'entity_type', 'restriction', 'type', 'enabled'], 'entity_id');
            $table->index(['entity_type', 'enabled', 'type', 'restriction', 'entity_id'], 'entity_type');
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
