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

    $eventManager->on('application:boot', function (Event $event, $application) {
        echo "应用已启动\n";
    });

`监听器的格式`，可以是 `匿名函数或对象实例`。

如，我们这里定义一个 `AppEvents` 类用于处理针对 `Application` 类的事件：

    class AppEvents
    {
        public function boot(Event $event, $application)
        {
            echo "应用已启动\n";
        }

        public function beforeSendResponse(Event $event, $application, $extraData)
        {
            echo "Here, beforeSendResponse\n";
        }
    }

    // 注册事件监听

    // 匿名函数
    $eventManager->on('application:boot', function (Event $event, $application) {
        echo "应用已启动\n";
    });

    // 对象实例
    $eventManager->on('application:boot', new AppEvents);

### 聚合事件监听器到专门的事件类中进行处理

上面我们定义了 `AppEvents` 类，其中有两个方法 `boot` 和 `beforeSendResponse`，
这两个方法可以直接用来监听 `application:boot` 事件和 `application:beforeSendResponse` 事件，
注册方法很简单，如下：

    $eventManager->on('application', new AppEvents);

这样我们便可以很方便的注册和整理不同维度的不同事件。

### 触发事件

触发事件调用 `fire` 方法，其参数为具体的某个事件名称，事件源（当前类），
也可以传入更多整合后的数据，供监听器使用。

    $eventManager->fire('application:boot', $this, $extraData);

### 事件传播

    $eventManager->on('application:boot', function (Event $event, $application) {
        // 终止事件传播，这样其他的侦听器就不会再收到此事件通知
        $event->stopPropagation();
    });

## 测试

    $ cd /path/to/soliphp/events/
    $ composer install
    $ phpunit

## License

MIT Public License


[Phalcon 框架的事件管理器]: https://docs.phalconphp.com/en/latest/events
