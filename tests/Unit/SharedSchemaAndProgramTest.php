<?php

namespace Tests\Unit;

use App\Filament\Enum\Program;
use App\Filament\Components\Support\SharedSchema;
use Filament\Tables\Columns\Layout\Split;
use Tests\TestCase;

class SharedSchemaAndProgramTest extends TestCase
{
    /**
     * Test Program enum getMetadata function with various casings and fallbacks.
     */
    public function test_program_enum_get_metadata(): void
    {
        // Test lowercase
        $anbkMeta = Program::getMetadata('anbk');
        $this->assertEquals('ANBK', $anbkMeta['nameRegister']);
        $this->assertEquals('ASESMEN NASIONAL BERBASIS KOMPUTER', $anbkMeta['DescriptionRegister']);

        // Test uppercase (case insensitivity)
        $anbkUpperMeta = Program::getMetadata('ANBK');
        $this->assertEquals('ANBK', $anbkUpperMeta['nameRegister']);
        $this->assertEquals('ASESMEN NASIONAL BERBASIS KOMPUTER', $anbkUpperMeta['DescriptionRegister']);

        // Test fallback to apps (default fallback)
        $invalidMeta = Program::getMetadata('invalid_program');
        $this->assertEquals('APPS', $invalidMeta['nameRegister']);
        $this->assertEquals('ASESMEN PSIKOTES POTENSI SISWA', $invalidMeta['DescriptionRegister']);

        // Test fallback to custom default (e.g. none)
        $noneMeta = Program::getMetadata('invalid_program', 'none');
        $this->assertEquals('NONE', $noneMeta['nameRegister']);
        $this->assertEquals('NONE', $noneMeta['DescriptionRegister']);
    }

    /**
     * Test SharedSchema columns array structure.
     */
    public function test_shared_schema_columns(): void
    {
        $columns = SharedSchema::columns();

        $this->assertIsArray($columns);
        $this->assertCount(1, $columns);
        $this->assertInstanceOf(Split::class, $columns[0]);
    }

    /**
     * Test SharedSchema getDifference function.
     */
    public function test_shared_schema_get_difference(): void
    {
        $get = $this->createMock(\Filament\Forms\Get::class);
        $set = $this->createMock(\Filament\Forms\Set::class);

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
