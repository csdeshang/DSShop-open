<?php

declare(strict_types=1);

namespace Yansongda\Artful;

use Yansongda\Artful\Contract\EventDispatcherInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\Exception;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;

/**
 * @method static Event\Event dispatch(object $event)
 * @method static void        addListener(string $eventName, callable $listener, int $priority = 0)
 */
class Event
{
    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     * @throws InvalidParamsException
     */
    public static function __callStatic(string $method, array $args): void
    {
        if (!Artful::hasContainer() || !Artful::has(EventDispatcherInterface::class)) {
            return;
        }

        $class = Artful::get(EventDispatcherInterface::class);

        if (!$class instanceof \Psr\EventDispatcher\EventDispatcherInterface) {
            throw new InvalidParamsException(Exception::PARAMS_EVENT_DRIVER_INVALID, '参数异常: 配置的 `EventDispatcherInterface` 不符合 PSR 规范');
        }

        if (!method_exists($class, $method)) {
            throw new InvalidParamsException(Exception::PARAMS_EVENT_DRIVER_INVALID, "参数异常: {$method} 在配置的 `EventDispatcherInterface` 中不存在");
        }

        $class->{$method}(...$args);
    }
}
