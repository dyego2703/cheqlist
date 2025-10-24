<?php

declare(strict_types=1);

namespace Cheqlist\Tests;

use Cheqlist\Exceptions\ValidationException;
use Cheqlist\Services\ActivityService;
use Cheqlist\Services\EquipmentService;
use PHPUnit\Framework\TestCase;

class ActivityServiceTest extends TestCase
{
    private ActivityService $service;

    protected function setUp(): void
    {
        $equipment = new EquipmentService();
        $this->service = new ActivityService($equipment);
    }

    public function testCreateActivityRequiresValidEquipment(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->create([
            'name' => 'Test',
            'description' => 'Invalid equipment reference',
            'equipmentId' => 'unknown',
        ]);
    }

    public function testListReturnsSeededActivities(): void
    {
        $activities = $this->service->list();
        $this->assertNotEmpty($activities);
        $this->assertSame('act-1', $activities[0]['id']);
    }
}
