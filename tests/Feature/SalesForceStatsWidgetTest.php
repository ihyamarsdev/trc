<?php

namespace Tests\Feature;

use App\Filament\Enum\Program;
use App\Filament\User\Widgets\SalesForceStatsWidget;
use App\Models\RegistrationData;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SalesForceStatsWidgetTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private Status $status;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin', 'guard_name' => 'web']);

        $this->adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->adminUser->assignRole('admin');

        $this->status = Status::create([
            'name' => 'Status Initial',
            'description' => 'Initial Status',
            'color' => 'blue',
            'order' => 1,
            'category' => 'sales',
        ]);
    }

    public function test_widget_includes_all_enum_programs_dynamically_even_with_zero_records(): void
    {
        $this->actingAs($this->adminUser);

        $widget = new SalesForceStatsWidget;
        $data = $widget->getChartData();

        $this->assertCount(count(Program::cases()), $data['labels']);
        $this->assertEquals(count(Program::cases()), $data['totals']['programs']);
        $this->assertEquals(0, $data['totals']['students']);
        $this->assertEquals(0, $data['totals']['schools']);

        foreach (Program::cases() as $case) {
            $this->assertContains($case->label(), $data['labels']);
        }

        $pasjDetail = collect($data['details'])->firstWhere('program_type', 'pasj');
        $this->assertNotNull($pasjDetail);
        $this->assertEquals('PASJ', $pasjDetail['label']);
        $this->assertEquals(0, $pasjDetail['student_count']);
        $this->assertEquals(0, $pasjDetail['school_count']);
        $this->assertEquals(0, $pasjDetail['percentage']);
        $this->assertEquals('0%', $pasjDetail['percentage_formatted']);
    }

    public function test_widget_calculates_percentages_dynamically_when_records_exist(): void
    {
        $this->actingAs($this->adminUser);

        RegistrationData::factory()->create([
            'users_id' => $this->adminUser->id,
            'status_id' => $this->status->id,
            'type' => 'anbk',
            'student_count' => 100,
        ]);

        RegistrationData::factory()->create([
            'users_id' => $this->adminUser->id,
            'status_id' => $this->status->id,
            'type' => 'pasj',
            'student_count' => 300,
        ]);

        $widget = new SalesForceStatsWidget;
        $data = $widget->getChartData();

        $this->assertEquals(400, $data['totals']['students']);
        $this->assertEquals(2, $data['totals']['schools']);

        $anbkDetail = collect($data['details'])->firstWhere('program_type', 'anbk');
        $this->assertEquals(100, $anbkDetail['student_count']);
        $this->assertEquals(25.0, $anbkDetail['percentage']);
        $this->assertEquals('25.0%', $anbkDetail['percentage_formatted']);

        $pasjDetail = collect($data['details'])->firstWhere('program_type', 'pasj');
        $this->assertEquals(300, $pasjDetail['student_count']);
        $this->assertEquals(75.0, $pasjDetail['percentage']);
        $this->assertEquals('75.0%', $pasjDetail['percentage_formatted']);
    }

    public function test_widget_formats_small_percentages_with_precision(): void
    {
        $this->actingAs($this->adminUser);

        RegistrationData::factory()->create([
            'users_id' => $this->adminUser->id,
            'status_id' => $this->status->id,
            'type' => 'apps',
            'student_count' => 353390,
        ]);

        RegistrationData::factory()->create([
            'users_id' => $this->adminUser->id,
            'status_id' => $this->status->id,
            'type' => 'pasj',
            'student_count' => 32,
        ]);

        $widget = new SalesForceStatsWidget;
        $data = $widget->getChartData();

        $pasjDetail = collect($data['details'])->firstWhere('program_type', 'pasj');
        $this->assertNotNull($pasjDetail);
        $this->assertEquals(32, $pasjDetail['student_count']);
        $this->assertEquals('0.01%', $pasjDetail['percentage_formatted']);
    }

    public function test_widget_handles_case_insensitivity_and_whitespace_for_program_type(): void
    {
        $this->actingAs($this->adminUser);

        RegistrationData::factory()->create([
            'users_id' => $this->adminUser->id,
            'status_id' => $this->status->id,
            'type' => '  PASJ  ',
            'student_count' => 500,
        ]);

        $widget = new SalesForceStatsWidget;
        $data = $widget->getChartData();

        $pasjDetail = collect($data['details'])->firstWhere('program_type', 'pasj');
        $this->assertNotNull($pasjDetail);
        $this->assertEquals(500, $pasjDetail['student_count']);
        $this->assertEquals(1, $pasjDetail['school_count']);
        $this->assertEquals(100.0, $pasjDetail['percentage']);
    }

    public function test_widget_dynamically_includes_custom_program_not_in_enum(): void
    {
        $this->actingAs($this->adminUser);

        RegistrationData::factory()->create([
            'users_id' => $this->adminUser->id,
            'status_id' => $this->status->id,
            'type' => 'seminar',
            'student_count' => 200,
        ]);

        $widget = new SalesForceStatsWidget;
        $data = $widget->getChartData();

        $expectedCount = count(Program::cases()) + 1;
        $this->assertEquals($expectedCount, $data['totals']['programs']);
        $this->assertContains('SEMINAR', $data['labels']);

        $seminarDetail = collect($data['details'])->firstWhere('program_type', 'seminar');
        $this->assertNotNull($seminarDetail);
        $this->assertEquals(200, $seminarDetail['student_count']);
        $this->assertEquals(100.0, $seminarDetail['percentage']);
    }

    public function test_widget_renders_livewire_component_successfully(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(SalesForceStatsWidget::class)
            ->assertSuccessful()
            ->assertSee('Rekap Program')
            ->assertSee('PASJ');
    }
}
