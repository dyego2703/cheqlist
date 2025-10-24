<?php

namespace Cheqlist\Services;

use Cheqlist\Exceptions\ValidationException;
use Respect\Validation\Validator as v;

class InstanceService extends AbstractService
{
    use ValidationTrait;

    public function __construct(private readonly ActivityService $activityService)
    {
        $this->items = [
            'ins-1' => [
                'id' => 'ins-1',
                'activityId' => 'act-1',
                'scheduledFor' => '2025-01-20T09:00:00Z',
                'status' => 'scheduled',
            ],
        ];
    }

    protected function validate(array $payload, ?string $id = null): void
    {
        $this->assert($payload, static function () {
            return v::arrayType()
                ->key('activityId', v::stringType()->length(1, 120))
                ->key('scheduledFor', v::dateTime())
                ->key('status', v::in(['scheduled', 'in_progress', 'completed', 'cancelled']));
        });

        try {
            $this->activityService->get($payload['activityId']);
        } catch (ValidationException $exception) {
            throw new ValidationException([
                'activityId' => ['Unknown activity reference'],
            ]);
        }
    }
}
