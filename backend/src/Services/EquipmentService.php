<?php

namespace Cheqlist\Services;

use Respect\Validation\Validator as v;

class EquipmentService extends AbstractService
{
    use ValidationTrait;

    public function __construct()
    {
        $this->items = [
            'eq-1' => [
                'id' => 'eq-1',
                'name' => '3D Printer',
                'category' => 'Manufacturing',
                'status' => 'available',
            ],
            'eq-2' => [
                'id' => 'eq-2',
                'name' => 'Laser Cutter',
                'category' => 'Fabrication',
                'status' => 'maintenance',
            ],
        ];
    }

    protected function validate(array $payload, ?string $id = null): void
    {
        $this->assert($payload, static fn () => v::arrayType()
            ->key('name', v::stringType()->length(1, 120))
            ->key('category', v::stringType()->length(1, 120))
            ->key('status', v::in(['available', 'unavailable', 'maintenance']))
        );
    }
}
