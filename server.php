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
require_once __DIR__ . '/conf.php';
require_once __DIR__ . '/vendor/autoload.php';

use Workerman\Worker;
use PHPSocketIO\SocketIO;

$conns = [];

// 创建socket.io服务端，监听$wsPort端口
$io = new SocketIO($wsPort);
// 当有客户端连接时
$io->on('connection', function ($socket) use ($io, $conns) {
	$id = $socket->id;
	$address = $socket->conn->remoteAddress;
	echo "connected: id: {$id}, {$address}\n";
	$socket->send('welcome');
	$socket->emit('group info', json_encode($socket->rooms));

	$socket->on('login', function ($msg) use ($io, $socket, $conns) {
		$socket->emit('login', json_encode($msg));
	});
	// 断开连接尚未离开room
	$socket->on('disconnecting', function ($reason) use ($io, $conns, $id, $address) {
		echo "disconnecting: id: {$id}, {$address}\n";
	});
	// 已断开连接
	$socket->on('disconnect', function ($reason) use ($io, $conns, $id, $address) {
		echo "disconnected: id: {$id}, {$address}\n";
	});
	// 连接出错
	$socket->on('error', function ($error) use ($io) {
		echo "error\n";
	});
});

Worker::runAll();



