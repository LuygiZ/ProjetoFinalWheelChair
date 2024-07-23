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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('gender', ['M', 'F']);
            $table->string('photo_url')->nullable();
            $table->enum('type', ['A', 'M']);  //
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->bigInteger('responsible_id')->unsigned();
            $table->foreign('responsible_id')->references('id')->on('users');
            $table->enum('status', ['P', 'W', 'C', 'I', 'D']);  // P Pending, W Work in Progress, C Completed, I Interrupted, D Discarded
            $table->date('preview_start_date')->nullable();
            $table->date('preview_end_date')->nullable();
            $table->date('real_start_date')->nullable();
            $table->date('real_end_date')->nullable();
            $table->integer('total_hours')->nullable();
            $table->boolean('billed');
            $table->decimal('total_price', 9, 2)->nullable();
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('owner_id')->unsigned();
            $table->foreign('owner_id')->references('id')->on('users');
            $table->bigInteger('project_id')->unsigned()->nullable();
            $table->foreign('project_id')->references('id')->on('projects');
            $table->boolean('completed');
            $table->string('description', 50);
            $table->text('notes')->nullable();
            $table->integer('total_hours')->nullable();
        });

        Schema::create('task_user', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('task_id')->unsigned();
            $table->foreign('task_id')->references('id')->on('tasks');
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_user');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('projects');
        Schema::table('users', function ($table) {
            $table->dropColumn(['gender', 'photo_url', 'type']);
        });
    }
};
