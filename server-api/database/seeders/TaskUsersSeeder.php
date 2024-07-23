<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaskUsersSeeder extends Seeder
{
    private $faker;
    private function buildArrayAssignedTasks($tasks, $users)
    {
        $assignedTasks = [];
        foreach ($tasks as $task) {
            $total_users = rand(0, 3);
            $assignedUsers = [];
            shuffle($users);
            $i = 0;
            while (count($assignedUsers) < $total_users) {
                $user = $users[$i];
                $i++;
                if ($user->id == $task->owner_id) {
                    continue;
                }
                $assignedUsers[] = $user->id;
            }
            foreach ($assignedUsers as $userId) {
                $assignedTasks[] = [
                    'task_id' => $task->id,
                    'user_id' => $userId,
                ];
            }
        }
        return $assignedTasks;
    }

    public function run()
    {
        $this->faker = \Faker\Factory::create('pt_PT');
        $tasks = DB::select('select id, owner_id from tasks where project_id is not null');
        $users = DB::select('select id from users');
        $assignedTasks = $this->buildArrayAssignedTasks($tasks, $users);
        DB::table('task_user')->insert($assignedTasks);
    }
}
