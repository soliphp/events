<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Events;

/**
 * 事件管理器接口
 */
interface EventManagerInterface
{
    /**
     * 注册某个事件的监听器
     *
     * @param string $name
     * @param object $listener
     */
    public function attach($name, $listener);

    /**
     * 移除某个事件的监听器
     *
     * @param string $name
     * @param object $listener
     */
    public function detach($name, $listener);

    /**
     * 触发事件
     *
     * 可以接受一个 EventInterface，如果没有传就会创建一个
     *
     * @param string|EventInterface $name
     * @param object|string $target
     * @param mixed $data
     * @return mixed
     */
    public function trigger($name, $target = null, $data = null);
}
