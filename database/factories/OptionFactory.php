<?php

namespace Database\Factories;

use App\Filament\Enum\OptionGroup;
use App\Models\Option;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Option>
 */
class OptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = strtoupper(fake()->unique()->lexify('????'));

        return [
            'group' => OptionGroup::Bank,
            'value' => $name,
            'label' => $name,
            'description' => null,
            'sort_order' => 99,
            'is_active' => true,
        ];
    }

    public function inGroup(OptionGroup $group): static
    {
        return $this->state(['group' => $group]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
