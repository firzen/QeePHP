<?php
class Controller_Users extends Controller_Abstract{
	function actionRegister(){
		if (request_is_post()){
			do {
				//从POST获取用户名和密码
				$username = post('username');
				$password = post('password','',false);
				
				// 对输入数据进行验证
				$errors = array();
				if (strlen($username) < 5)
				{
					$errors['username'][] = '用户名不能少于 5 个字符';
				}
				// 验证用户名有效性，更多验证方法请参考 QValidator
				if (!QValidator::validate_is_alnum($username)){
					$errors['username'][] = '用户名只能由字母和数字组成';
				}
				if (strlen($password) < 6)
				{
					$errors['password'][] = '密码不能少于 6 个字符';
				}
				//验证图形验证码是否正确
				if (!Helper_ImgCode::isValid(request('imgcode'),true)){
					$errors['imgcode'][]='验证码不正确';
				}
				//验证用户是否已经注册
				$user=User::find('username =?',$username)->getOne();
				if (!$user->isNewRecord()){
					$errors['username'][] = '用户名已经被注册';
				}
				if (count($errors)){
					$this->_view['errors']=$errors;
					break;
				}
				//验证完毕，保存用户
				try {
					$user=new User();
					$user->username=$username;
					$user->password=$password;
					$user->save();
					
					// 成功后输出新建用户对象的信息
					dump($user->username, 'UserName');
					dump($user->password, 'Password');
					dump($user->id(), 'UserID');
					exit;
					
					// 登录成功后，重定向浏览器
					return $this->_redirect(url('default/index'));
				}catch (Exception $ex){
					dump($ex->getMessage());
					exit;
				}
			}while (0);
		}
	}
	function actionImgCode(){
		// 使用验证码生成助手
		return Helper_ImgCode::create(6,900);
	}
	function actionLogin(){
		if (request_is_post()){
			$user=User::validateLogin(request('username'), request('password'));
			if ($user ===false){
				exit('用户名或密码错误');
			}
			// 将登录用户的信息存入 SESSION，以便应用程序记住用户的登录状态
			$this->_app->changeCurrentUser($user->toArray(), 'MEMBER');
			// 登录成功后，重定向浏览器
			return $this->_redirect(url('default/index'));
		}
	}
	function actionLogout()
	{
		// 清除当前用户的登录信息
		$this->_app->cleanCurrentUser();
		// 重定向浏览器
		return $this->_redirect(url('default/index'));
	}
	function actionChangePassword(){
		if (request_is_post()){
			$oldpass=request('oldpass');
			$newpass=request('newpass');
			$newpass_repeat=request('newpass_repeat');
			
			//验证
			do {
				$errors=array();
				//新密码
				if (strlen($newpass)<6){
					$errors['newpass'][]='新密码长度不能小于6位';
				}
				if ($newpass!=$newpass_repeat){
					$errors['newpass'][]='新密码两次输入不一致';
				}
				//旧密码
				//1.获得登录用户
				$user=User::validateLogin($this->_login_user['username'], $oldpass);
				if ($user===false){
					$errors['oldpass'][]='旧密码输入不正确';
				}
				if (count($errors)){
					$this->_view['errors']=$errors;
					break;
				}
				//保存新密码
				$user->changePassword($newpass);
				
				//跳转到首页
				return $this->_redirectMessage('恭喜您', '修改密码成功', url('default/index'));
			}while (0);
		}
	}
}
