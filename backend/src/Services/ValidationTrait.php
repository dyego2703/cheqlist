<?php

namespace Cheqlist\Services;

use Cheqlist\Exceptions\ValidationException;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validatable;

trait ValidationTrait
{
    /**
     * @param callable():Validatable $rules
     */
    protected function assert(array $payload, callable $rules): void
    {
        try {
            $rules()->assert($payload);
        } catch (NestedValidationException $exception) {
            $messages = [];
            foreach ($exception->getMessages() as $key => $message) {
                $messages[$key] = [$message];
            }

            throw new ValidationException($messages);
        }
    }
}
