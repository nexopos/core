<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Ns\Classes\Schema;

/**
 * @property int user_id
 * @property string identifier
 * @property int title
 * @property string description
 * @property string url
 * @property int source
 * @property bool dismissable
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasTable( 'notifications' ) ) {
            Schema::create( 'notifications', function ( Blueprint $table ) {
                $table->id();
                $table->integer( 'user_id' );
                $table->string( 'identifier' );
                $table->string( 'title' );
                $table->text( 'description' );
                $table->string( 'url' )->default( '#' );
                $table->string( 'source' )->default( 'system' );
                $table->boolean( 'dismissable' )->default( true );
                $table->json( 'actions' )->nullable();
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
        Schema::dropIfExists( 'notifications' );
    }
};
