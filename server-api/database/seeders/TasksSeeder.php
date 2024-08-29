<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TasksSeeder extends Seeder
{
    private $faker;
    private $startingTasks = [
        'Requirement analysis',
        'Data analysis',
        'Architecture definition',
        'Costs & planning',
        'Human resources development',
        'Tecnhical qualification',
        'Resources searching',
        'Resources planning',
        'UI strategic planning',
    ];

    private $workingTasks = [
        'Data structure definition',
        'Data seeding',
        'Architecture implementation',
        'Unit Testing',
        'Stress load tests',
        'Resources & scalling',
        'Server implementation',
        'Server testing',
        'UI mookups',
        'UI specification',
        'UI implementation',
        'UI testing',
        'Client implementation',
        'Client testing',
        'System publishing',
        'System integration',
        'Internal qualification',
        'Tecnhology analysis',
        'Tecnhology learning',
        'Integration tests',
        'Resources analysis',
        'Server debugging',
        'Client debugging',
        'Server Documentation',
        'Client Documentation',
        'System Integration Documentation',
        'UI Documentation',
        'Server refactoring',
        'Client refactoring',
        'System refactoring',
    ];

    private $endingTasks = [
        'Publishing & integration tests',
        'Stress analysis',
        'End-user UI testing',
        'Product marketing',
        'SEO development',
        'Cost calculations',
        'Revenue analysis',
        'Resource & systems maintenance',
        'Profit analysis'
    ];

    private $interruptTasks = [
        'Costs analysys',
        'Loss analysis',
        'Conflict arbitration',
        'Conflict analysis',
    ];

    private $dismissTasks = [
        'Dismiss analysys',
        'Loss report',
        'Internal conflit arbitration',
        'Internal analysis',
    ];

    private $noProjectTasks_verbs = [
        'Learn',
        'Implement',
        'Analyse',
        'Plan',
        'Teach',
        'Program',
        'Refactor',
        'Design',
        'Reschedule',
        'Report',
        'Test',
        'Publish',
        'Internal stream',
        'Migrate',
        'Reschedule',
        'Define',
        'Schedule',
        'Syncronize',
    ];

    private $noProjectTasks_noums = [
        'Vue',
        'React',
        'Angular',
        'Javascript',
        'Web assembly',
        'Webpack',
        'Laravel',
        'Php',
        '.Net',
        'Java',
        'User Interface',
        'Mookups',
        'Client',
        'Server',
        'System',
        'Integration',
        'Resources',
        'Database',
        'Usability',
        'Web Server',
        'Application Server',
        'Docker',
        'Kubernetes',
        'Stress tests',
        'UI tests',
        'Python',
        'C++',
        'C#',
        'Rust',
        'Rust',
    ];

    private function getRandomArray($arrayTasks, $percentage = 70)
    {
        shuffle($arrayTasks);
        $total = intval(count($arrayTasks) * $percentage / 100);
        return array_slice($arrayTasks, 0, $total);
    }

    private function createTaskForProject($taskName, $project)
    {
        $newTask = [
            'description' => $taskName,
            'owner_id' => $project->responsible_id,
            'project_id' => $project->id,
            'completed' => true,
            'total_hours' => null
        ];
        if (in_array($project->status, ['P', 'W'])) {
            $newTask['completed'] = rand(1, 2) == 1 ? true : false;
        } else {
            $newTask['completed'] = rand(1, 5) == 1 ? false : true;
        }
        $newTask['total_hours'] = $newTask['completed'] ? rand(1, 40) : null;
        return $newTask;
    }

    private function createTaskForUser($taskName, $user)
    {
        $newTask = [
            'description' => $taskName,
            'owner_id' => $user->id,
            'project_id' => null,
            'completed' => true,
            'total_hours' => null
        ];
        $newTask['completed'] = rand(1, 2) == 1 ? true : false;
        $newTask['total_hours'] = $newTask['completed'] ? rand(1, 40) : null;
        return $newTask;
    }

    private function addTasksForProject($project)
    {
        $arrayTasks = [];
        switch ($project->status) {
            case 'P':
                $arrayTasks = array_merge(
                    $this->getRandomArray($this->startingTasks)
                );
                break;
            case 'W':
                $arrayTasks = array_merge(
                    $this->getRandomArray($this->startingTasks, 10),
                    $this->getRandomArray($this->workingTasks, 10),
                );
                break;
            case 'C':
                $arrayTasks = array_merge(
                    $this->getRandomArray($this->startingTasks, 10),
                    $this->getRandomArray($this->workingTasks, 10),
                    $this->getRandomArray($this->endingTasks, 10),
                );
                break;
            case 'I':
                $arrayTasks = array_merge(
                    $this->getRandomArray($this->startingTasks, 10),
                    $this->getRandomArray($this->workingTasks, 5),
                    $this->getRandomArray($this->interruptTasks, 10),
                );
                break;
            case 'D':
                $arrayTasks = array_merge(
                    $this->getRandomArray($this->startingTasks, 10),
                    $this->getRandomArray($this->workingTasks, 5),
                    $this->getRandomArray($this->dismissTasks, 10),
                );
                break;
        }
        $tasks = [];
        foreach ($arrayTasks as $taskname) {
            $tasks[] = $this->createTaskForProject($taskname, $project);
        }
        DB::table('tasks')->insert($tasks);
    }

    private function addTasksForUser($user)
    {
        $totalTask = rand(2, 5);
        $tasks = [];
        for ($i = 0; $i < $totalTask; $i++) {
            $verb = $this->faker->randomElement($this->noProjectTasks_verbs);
            $noum = $this->faker->randomElement($this->noProjectTasks_noums);
            $tasks[] = $this->createTaskForUser($verb . " " . $noum, $user);
        }
        DB::table('tasks')->insert($tasks);
    }

    public function run()
    {
        $this->faker = \Faker\Factory::create('pt_PT');
        $projects = DB::table('projects')->get();
        foreach ($projects as $project) {
            $this->addTasksForProject($project);
        }
        DB::update('update projects as prj set total_hours = (select sum(total_hours) from tasks where project_id = prj.id)');
        DB::update("update projects as prj set total_price = 55 * total_hours where status in ('C', 'D', 'I')");

        $users = DB::table('users')->get();
        foreach ($users as $user) {
            $this->addTasksForUser($user);
        }
    }
}
