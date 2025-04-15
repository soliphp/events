<?php

namespace Soli\Tests\Events;

use PHPUnit\Framework\TestCase;

use Soli\Events\EventManager;
use Soli\Events\Event;

use Soli\Tests\Data\Events\EComponent;
use Soli\Tests\Data\Events\EComponentEvents;

class EventManagerTest extends TestCase
{
    public function testTrait()
    {
        $eventManager = new EventManager();
        $eComponent = new EComponent();

        $eComponent->setEventManager($eventManager);

        $this->assertTrue($eventManager === $eComponent->getEventManager());
    }

    public function testTriggerByClosure()
    {
        $expected = 'before';
        $this->expectOutputString($expected);

        $eventManager = new EventManager();

        $before = function (Event $event) use ($expected) {
            echo $expected;
        };

        // 监听事件
        $eventManager->attach('before', $before);

        $status = $eventManager->trigger('before');
        $this->assertTrue($status);
    }

    public function testTriggerByInstance()
    {
        $expected = 'after';
        $this->expectOutputString($expected);

        $eventManager = new EventManager();

        $eventManager->attach('my-component', new EComponentEvents());

        $r = $eventManager->trigger('my-component.before');
        $this->assertFalse($r);

        $r = $eventManager->trigger('my-component.after');
        $this->assertTrue($r);
    }

    public function testTriggerByClosureAndInstance()
    {
        $expected = 'after';
        $this->expectOutputString($expected . $expected);

        $eventManager = new EventManager();

        $after = function (Event $event) use ($expected) {
            echo $expected;
        };

        // 监听事件
        $eventManager->attach('my-component.after', $after);

        $eventManager->attach('my-component', new EComponentEvents());

        $status = $eventManager->trigger('my-component.after');
        $this->assertTrue($status);
    }

    public function testTriggerEmptyEvents()
    {
        $eventManager = new EventManager();

        $r = $eventManager->trigger('events.empty');

        $this->assertFalse($r);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid event type
     */
    public function testTriggerInvalidEventType()
    {
        $this->expectException(\InvalidArgumentException::class);

        $eventManager = new EventManager();

        $before = function (Event $event, $eComponent) {
            return 'before';
        };

        // 监听事件
        $eventManager->attach('my-component.before', $before);

        $eventManager->trigger(new \stdClass(), $eventManager);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid event type
     */
    public function testTriggerInvalidEventType2()
    {
        $this->expectException(\InvalidArgumentException::class);

        $eventManager = new EventManager();

        $before = function (Event $event, $eComponent) {
            return 'before';
        };

        // 监听事件
        $eventManager->attach('my-component.before', $before);

        $eventManager->trigger(1234, $eventManager);
    }

    public function testTriggerEventInstance()
    {
        $expected = 'before';
        $this->expectOutputString($expected);

        $eventManager = new EventManager();

        $before = function (Event $event) use ($expected) {
            echo $event->getData();
        };

        $name = 'my-component.before';

        // 监听事件
        $eventManager->attach($name, $before);

        $event = new Event($name, $this);

        $r = $eventManager->trigger($event, $this, $expected);

        $this->assertTrue($r);
    }

    public function testClearListeners()
    {
        $eventManager = new EventManager();

        $before = function (Event $event, $eComponent) {
            return 'before';
        };

        // 监听事件
        $eventManager->attach('my-component.before', $before);

        $eventManager->clearListeners('my-component.before');

        $listeners = $eventManager->getListeners('my-component.before');
        $this->assertEmpty($listeners);
    }

    public function testGetListeners()
    {
        $eventManager = new EventManager();

        $before = function (Event $event, $eComponent) {
            return 'before';
        };

        $eventManager->attach('my-component.before', $before);

        $listeners = $eventManager->getListeners('my-component.before');
        $this->assertTrue($before === $listeners[0]);

        // detach
        $eventManager->detach('my-component.before', $before);

        $listeners = $eventManager->getListeners('my-component.before');
        $this->assertEmpty($listeners);
    }

    public function testStopPropagation()
    {
        $expected = 'before listener return value.';
        $this->expectOutputString($expected);

        $eventManager = new EventManager();

        $before = function (Event $event) use ($expected) {
            $event->stopPropagation();
            echo $expected;
        };

        $before2 = function (Event $event) {
            echo 'Will not be executed.';
        };

        $eventManager->attach('my-component.before', $before);
        $eventManager->attach('my-component.before', $before2);

        $r = $eventManager->trigger('my-component.before');
        $this->assertTrue($r);
    }
}
