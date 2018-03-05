# phpSocket.io手册
[中文手册(在线)](https://github.com/walkor/phpsocket.io/tree/master/docs/zh)

[中文手册(本地)](./vendor/workerman/phpsocket.io/docs/zh/README.md)

# 在线聊天Demo
[chat demo](http://www.workerman.net/demos/phpsocketio-chat/)

# 命令

## Start
- debug mode
```
$ php server.php start
```
- daemon mode
```
$ php server.php start -d
```

## Stop
```
$ php server.php stop
```

## Status
```
$ php server.php status
```

# 环境

## Linux系统环境检测

Linux系统可以使用以下脚本测试本机PHP环境是否满足WorkerMan运行要求。
```
$ curl -Ss http://www.workerman.net/check.php | php
```

上面脚本如果全部显示ok，则代表满足WorkerMan要求，直接到官网下载例子即可运行。

如果不是全部ok，则参考 [文档](http://doc.workerman.net/315116) 安装缺失的扩展即可。

（注意：检测脚本中没有检测event扩展或者libevent扩展，如果业务并发连接数大于1024建议安装event扩展或者libevent扩展，安装方法参照 [文档说明](http://doc.workerman.net/315116) ）