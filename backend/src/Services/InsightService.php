<?php

namespace Cheqlist\Services;

class InsightService
{
    public function __construct(
        private readonly EquipmentService $equipmentService,
        private readonly ActivityService $activityService,
        private readonly InstanceService $instanceService,
        private readonly RecordService $recordService
    ) {
    }

    public function summarize(): array
    {
        $instances = $this->instanceService->list();
        $completed = array_filter($instances, static fn (array $instance) => $instance['status'] === 'completed');

        return [
            'totals' => [
                'equipments' => count($this->equipmentService->list()),
                'activities' => count($this->activityService->list()),
                'instances' => count($instances),
                'records' => count($this->recordService->list()),
            ],
            'instances' => [
                'completed' => count($completed),
                'scheduled' => count(array_filter($instances, static fn (array $instance) => $instance['status'] === 'scheduled')),
            ],
        ];
    }
}
