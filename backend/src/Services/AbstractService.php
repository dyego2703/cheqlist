<?php

namespace Cheqlist\Services;

use Cheqlist\Exceptions\ValidationException;

abstract class AbstractService
{
    /**
     * @var array<string,array<string,mixed>>
     */
    protected array $items = [];

    public function list(): array
    {
        return array_values($this->items);
    }

    public function get(string $id): array
    {
        if (!isset($this->items[$id])) {
            throw new ValidationException(['id' => ['Resource not found']], 'Resource not found', 404);
        }

        return $this->items[$id];
    }

    public function delete(string $id): void
    {
        if (!isset($this->items[$id])) {
            throw new ValidationException(['id' => ['Resource not found']], 'Resource not found', 404);
        }

        unset($this->items[$id]);
    }

    protected function persist(array $payload, ?string $id = null): array
    {
        $resourceId = $id ?? $this->generateId();
        $record = array_merge(['id' => $resourceId], $payload);
        $this->items[$resourceId] = $record;

        return $record;
    }

    protected function generateId(): string
    {
        return bin2hex(random_bytes(8));
    }

    /**
     * @throws ValidationException
     */
    abstract protected function validate(array $payload, ?string $id = null): void;

    /**
     * @throws ValidationException
     */
    public function create(array $payload): array
    {
        $this->validate($payload);

        return $this->persist($payload);
    }

    /**
     * @throws ValidationException
     */
    public function update(string $id, array $payload): array
    {
        if (!isset($this->items[$id])) {
            throw new ValidationException(['id' => ['Resource not found']], 'Resource not found', 404);
        }

        $this->validate($payload, $id);

        return $this->persist(array_merge($this->items[$id], $payload), $id);
    }
}
