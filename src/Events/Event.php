<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Events;

/**
 * 事件原型
 *
 * @codeCoverageIgnore
 */
class Event implements EventInterface
{
    /**
     * 事件名称，当事件监听器为对象时，事件名称对应事件监听器中的方法名
     *
     * @var string
     */
    protected $name;

    /**
     * 事件来源
     *
     * @var object|string
     */
    protected $target;

    /**
     * 事件相关数据
     *
     * @var mixed
     */
    protected $data;

    /**
     * 是否停止触发未调用的监听器
     *
     * @var bool
     */
    protected $stopped = false;

    /**
     * Event constructor.
     *
     * @param string $name
     * @param string|object $target
     * @param mixed $data
     */
    public function __construct($name, $target = null, $data = null)
    {
        $this->name = $name;
        $this->target = $target;
        $this->data = $data;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setTarget($target)
    {
        $this->target = $target;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function stopPropagation()
    {
        $this->stopped = true;
    }

    public function isPropagationStopped()
    {
        return $this->stopped;
    }
}
