<?php

declare(strict_types=1);

namespace Cheqlist\Tests;

use Cheqlist\Exceptions\ValidationException;
use Cheqlist\Services\EquipmentService;
use PHPUnit\Framework\TestCase;

class EquipmentServiceTest extends TestCase
{
    private EquipmentService $service;

    protected function setUp(): void
    {
        $this->service = new EquipmentService();
    }

    public function testCreateEquipmentWithValidData(): void
    {
        $created = $this->service->create([
            'name' => 'CNC Mill',
            'category' => 'Manufacturing',
            'status' => 'available',
        ]);

        $this->assertArrayHasKey('id', $created);
        $this->assertSame('CNC Mill', $created['name']);
        $this->assertCount(3, array_intersect_key($created, ['name' => true, 'category' => true, 'status' => true]));
    }

    public function testCreateEquipmentWithInvalidStatusThrowsException(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->create([
            'name' => 'Broken Tool',
            'category' => 'Misc',
            'status' => 'retired',
        ]);
    }

    public function testUpdateNonExistingEquipmentThrowsException(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->update('missing', [
            'name' => 'Updated',
            'category' => 'Test',
            'status' => 'available',
        ]);
    }
}
