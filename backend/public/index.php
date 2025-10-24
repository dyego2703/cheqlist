<?php

declare(strict_types=1);

use Cheqlist\Auth\AuthService;
use Cheqlist\Auth\JwtService;
use Cheqlist\Exceptions\ValidationException;
use Cheqlist\Middleware\JwtMiddleware;
use Cheqlist\Services\ActivityService;
use Cheqlist\Services\EquipmentService;
use Cheqlist\Services\InsightService;
use Cheqlist\Services\InstanceService;
use Cheqlist\Services\RecordService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$jwtSecret = getenv('JWT_SECRET') ?: 'insecure-dev-secret';

$equipmentService = new EquipmentService();
$activityService = new ActivityService($equipmentService);
$instanceService = new InstanceService($activityService);
$recordService = new RecordService($instanceService);
$insightService = new InsightService($equipmentService, $activityService, $instanceService, $recordService);
$jwtService = new JwtService($jwtSecret);
$jwtMiddleware = new JwtMiddleware($jwtService);
$authService = new AuthService();

$respondJson = static function (Response $response, mixed $payload, int $status = 200): Response {
    if ($payload !== null && $status !== 204) {
        $response->getBody()->write(json_encode($payload, JSON_PRETTY_PRINT));
    }

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($status);
};

$handleValidation = static function (Response $response, ValidationException $exception) use ($respondJson): Response {
    return $respondJson($response, [
        'message' => $exception->getMessage(),
        'errors' => $exception->getErrors(),
    ], $exception->getCode() ?: 422);
};

$app->post('/auth/login', function (Request $request, Response $response) use ($authService, $jwtService, $respondJson, $handleValidation) {
    $body = (array) $request->getParsedBody();
    $username = (string) ($body['username'] ?? '');
    $password = (string) ($body['password'] ?? '');

    try {
        $claims = $authService->authenticate($username, $password);
    } catch (ValidationException $exception) {
        return $handleValidation($response, $exception);
    }

    $token = $jwtService->generateToken($claims);

    return $respondJson($response, ['token' => $token]);
});

// Equipment routes
$app->get('/equipments', function (Request $request, Response $response) use ($equipmentService, $respondJson) {
    return $respondJson($response, $equipmentService->list());
});

$app->get('/equipments/{id}', function (Request $request, Response $response, array $args) use ($equipmentService, $respondJson, $handleValidation) {
    try {
        $equipment = $equipmentService->get($args['id']);
    } catch (ValidationException $exception) {
        return $handleValidation($response, $exception);
    }

    return $respondJson($response, $equipment);
});

$app->post('/equipments', function (Request $request, Response $response) use ($equipmentService, $respondJson, $handleValidation) {
    $payload = (array) $request->getParsedBody();

    try {
        $equipment = $equipmentService->create($payload);
    } catch (ValidationException $exception) {
        return $handleValidation($response, $exception);
    }

    return $respondJson($response, $equipment, 201);
})->add($jwtMiddleware);

$app->put('/equipments/{id}', function (Request $request, Response $response, array $args) use ($equipmentService, $respondJson, $handleValidation) {
    $payload = (array) $request->getParsedBody();

    try {
        $equipment = $equipmentService->update($args['id'], $payload);
    } catch (ValidationException $exception) {
        return $handleValidation($response, $exception);
    }

    return $respondJson($response, $equipment);
})->add($jwtMiddleware);

$app->delete('/equipments/{id}', function (Request $request, Response $response, array $args) use ($equipmentService, $respondJson, $handleValidation) {
    try {
        $equipmentService->delete($args['id']);
    } catch (ValidationException $exception) {
        return $handleValidation($response, $exception);
    }

    return $respondJson($response, null, 204);
})->add($jwtMiddleware);

// Activity routes
$app->get('/activities', function (Request $request, Response $response) use ($activityService, $respondJson) {
    return $respondJson($response, $activityService->list());
});

$app->get('/activities/{id}', function (Request $request, Response $response, array $args) use ($activityService, $respondJson, $handleValidation) {
    try {
        $activity = $activityService->get($args['id']);
    } catch (ValidationException $exception) {
        return $handleValidation($response, $exception);
    }

    return $respondJson($response, $activity);
});

$app->post('/activities', function (Request $request, Response $response) use ($activityService, $respondJson, $handleValidation) {
    $payload = (array) $request->getParsedBody();

    try {
        $activity = $activityService->create($payload);
    } catch (ValidationException $exception) {
        return $handleValidation($response, $exception);
    }

    return $respondJson($response, $activity, 201);
})->add($jwtMiddleware);

$app->put('/activities/{id}', function (Request $request, Response $response, array $args) use ($activityService, $respondJson, $handleValidation) {
    $payload = (array) $request->getParsedBody();

    try {
        $activity = $activityService->update($args['id'], $payload);
    } catch (ValidationException $exception) {
        return $handleValidation($response, $exception);
    }

    return $respondJson($response, $activity);
})->add($jwtMiddleware);

$app->delete('/activities/{id}', function (Request $request, Response $response, array $args) use ($activityService, $respondJson, $handleValidation) {
    try {
        $activityService->delete($args['id']);
    } catch (ValidationException $exception) {
        return $handleValidation($response, $exception);
    }

    return $respondJson($response, null, 204);
})->add($jwtMiddleware);

// Instance routes
$app->get('/instances', function (Request $request, Response $response) use ($instanceService, $respondJson) {
    return $respondJson($response, $instanceService->list());
});

$app->get('/instances/{id}', function (Request $request, Response $response, array $args) use ($instanceService, $respondJson, $handleValidation) {
    try {
        $instance = $instanceService->get($args['id']);
    } catch (ValidationException $exception) {
        return $handleValidation($response, $exception);
    }

    return $respondJson($response, $instance);
});

$app->post('/instances', function (Request $request, Response $response) use ($instanceService, $respondJson, $handleValidation) {
    $payload = (array) $request->getParsedBody();

    try {
        $instance = $instanceService->create($payload);
    } catch (ValidationException $exception) {
        return $handleValidation($response, $exception);
    }

    return $respondJson($response, $instance, 201);
})->add($jwtMiddleware);

$app->put('/instances/{id}', function (Request $request, Response $response, array $args) use ($instanceService, $respondJson, $handleValidation) {
    $payload = (array) $request->getParsedBody();

    try {
        $instance = $instanceService->update($args['id'], $payload);
    } catch (ValidationException $exception) {
        return $handleValidation($response, $exception);
    }

    return $respondJson($response, $instance);
})->add($jwtMiddleware);

$app->delete('/instances/{id}', function (Request $request, Response $response, array $args) use ($instanceService, $respondJson, $handleValidation) {
    try {
        $instanceService->delete($args['id']);
    } catch (ValidationException $exception) {
        return $handleValidation($response, $exception);
    }

    return $respondJson($response, null, 204);
})->add($jwtMiddleware);

// Record routes
$app->get('/records', function (Request $request, Response $response) use ($recordService, $respondJson) {
    return $respondJson($response, $recordService->list());
});

$app->get('/records/{id}', function (Request $request, Response $response, array $args) use ($recordService, $respondJson, $handleValidation) {
    try {
        $record = $recordService->get($args['id']);
    } catch (ValidationException $exception) {
        return $handleValidation($response, $exception);
    }

    return $respondJson($response, $record);
});

$app->post('/records', function (Request $request, Response $response) use ($recordService, $respondJson, $handleValidation) {
    $payload = (array) $request->getParsedBody();

    try {
        $record = $recordService->create($payload);
    } catch (ValidationException $exception) {
        return $handleValidation($response, $exception);
    }

    return $respondJson($response, $record, 201);
})->add($jwtMiddleware);

$app->put('/records/{id}', function (Request $request, Response $response, array $args) use ($recordService, $respondJson, $handleValidation) {
    $payload = (array) $request->getParsedBody();

    try {
        $record = $recordService->update($args['id'], $payload);
    } catch (ValidationException $exception) {
        return $handleValidation($response, $exception);
    }

    return $respondJson($response, $record);
})->add($jwtMiddleware);

$app->delete('/records/{id}', function (Request $request, Response $response, array $args) use ($recordService, $respondJson, $handleValidation) {
    try {
        $recordService->delete($args['id']);
    } catch (ValidationException $exception) {
        return $handleValidation($response, $exception);
    }

    return $respondJson($response, null, 204);
})->add($jwtMiddleware);

// Insights route
$app->get('/insights', function (Request $request, Response $response) use ($insightService, $respondJson) {
    return $respondJson($response, $insightService->summarize());
});

$app->run();
