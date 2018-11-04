<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Events;

use Closure;

/**
 * 事件管理器
 *
 * 管理事件的注册、删除和调度(触发)。
 *
 *<pre>
 * use Soli\Events\EventManager;
 * use Soli\Events\Event;
 *
 * $eventManager = new EventManager();
 *
 * // 注册具体的某个事件监听器
 * $eventManager->attach('app.boot', function (Event $event, $app) {
 *     echo "应用已启动\n";
 * });
 *
 * // 也可以将针对 "app" 的事件统一整理到 AppEvents 类，一并注册
 * $eventManager->attach('app', new AppEvents());
 *
 * // 触发某个具体事件
 * $eventManager->trigger('app.boot', $this);
 *</pre>
 */
class EventManager implements EventManagerInterface
{
    /**
     * 事件列表
     *
     * @var array
     */
    protected $events;

    /**
     * 注册某个事件的监听器
     *
     * @param string $name 事件名称
     * @param object $listener 监听器（匿名函数、对象实例）
     */
    public function attach($name, $listener)
    {
        // 追加到事件队列
        $this->events[$name][] = $listener;
    }

    /**
     * 移除某个事件的监听器
     *
     * @param string $name 事件名称
     * @param object $listener 监听器（匿名函数、对象实例）
     */
    public function detach($name, $listener)
    {
        if (isset($this->events[$name])) {
            $key = array_search($listener, $this->events[$name], true);
            if ($key !== false) {
                unset($this->events[$name][$key]);
            }
        }
    }

    /**
     * 触发事件
     *
     *<code>
     * $eventManager->trigger('app.boot', $app, $data);
     *
     * $event = new Event('app.finish', $app, $data);
     * $eventManager->trigger($event);
     *</code>
     *
     * @param string|EventInterface $event 事件名称或事件对象实例
     * @param object|string $target 事件来源
     * @param mixed $data 事件相关数据，可被监听器调用
     * @return bool 是否执行了当前事件的监听器
     * @throws \InvalidArgumentException
     */
    public function trigger($event, $target = null, $data = null)
    {
        if (!is_array($this->events)) {
            return false;
        }

        if (is_object($event) && $event instanceof EventInterface) {
            $name = $event->getName();
        } elseif (is_string($event)) {
            $name = $event;
            $event = null;
        } else {
            throw new \InvalidArgumentException('Invalid event type');
        }

        /**
         * @param EventInterface|null $event
         * @return Event
         */
        $eventInstance = function ($event) use ($name, $target, $data) {
            if ($event === null) {
                $event = new Event($name, $target, $data);
            } elseif ($data) {
                $event->setData($data);
            }
            return $event;
        };

        // 执行当前事件监听器的个数
        $counter = 0;

        if (strpos($name, '.')) {
            // 事件空间.事件名称
            list($eventSpace, $eventName) = explode('.', $name, 2);

            // 以事件空间添加的事件
            if (isset($this->events[$eventSpace])) {
                $counter += $this->notify($this->events[$eventSpace], $eventInstance($event));
            }
        }

        // 以具体的事件名称添加的事件
        if (isset($this->events[$name])) {
            // 通知事件监听者
            $counter += $this->notify($this->events[$name], $eventInstance($event));
        }

        return $counter > 0;
    }

    /**
     * 触发事件监听队列
     *
     * @param array $queue
     * @param EventInterface $event
     * @return bool
     */
    protected function notify(array $queue, EventInterface $event)
    {
        // 执行当前事件监听器的个数
        $counter = 0;

        $name = $event->getName();
        $target = $event->getTarget();
        $data = $event->getData();

        $eventName = null;
        if (strpos($name, '.')) {
            // 事件空间.事件名称
            list($eventSpace, $eventName) = explode('.', $name, 2);
        }

        foreach ($queue as $listener) {
            if ($listener instanceof Closure) {
                // 调用闭包监听器
                call_user_func_array($listener, [$event, $target, $data]);
                $counter++;
            } elseif ($eventName && method_exists($listener, $eventName)) {
                // 调用对象监听器
                $listener->{$eventName}($event, $target, $data);
                $counter++;
            }

            if ($event->isPropagationStopped()) {
                break;
            }
        }

        return $counter;
    }

    /**
     * 清除某个事件的监听器列表
     *
     * @param string $name 事件名称
     * @return void
     */
    public function clearListeners($name)
    {
        if (is_array($this->events) && isset($this->events[$name])) {
            unset($this->events[$name]);
        }
    }

    /**
     * 获取某个事件的监听器列表
     *
     * @param string $name 事件名称
     * @return array
     */
    public function getListeners($name)
    {
        if (is_array($this->events) && isset($this->events[$name])) {
            return $this->events[$name];
        }

        return [];
    }
}
