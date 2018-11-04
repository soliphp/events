Soli Event Manager
------------------

事件管理器，管理事件的注册、删除和调度(触发)。

[![Build Status](https://travis-ci.org/soliphp/events.svg?branch=master)](https://travis-ci.org/soliphp/events)
[![Coverage Status](https://coveralls.io/repos/github/soliphp/events/badge.svg?branch=master)](https://coveralls.io/github/soliphp/events?branch=master)
[![License](https://poser.pugx.org/soliphp/events/license)](https://packagist.org/packages/soliphp/events)

## 安装

使用 `composer` 安装到你的项目：

    composer require soliphp/events

## 使用

### 针对具体的某个事件设置监听器

    use Soli\Events\EventManager;
    use Soli\Events\Event;

    $eventManager = new EventManager();

    $eventManager->attach('app.boot', function (Event $event, $app) {
        echo "应用已启动\n";
    });

`监听器的格式`，可以是 `匿名函数或对象实例`。

如，我们这里定义一个 `AppEvents` 类用于处理针对 `App` 类的事件：

    class AppEvents
    {
        public function boot(Event $event, $app)
        {
            // 导出内部（事件）数据或状态给外部（监听器）调用者
            $data = $event->getData();

            echo "应用已启动\n";
        }

        public function finish(Event $event, $app, $data)
        {
            echo "应用执行结束\n";
        }
    }

    // 注册事件监听

    // 匿名函数
    $eventManager->attach('app.boot', function (Event $event, $app) {
        $ver = $app::VERSION;
        echo "应用已启动 $ver\n";
    });

    // 对象实例
    $eventManager->attach('app.boot', new AppEvents);

### 聚合事件监听器到专门的事件类中进行处理

上面我们定义了 `AppEvents` 类，其中有两个方法 `boot` 和 `finish`，
这两个方法可以直接用来监听 `app.boot` 事件和 `app.finish` 事件，
注册方法很简单，如下：

    $eventManager->attach('app', new AppEvents);

这样我们便可以很方便的注册和整理不同维度的不同事件。

### 触发事件

触发事件调用 `trigger` 方法，其参数为具体的某个事件名称，事件源（当前类），
也可以传入更多整合后的数据，供监听器使用。

    $eventManager->trigger('app.boot', $this, $data);

### 事件传播

    $eventManager->attach('app.boot', function (Event $event, $app) {
        // 终止事件传播，这样其他的侦听器就不会再收到此事件通知
        $event->stopPropagation();
    });

## PSR-14

`soliphp/events` 实现了绝大部分的 [PSR-14] 的接口，但是与 [PSR-14] 也有一些不同。

1. EventManagerInterface 的 clearListeners 接口，实现了但未写到
   EventManagerInterface 接口文件中。

对比 clearListeners 的命名格式，添加和移除某个监听器的命名应为 addListener、removeListener，
而不应为 attach、detach，但是事实上我们更习惯使用 attach、detach
的命名，于是便搁置了 clearListeners 方法，暂时不进入 EventManagerInterface
接口文件。等待 [PSR-14] 正式通过后，再做调整。

2. EventManagerInterface 的 trigger 接口实现

[PSR-14] 上 trigger 接口方法为：

    /**
     * Trigger an event
     *
     * Can accept an EventInterface or will create one if not passed
     *
     * @param  string|EventInterface $event
     * @param  object|string $target
     * @param  array|object $argv
     * @return mixed
     */
    public function trigger($event, $target = null, $argv = array());

第三个参数 $argv 类型为 array|object，事实上这个参数类型可以更广泛 string/int 等。

故这里将 $argv 参数类型改为 mixed，默认值改为 null，且命名为 $data：

    /**
     * @param mixed $data
     */
    public function trigger($event, $target = null, $data = null);

3. EventInterface 使用 get/setData 替换 get/setParams、getParam

第2条说到 $data（即 $argv）的参数类型为 mixed，因此我们直接使用 get/setData
替换之；

且假如第2条 $argv 不做参数类型的更改，get/setParams、getParam 三个接口
并不能直接体现 $argv 为 object 时的情况，是选择 getParam() 还是 getParams()
方法获取 $argv 参数，会疑惑开发者 array 时用 getParams()，而 object 时用
getParam()，而事实上 getParam() 仅仅是获取参数类型为 array 时的单个元素，
那 object 是想怎么获取？这样的接口体验远不如直接使用 getData()
一个方法，交给开发者来处理具体的数据类型问题。

## 测试

    $ cd /path/to/soliphp/events/
    $ composer install
    $ phpunit

## License

MIT Public License


[Phalcon 框架的事件管理器]: https://docs.phalconphp.com/en/latest/events
[PSR-14]: https://github.com/php-fig/fig-standards/blob/master/proposed/event-manager.md
