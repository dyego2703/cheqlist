<?php

namespace Cheqlist\Services;

use Cheqlist\Exceptions\ValidationException;
use Respect\Validation\Validator as v;

class RecordService extends AbstractService
{
    use ValidationTrait;

    public function __construct(private readonly InstanceService $instanceService)
    {
        $this->items = [
            'rec-1' => [
                'id' => 'rec-1',
                'instanceId' => 'ins-1',
                'completedAt' => '2025-01-20T11:30:00Z',
                'notes' => 'Calibration finished successfully.',
                'metrics' => [
                    'durationMinutes' => 90,
                    'operator' => 'Sam Carter',
                ],
            ],
        ];
    }

    protected function validate(array $payload, ?string $id = null): void
    {
        $this->assert($payload, static function () {
            return v::arrayType()
                ->key('instanceId', v::stringType()->length(1, 120))
                ->key('completedAt', v::dateTime())
                ->key('notes', v::optional(v::stringType()->length(0, 500)))
                ->key('metrics', v::optional(v::arrayType()));
        });

        try {
            $this->instanceService->get($payload['instanceId']);
        } catch (ValidationException $exception) {
            throw new ValidationException([
                'instanceId' => ['Unknown instance reference'],
            ]);
        }
    }
}
