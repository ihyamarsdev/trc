<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Support\Str;
use App\Models\RegistrationData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RegistrationData>
 */
class RegistrationDataFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = RegistrationData::class;

    public function definition(): array
    {
        $faker = $this->faker;

        $types = ['apps','anbk','snbt','tka'];
        $periodes = ['Januari - Juni', 'Juli - Desember'];
        $schoolTypes = ['Negeri','Swasta'];
        $eduLevels = ['SD','SMP','SMA','SMK','MTS','MA'];
        $statusColors = ['red','yellow','blue','green'];
        $description = ['ABK', 'Non ABK'];

        // angka & hitungan finansial sederhana
        $studentCount = $faker->numberBetween(30, 600);
        $dateRegister = $faker->dateTimeBetween('-6 months', 'now');
        $implementEst = (clone $dateRegister)->modify('+' . $faker->numberBetween(7, 60) . ' days');
        $schools_level = $faker->randomElement($eduLevels);
        $schools =  $schools_level . ' ' . Str::title($faker->city());


        return [
            // Sales
            'type' => $faker->randomElement($types),
            'periode' => $faker->randomElement($periodes),
            'years' => (string) $dateRegister->format('Y'),
            'date_register' => $dateRegister,
            'provinces' => $faker->state(),
            'regencies' => $faker->city(),
            'district' => $faker->citySuffix(),
            'area' => $faker->streetName(),
            'student_count' => $studentCount,
            'counselor_coordinators' => $faker->name(),
            'counselor_coordinators_phone' => $faker->phoneNumber(),
            'curriculum_deputies' => $faker->name(),
            'curriculum_deputies_phone' => $faker->phoneNumber(),
            'proctors' => $faker->name(),
            'proctors_phone' => $faker->phoneNumber(),
            'schools' =>  $schools,
            'schools_type' => $faker->randomElement($schoolTypes),
            'class' => $faker->randomElement(['1','2','3','4','5','6','7','8','9','10','11','12']),
            'education_level' => $schools_level,
            'description' => fake()->randomElement($description),
            'principal' => $faker->name(),
            'principal_phone' => $faker->phoneNumber(),
            'implementation_estimate' => $implementEst,
            'status_color' => $faker->randomElement($statusColors),


            'users_id' => null,
            'status_id' => 1,
        ];
    }
}
