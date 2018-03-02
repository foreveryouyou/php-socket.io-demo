<?php
/**
 * Created by PhpStorm.
 * User: qinweige
 * Date: 2018/3/2
 * Time: 16:29
 */

require_once 'POP_OutputJson.php';
define('ERROR_CODE_OK', 0);
define('ERROR_CODE_NG', 1);

$jop = new POP_OutputJson();
$act = isset($_GET['act']) ? $_GET['act'] : '';

$username = isset($_POST['username']) ? $_POST['username'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

$dataFile = 'data.json';
if (!file_exists($dataFile)) {
	file_put_contents($dataFile, '[]');
}
$userList = listUser();

switch ($act) {
	case 'login':
		if (empty($username) || empty($password)) {
			$jop->code(ERROR_CODE_NG)->msg('用户名、密码不能为空')->out();
		}
		if (isset($userList[$username]) && $userList[$username] == $password) {
			$data = array(
				'userId'    => md5($username),
				'sessionId' => uniqid('wsSession')
			);
			$jop->msg('登录成功')->data($data)->out();
		}
		$jop->code(ERROR_CODE_NG)->msg('用户名或密码错误')->out();
		break;
	case 'logout':
		$jop->code(ERROR_CODE_OK)->msg('退出成功')->out();
		break;
	case 'addUser':
		if (empty($username) || empty($password)) {
			$jop->code(ERROR_CODE_NG)->msg('用户名、密码不能为空')->out();
		}
		if (isset($userList[$username])) {
			$jop->code(ERROR_CODE_NG)->msg('用户名已存在')->out();
		}
		$userList[$username] = $password;
		saveUserList($userList);
		$jop->msg('新增成功')->data($userList)->out();
		break;
	case 'delUser':
		unset($userList[$username]);
		saveUserList($userList);
		$jop->msg('删除成功')->data($userList)->out();
		break;
	case 'listUser':
		$jop->data(listUser())->out();
		break;
	default:
		$jop->msg('不存在的操作')->out();
		break;
}

// ****************************
function listUser()
{
	global $dataFile;
	$str = file_get_contents($dataFile);
	$userList = json_decode($str, true);
	return is_array($userList) ? $userList : array();
}

function saveUserList($userList)
{
	global $dataFile;
	file_put_contents($dataFile, json_encode($userList));
	return;
}
