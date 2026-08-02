<?php

declare(strict_types=1);

namespace Yansongda\Artful\Contract;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This file mostly code come from symfony/event-dispatcher,
 * thanks provide such a useful class.
 */
interface EventDispatcherInterface extends \Psr\EventDispatcher\EventDispatcherInterface
{
    /**
     * Adds an event listener that listens on the specified events.
     *
     * @param int $priority The higher this value, the earlier an event
     *                      listener will be triggered in the chain (defaults to 0)
     */
    public function addListener(string $eventName, callable $listener, int $priority = 0): void;

    /**
     * Adds an event subscriber.
     *
     * The subscriber is asked for all the events it is
     * interested in and added as a listener for these events.
     */
    public function addSubscriber(EventSubscriberInterface $subscriber): void;

    /**
     * Removes an event listener from the specified events.
     */
    public function removeListener(string $eventName, callable $listener): void;

    public function removeSubscriber(EventSubscriberInterface $subscriber): void;

    /**
     * Gets the listeners of a specific event or all listeners sorted by descending priority.
     *
     * @return array<callable|callable[]>
     */
    public function getListeners(?string $eventName = null): array;

    /**
     * Gets the listener priority for a specific event.
     *
     * Returns null if the event or the listener does not exist.
     */
    public function getListenerPriority(string $eventName, callable $listener): ?int;

    /**
     * Checks whether an event has any registered listeners.
     */
    public function hasListeners(?string $eventName = null): bool;
}
