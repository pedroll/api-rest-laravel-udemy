<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {

            $table->bigIncrements('id');

            $table->bigInteger('user_id')->unsigned();

            $table->bigInteger('category_id')->unsigned();

            $table->string('title', 50)->nullable();

            $table->text('content')->nullable();

            $table->string('image', 255)->nullable();

            $table->timestamp('created_at')->nullable();

            $table->timestamp('updated_at')->nullable();


            $table->index(['category_id']);

            $table->index(['user_id']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
};
