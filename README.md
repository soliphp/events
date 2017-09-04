Soli Event Manager
------------------

当前项目参考 [Phalcon 框架的事件管理器]实现。

事件管理器的目的是为了通过创建"钩子"拦截框架或应用中的部分组件操作。

这些钩子允许开发者获得状态信息，操纵数据或者改变某个组件进程中的执行流向。

以上介绍摘自[Phalcon 框架的事件管理器]官方文档。

## 安装

使用 `composer` 安装到你的项目：

    composer require soliphp/events

## 命名约定

当前事件管理器的命名规则采用分组的方式，目的是为了避免不同组件间的事件名称重名，产生碰撞，
同时也便于对项目中同一维度的事件进行聚合整理；事件命名格式为 "component:event"，
类比类的命名空间，我们暂且将这种对事件的命名方式称之为「事件命名空间」。

如，我们有一个 `\Soli\Application` 类，它的事件命名空间可以定义为 "application"，
对于此类 "boot" 事件的全名为 "application:boot"。

## 使用

### 针对具体的某个事件设置监听器

    use Soli\Events\EventManager;
    use Soli\Events\Event;

    $eventManager = new EventManager();

    $eventManager->attach('application:boot', function (Event $event, $application) {
        echo "应用已启动\n";
    });

`监听器的格式`，可以是 `匿名函数或对象实例`。

如，我们这里定义一个 `AppEvents` 类用于处理针对 `Application` 类的事件：

    class AppEvents
    {
        public function boot(Event $event, $application)
        {
            // 导出内部（事件）数据或状态给外部（监听器）调用者
            $data = $event->getData();

            echo "应用已启动\n";
        }

        public function finish(Event $event, $application, $extraData)
        {
            echo "应用执行结束\n";
        }
    }

    // 注册事件监听

    // 匿名函数
    $eventManager->attach('application:boot', function (Event $event, $application) {
        $ver = $application::VERSION;
        echo "应用已启动 $ver\n";
    });

    // 对象实例
    $eventManager->attach('application:boot', new AppEvents);

### 聚合事件监听器到专门的事件类中进行处理

上面我们定义了 `AppEvents` 类，其中有两个方法 `boot` 和 `finish`，
这两个方法可以直接用来监听 `application:boot` 事件和 `application:finish` 事件，
注册方法很简单，如下：

    $eventManager->attach('application', new AppEvents);

这样我们便可以很方便的注册和整理不同维度的不同事件。

### 触发事件

触发事件调用 `trigger` 方法，其参数为具体的某个事件名称，事件源（当前类），
也可以传入更多整合后的数据，供监听器使用。

    $eventManager->trigger('application:boot', $this, $extraData);

### 事件传播

    $eventManager->attach('application:boot', function (Event $event, $application) {
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
getParams()，而事实上 getParam() 仅仅是获取参数类型为 array 时的单个元素，
那 object 是想怎么获取？这样的接口体验还不如直接使用 getData()
一个方法，交给开发者自己来处理。

## 测试

    $ cd /path/to/soliphp/events/
    $ composer install
    $ phpunit

## License

MIT Public License


[Phalcon 框架的事件管理器]: https://docs.phalconphp.com/en/latest/events
[PSR-14]: https://github.com/php-fig/fig-standards/blob/master/proposed/event-manager.md
