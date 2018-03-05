<?php
/**
 * Created by PhpStorm.
 * User: qinweige
 * Date: 2018/3/1
 * Time: 9:55
 *
 */
/*
	1、加入分组（一个连接可以加入多个分组）
	$socket->join('group name');

	2、离开分组（连接断开时会自动从分组中离开）
	$socket->leave('group name');

	1、向当前客户端发送事件
	$socket->emit('event name', $data);

	2、向所有客户端发送事件
	$io->emit('event name', $data);

	3、向所有客户端发送事件，但不包括当前连接。
	$socket->broadcast->emit('event name', $data);

	4、向某个分组的所有客户端发送事件
	$io->to('group name')->emit('event name', $data);

	# 获取客户端ip
	$socket->conn->remoteAddress

	# 关闭连接
	$socket->disconnect();
*/
error_reporting(E_ERROR);
require_once __DIR__ . '/conf.php';
require_once __DIR__ . '/vendor/autoload.php';

use Workerman\Worker;
use PHPSocketIO\SocketIO;

/*
 * 每个user结构
{
    "limit": 10, // 登录限制数
    "ipList": [  // 允许的ip列表
        "192.168.20.55",
        "192.168.20.56",
        "192.168.20.57"
    ],
    "sessionList": { // 当前登录数
        "session_1": 2, // 当前session下连接数(打开的页面数)
        "session_2": 1,
        "session_3": 1
    },
    "connList": {
        "conn_id1": {
            "ip": "192.168.20.55:51885",
            "time": 1519895820,
            "sid": "session_1"
        },
        "conn_id2": {
            "ip": "192.168.20.55:51885",
            "time": 1519895820,
            "sid": "session_1"
        },
        "conn_id3": {
            "ip": "192.168.20.55:51885",
            "time": 1519895820,
            "sid": "session_2"
        },
        "conn_id4": {
            "ip": "192.168.20.55:51885",
            "time": 1519895820,
            "sid": "session_3"
        }
    }
}
 */
// 用户列表, 未考虑并发加锁
$users = [];
if (!$memHost || !$memPort) {
	die("memcache 连接配置不正确\n");
}
$memcache = new Memcache;
$memcache->connect($memHost, $memPort) or die ("无法连接memcache\n");

// 创建socket.io服务端，监听$wsPort端口
$io = new SocketIO($wsPort);
// 当有客户端连接时
$io->on('connection', function ($socket) use ($io, $memcache, &$users) {
	$id = $socket->id;
	$address = $socket->conn->remoteAddress;
	$ip = explode(':', $address);
	echo "connected: id: {$id}, {$address}, online users:" . count($users) . "\n";

	$socket->on('user connect', function ($uid, $sessionId) use ($io, $socket, &$memcache, &$users, $ip) {
		// 应该异步, 这里为了简化用数组同步模拟memcache
		$socket->uid = $uid;
		$socket->sessionId = $sessionId;
		$memKey = 'mem_' . $uid;
		$user = $memcache->get($memKey);
		if (!$user) {
			$user = [
				'limit'       => 10,
				'ipList'      => [
					'192.168.20.55', // 本机内网ip
					'192.168.20.56',
					'192.168.20.57',
					'116.247.98.179', // 公司外网ip
				],
				'sessionList' => [],
				'connList'    => []
			];
		}
		$memcache->set($memKey, $user);
		if (!in_array($ip[0], $user['ipList'])) {
			// ip不在白名单, 禁止登录, 这里可以通知前端并断开连接
			echo $ip[0] . " not in ipList\n";
			$socket->disconnect();
			return;
		}
		if (count($user['sessionList']) >= $user['limit']) {
			// 数量已满, 禁止登录, 这里可以通知前端并断开连接
			echo "limit\n";
			return;
		}
		if (!isset($user['sessionList'][$sessionId])) {
			$user['sessionList'][$sessionId] = 0;
		}
		$user['sessionList'][$sessionId] += 1;
		$user['connList'][$socket->id] = [
			'ip'   => $ip,
			'time' => time(),
			'sid'  => $sessionId
		];
		$memcache->set($memKey, $user);
		$socket->emit('user connected', 'welcome');
		$io->to('admin')->emit('admin info', '连接: ' . json_encode($users));
	});
	$socket->on('admin connect', function () use ($io, $socket, $users) {
		$socket->join('admin');
		$io->to('admin')->emit('admin info', json_encode($users));
	});
	// 断开连接尚未离开room
	$socket->on('disconnecting', function ($reason) use ($io, $id, $address) {
		echo "disconnecting: id: {$id}, {$address}\n";
	});
	// 已断开连接
	$socket->on('disconnect', function ($reason) use ($io, $socket, &$memcache, $ip, &$users) {
		$connId = $socket->id;
		$address = $socket->conn->remoteAddress;
		$uid = $socket->uid;
		$sessionId = $socket->sessionId;
		echo "disconnected: id: {$connId}, {$address},{$uid},{$sessionId} online:\n";
		$io->to('admin')->emit('admin info', '断开: ' . json_encode($users));
		$memKey = 'mem_' . $uid;
		$user = $memcache->get($memKey);
		if (!$user) {
			// 无记录, 不操作
			echo "no record\n";
			return;
		}
		if (isset($user['connList'][$connId])) {
			if (isset($user['sessionList'][$sessionId])) {
				$user['sessionList'][$sessionId] -= 1;
				if ($user['sessionList'][$sessionId] <= 0) {
					unset($user['sessionList'][$sessionId]);
				}
			}
			unset($user['connList'][$connId]);
		}
		$memcache->set($memKey, $user);
	});
	// 连接出错
	$socket->on('error', function ($error) use ($io) {
		echo "error\n";
	});
});

Worker::runAll();



