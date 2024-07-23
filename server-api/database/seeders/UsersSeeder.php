<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class UsersSeeder extends Seeder
{
    private $photoPath = 'public/fotos';
    private $files_M = [];
    private $files_F = [];
    private $faker;

    public static $users = [
        ['name' => 'João Silva', 'email' => 'jsilva@mail.pt', 'gender' => 'M'],
        ['name' => 'Maria Oliveira', 'email' => 'maria.o@mail.pt', 'gender' => 'F'],
        ['name' => 'Marco', 'email' => 'marco@mail.pt', 'gender' => 'M'],
        ['name' => 'Maria Antonieta', 'email' => 'maria.a@mail.pt', 'gender' => 'F'],
        ['name' => 'Daniel', 'email' => 'daniel@mail.pt', 'gender' => 'M'],
        ['name' => 'Carla', 'email' => 'carla@mail.pt', 'gender' => 'F'],
        ['name' => 'Diogo', 'email' => 'diogo@mail.pt', 'gender' => 'M'],
        ['name' => 'Ana', 'email' => 'ana@mail.pt', 'gender' => 'F'],
        ['name' => 'David', 'email' => 'david@mail.pt', 'gender' => 'M'],
        ['name' => 'Catarina', 'email' => 'catarina@mail.pt', 'gender' => 'F'],
        ['name' => 'Pedro', 'email' => 'pedro@mail.pt', 'gender' => 'M'],
        ['name' => 'Beatriz', 'email' => 'beatriz@mail.pt', 'gender' => 'F'],
        ['name' => 'Paulo', 'email' => 'paulo@mail.pt', 'gender' => 'M'],
        ['name' => 'Joana', 'email' => 'joana@mail.pt', 'gender' => 'F'],
        ['name' => 'João Pereira', 'email' => 'jpereira@mail.pt', 'gender' => 'M'],
        ['name' => 'Anabela', 'email' => 'anabela@mail.pt', 'gender' => 'F'],
        ['name' => 'Nuno', 'email' => 'nuno@mail.pt', 'gender' => 'M'],
        ['name' => 'Susana', 'email' => 'susana@mail.pt', 'gender' => 'F'],
        ['name' => 'Filipe', 'email' => 'filipe@mail.pt', 'gender' => 'M'],
        ['name' => 'Patricia', 'email' => 'patricia@mail.pt', 'gender' => 'F']
    ];

    private function cleanPhotoFiles()
    {
        Storage::deleteDirectory($this->photoPath);
        Storage::makeDirectory($this->photoPath);
    }

    private function fillPhotoFilesNames()
    {
        $allFiles = collect(File::files(database_path('seeders/fotos')));
        foreach ($allFiles as $f) {
            if (strpos($f->getPathname(), 'M_')) {
                $this->files_M[] = $f->getPathname();
            } else {
                $this->files_F[] = $f->getPathname();
            }
        }
        shuffle($this->files_M);
        shuffle($this->files_F);
    }

    private function savePhotoFile($file, $counter)
    {
        $targetDir = storage_path('app/' . $this->photoPath);
        $newfilename = $counter . "_" . $this->faker->randomNumber($nbDigits = 4, $strict = true) . '.jpg';
        File::copy($file, $targetDir . '/' . $newfilename);
        return $newfilename;
    }

    public function run()
    {
        $this->faker = \Faker\Factory::create('pt_PT');
        $this->cleanPhotoFiles();
        $this->fillPhotoFilesNames();

        $dbUsers = [];
        $i = 0;
        foreach (static::$users as $u) {
            $i++;
            $createdAt = $this->faker->dateTimeBetween('-1 years', '-3 months');
            $email_verified_at = $this->faker->dateTimeBetween($createdAt, '-2 months');
            $updatedAt = $this->faker->dateTimeBetween($email_verified_at, '-1 months');
            if ($u['gender'] == 'M') {
                $originalFileName = array_shift($this->files_M);
            } else {
                $originalFileName = array_shift($this->files_F);
            }
            $newFileName = $this->savePhotoFile($originalFileName, $i);
            $dbUsers[] = [
                'name' => $u['name'],
                'email' => $u['email'],
                'email_verified_at' => $email_verified_at,
                'password' => bcrypt('123'),
                'remember_token' => $this->faker->asciify('**********'), //str_random(10),
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
                'photo_url' => $newFileName,
                'gender' =>  $u['gender'],
                'type' =>  $i <= 5 ? 'A' : 'M',
            ];
        }

        DB::table('users')->insert($dbUsers);
    }
}
