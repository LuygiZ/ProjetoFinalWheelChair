<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectsSeeder extends Seeder
{
    //$ ['P', 'W', 'C', 'I', 'D']);  // P Pending, W Work in Progress, C Completed, I Interrupted, D dismissed
    public static $projects = [
        ['name' => 'Matrix I', 'responsible_id' => 1, 'status' => 'C', 'billed' => true, 'start' => 14, 'duration' => 8],
        ['name' => 'Online TicTacToe', 'responsible_id' => 3, 'status' => 'W', 'billed' => false, 'start' => 2, 'duration' => 4],
        ['name' => 'Online Chess', 'responsible_id' => 1, 'status' => 'P', 'billed' => false, 'start' => 3, 'duration' => 6],
        ['name' => 'Matrix II', 'responsible_id' => 2, 'status' => 'W', 'billed' => false, 'start' => 3, 'duration' => 10],
        ['name' => 'Matrix Redux', 'responsible_id' => 6, 'status' => 'D', 'billed' => false, 'start' => 3, 'duration' => 10],
        ['name' => 'Survival', 'responsible_id' => 3, 'status' => 'C', 'billed' => true, 'start' => 10, 'duration' => 6],
        ['name' => 'Survival Zombie', 'responsible_id' => 2, 'status' => 'I', 'billed' => false, 'start' => 8, 'duration' => 4],
        ['name' => 'Dune Online', 'responsible_id' => 3, 'status' => 'W', 'billed' => false, 'start' => 6, 'duration' => 12],
        ['name' => 'Zombie Killer', 'responsible_id' => 4, 'status' => 'C', 'billed' => false, 'start' => 8, 'duration' => 10],
        ['name' => 'My Revenge', 'responsible_id' => 2, 'status' => 'P', 'billed' => false, 'start' => 5, 'duration' => 10],
        ['name' => 'Apocalipse Now', 'responsible_id' => 1, 'status' => 'W', 'billed' => false, 'start' => 3, 'duration' => 6],
        ['name' => 'Online Uno', 'responsible_id' => 5, 'status' => 'W', 'billed' => false, 'start' => 4, 'duration' => 4],
        ['name' => 'Online Poker', 'responsible_id' => 6, 'status' => 'W', 'billed' => false, 'start' => 3, 'duration' => 12],
        ['name' => 'Paper Scissor Rock', 'responsible_id' => 7, 'status' => 'C', 'billed' => true, 'start' => 8, 'duration' => 1],
        ['name' => 'Paper Scissor Rock 3D', 'responsible_id' => 6, 'status' => 'C', 'billed' => false, 'start' => 6, 'duration' => 3],
        ['name' => 'Abandoned Earth', 'responsible_id' => 5, 'status' => 'C', 'billed' => false, 'start' => 5, 'duration' => 2],
        ['name' => 'Mirror Earth', 'responsible_id' => 7, 'status' => 'W', 'billed' => false, 'start' => 4, 'duration' => 8],
    ];

    private function calculateDates($project, $faker, &$preview_start, &$preview_end, &$real_start, &$real_end)
    {
        $start1 = $project['start'] + 1;
        $start2 = $project['start'] - 1;

        $preview_start = $faker->dateTimeBetween("-$start1 months", "-$start2 months");
        $preview_start = \Carbon\Carbon::createFromFormat('Y-m-d', date_format($preview_start, 'Y-m-d'));
        $preview_end = $faker->dateTimeBetween($preview_start, $preview_start->copy()->addMonths($project['duration']));
        $preview_end = \Carbon\Carbon::createFromFormat('Y-m-d', date_format($preview_end, 'Y-m-d'));
        $real_start = $faker->dateTimeBetween($preview_start, $preview_start->copy()->addDays(rand(0, 40)));
        $real_start = \Carbon\Carbon::createFromFormat('Y-m-d', date_format($real_start, 'Y-m-d'));
        $real_end = $faker->dateTimeBetween($preview_end, $preview_end->copy()->addDays(rand(0, 100)));
        $real_end = \Carbon\Carbon::createFromFormat('Y-m-d', date_format($real_end, 'Y-m-d'));
    }

    public function run()
    {
        $faker = \Faker\Factory::create('pt_PT');
        $dbProjects = [];
        $i = 0;
        foreach (static::$projects as $p) {
            $this->calculateDates($p, $faker, $preview_start, $preview_end, $real_start, $real_end);

            $dbProjects[] = [
                'name' => $p['name'],
                'responsible_id' => $p['responsible_id'],
                'status' => $p['status'],
                'preview_start_date' => $preview_start->format('Y-m-d'),
                'preview_end_date' => $preview_end->format('Y-m-d'),
                'real_start_date' => in_array($p['status'], ['P', 'D']) ? null : $real_start->format('Y-m-d'),
                'real_end_date' =>  $p['status'] == 'C' ? $real_end->format('Y-m-d') : null,
                'total_hours' => null,
                'billed' => $p['billed'],
                'total_price' => null,
            ];
        }

        DB::table('projects')->insert($dbProjects);
    }
}
