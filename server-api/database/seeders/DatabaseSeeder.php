<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info("-----------------------------------------------");
        $this->command->info("START of database seeder");
        $this->command->info("-----------------------------------------------");

        DB::statement("SET foreign_key_checks=0");

        DB::table('users')->delete();
        DB::table('task_user')->delete();
        DB::table('tasks')->delete();
        DB::table('projects')->delete();

        DB::statement('ALTER TABLE users AUTO_INCREMENT = 0');
        DB::statement('ALTER TABLE task_user AUTO_INCREMENT = 0');
        DB::statement('ALTER TABLE tasks AUTO_INCREMENT = 0');
        DB::statement('ALTER TABLE projects AUTO_INCREMENT = 0');

        DB::statement("SET foreign_key_checks=1");

        $this->call(UsersSeeder::class);
        $this->call(ProjectsSeeder::class);
        $this->call(TasksSeeder::class);
        $this->call(TaskUsersSeeder::class);

        $this->command->info("-----------------------------------------------");
        $this->command->info("END of database seeder");
        $this->command->info("-----------------------------------------------");

    }
}
