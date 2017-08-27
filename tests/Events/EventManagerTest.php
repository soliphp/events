<?php

namespace Soli\Tests\Events;

use Soli\Tests\TestCase;
use Soli\Events\EventManager;
use Soli\Events\Event;

use Soli\Tests\Data\Events\EComponent;
use Soli\Tests\Data\Events\EComponentEvents;

class EventManagerTest extends TestCase
{
    public function testTrait()
    {
        $eventManager = new EventManager;
        $eComponent = new EComponent;

        $eComponent->setEventManager($eventManager);

        $this->assertTrue($eventManager === $eComponent->getEventManager());
    }

    public function testTriggerByClosure()
    {
        $eventManager = new EventManager;

        $before = function (Event $event, $eComponent) {
            return 'before';
        };

        // 监听事件
        $eventManager->on('my-component:before', $before);

        $result = $eventManager->trigger('my-component:before', $eventManager);
        $this->assertStringStartsWith('before', $result);
    }

    public function testTriggerByInstance()
    {
        $eventManager = new EventManager;

        $eventManager->on('my-component', new EComponentEvents());

        $result = $eventManager->trigger('my-component:before', $eventManager);
        $this->assertNull($result);

        $result = $eventManager->trigger('my-component:after', $eventManager);
        $this->assertStringStartsWith('after', $result);
    }

    public function testTriggerEmptyEvents()
    {
        $eventManager = new EventManager();

        $result = $eventManager->trigger('events:empty', $eventManager);

        $this->assertNull($result);
    }

    /**
     * @expectedException \Exception
     */
    public function testTriggerInvalidEventType()
    {
        $eventManager = new EventManager();

        $before = function (Event $event, $eComponent) {
            return 'before';
        };

        // 监听事件
        $eventManager->on('my-component:before', $before);

        $eventManager->trigger('invalidEventType', $eventManager);
    }

    public function testClearListeners()
    {
        $eventManager = new EventManager();

        $before = function (Event $event, $eComponent) {
            return 'before';
        };

        // 监听事件
        $eventManager->on('my-component:before', $before);

        $eventManager->clearListeners('my-component:before');

        $listeners = $eventManager->getListeners('my-component:before');
        $this->assertTrue(empty($listeners));
    }

    public function testGetListeners()
    {
        $eventManager = new EventManager();

        $before = function (Event $event, $eComponent) {
            return 'before';
        };

        $eventManager->on('my-component:before', $before);

        $listeners = $eventManager->getListeners('my-component:before');
        $this->assertFalse(empty($listeners));

        // off
        $eventManager->off('my-component:before', $before);

        $listeners = $eventManager->getListeners('my-component:before');
        $this->assertTrue(empty($listeners));
    }

    public function testStopPropagation()
    {
        $eventManager = new EventManager();

        $before = function (Event $event, $eComponent) {
            $event->stopPropagation();
            return 'before listener return value.';
        };

        $before2 = function (Event $event, $eComponent) {
            return 'Will not be executed.';
        };

        $eventManager->on('my-component:before', $before);
        $eventManager->on('my-component:before', $before2);

        $status = $eventManager->trigger('my-component:before');
        $this->assertEquals('before listener return value.', $status);
    }
}
