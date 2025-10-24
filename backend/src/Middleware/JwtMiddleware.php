<?php

namespace Cheqlist\Middleware;

use Cheqlist\Auth\JwtService;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpUnauthorizedException;

class JwtMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly JwtService $jwtService)
    {
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            throw new HttpUnauthorizedException($request, 'Missing or invalid Authorization header');
        }

        $token = substr($authHeader, 7);

        try {
            $claims = $this->jwtService->validateToken($token);
        } catch (ExpiredException|SignatureInvalidException|InvalidArgumentException $exception) {
            throw new HttpUnauthorizedException($request, $exception->getMessage());
        }

        $request = $request->withAttribute('token', $claims);
        return $handler->handle($request);
    }
}
