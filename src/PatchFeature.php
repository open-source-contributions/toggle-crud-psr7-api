<?php

declare(strict_types=1);

namespace Pheature\Crud\Psr7\Toggle;

use Pheature\Crud\Toggle\Command\DisableFeature as DisableFeatureCommand;
use Pheature\Crud\Toggle\Command\EnableFeature as EnableFeatureCommand;
use Pheature\Crud\Toggle\Handler\AddStrategy;
use Pheature\Crud\Toggle\Handler\DisableFeature;
use Pheature\Crud\Toggle\Handler\EnableFeature;
use Pheature\Crud\Toggle\Handler\RemoveStrategy;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Webmozart\Assert\Assert;

final class PatchFeature implements RequestHandlerInterface
{
    private AddStrategy $addStrategy;
    private RemoveStrategy $removeStrategy;
    private EnableFeature $enableFeature;
    private DisableFeature $disableFeature;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(
        AddStrategy $addStrategy,
        RemoveStrategy $removeStrategy,
        EnableFeature $enableFeature,
        DisableFeature $disableFeature,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->addStrategy = $addStrategy;
        $this->removeStrategy = $removeStrategy;
        $this->enableFeature = $enableFeature;
        $this->disableFeature = $disableFeature;
        $this->responseFactory = $responseFactory;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $patchRequest = new PatchRequest($request);
            if ($patchRequest->isAddStrategyAction()) {
                $this->addStrategy->handle($patchRequest->addStrategyCommand());
            }
            if ($patchRequest->isRemoveStrategyAction()) {
                $this->removeStrategy->handle($patchRequest->removeStrategyCommand());
            }
        } catch (\InvalidArgumentException $exception) {
            return $this->responseFactory->createResponse(400, 'Bad request.');
        }

        return $this->responseFactory->createResponse(202, 'Processed.');
    }

    private function enableFeature(string $featureId): void
    {
        $this->enableFeature->handle(
            EnableFeatureCommand::withId($featureId)
        );
    }

    private function disableFeature(string $featureId): void
    {
        $this->disableFeature->handle(
            DisableFeatureCommand::withId($featureId)
        );
    }
}
