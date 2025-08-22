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
        if ( ! Schema::hasTable( 'options' ) ) {
            Schema::create( 'options', function ( Blueprint $table ) {
                $table->increments( 'id' );
                $table->integer( 'user_id' )->nullable();
                $table->string( 'key' );
                $table->text( 'value' )->nullable();
                $table->datetime( 'expire_on' )->nullable();
                $table->boolean( 'array' ); // this will avoid some option to be saved as options
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
        Schema::dropIfExists( 'options' );
    }
};
