<?php

namespace Tests\Unit;

use App\Filament\Components\Support\SharedSchema;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Columns\Layout\Split;
use Tests\TestCase;

class SharedSchemaAndProgramTest extends TestCase
{
    /**
     * Test SharedSchema columns array structure.
     */
    public function test_shared_schema_columns(): void
    {
        $columns = SharedSchema::columns();

        $this->assertIsArray($columns);
        $this->assertCount(2, $columns);
        $this->assertInstanceOf(Split::class, $columns[0]);

        $columnNames = $this->flattenColumnNames($columns);

        $this->assertContains('schools', $columnNames);
        $this->assertContains('latestStatusLog.status.name', $columnNames);
        $this->assertNotContains('latestStatusLog.status.color', $columnNames);
    }

    /**
     * @param  array<int, mixed>  $components
     * @return list<string>
     */
    private function flattenColumnNames(array $components): array
    {
        $names = [];

        foreach ($components as $component) {
            if (method_exists($component, 'getComponents')) {
                array_push($names, ...$this->flattenColumnNames($component->getComponents()));

                continue;
            }

            $names[] = $component->getName();
        }

        return $names;
    }

    /**
     * Test SharedSchema getDifference function.
     */
    public function test_shared_schema_get_difference(): void
    {
        $get = $this->createMock(Get::class);
        $set = $this->createMock(Set::class);

        $get->method('__invoke')
            ->willReturnMap([
                ['account_count_created', 10],
                ['implementer_count', 8],
            ]);

        $set->expects($this->once())
            ->method('__invoke')
            ->with('difference', 2);

        SharedSchema::getDifference($get, $set);
    }
}
