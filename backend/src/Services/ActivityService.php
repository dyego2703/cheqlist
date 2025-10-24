<?php

namespace Cheqlist\Services;

use Cheqlist\Exceptions\ValidationException;
use Respect\Validation\Validator as v;

class ActivityService extends AbstractService
{
    use ValidationTrait;

    public function __construct(private readonly EquipmentService $equipmentService)
    {
        $this->items = [
            'act-1' => [
                'id' => 'act-1',
                'name' => 'Calibration',
                'description' => 'Calibrate the 3D printer bed.',
                'equipmentId' => 'eq-1',
            ],
        ];
    }

    protected function validate(array $payload, ?string $id = null): void
    {
        $this->assert($payload, static function () {
            return v::arrayType()
                ->key('name', v::stringType()->length(1, 120))
                ->key('description', v::stringType()->length(1, 500))
                ->key('equipmentId', v::stringType()->length(1, 120));
        });

        if (!isset($payload['equipmentId'])) {
            return;
        }

        try {
            $this->equipmentService->get($payload['equipmentId']);
        } catch (ValidationException $exception) {
            throw new ValidationException([
                'equipmentId' => ['Unknown equipment reference'],
            ]);
        }
    }
}
