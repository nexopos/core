<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Ns\Classes\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasTable( 'medias' ) ) {
            Schema::create( 'medias', function ( Blueprint $table ) {
                $table->increments( 'id' );
                $table->string( 'name' );
                $table->string( 'type' );
                $table->string( 'slug' );
                $table->string( 'extension' );
                $table->integer( 'user_id' );
                $table->timestamps();
            } );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists( 'medias' );
    }
};
