<?php
namespace Home\Controller;
class GameController extends HomeController
{
	protected function _initialize(){
		parent::_initialize();
		$allow_action=array("index");
		if(!in_array(ACTION_NAME,$allow_action)){
			$this->error("非法操作！");
		}
	}
	
	public function index()
	{
		if (!userid()) {
			redirect('/Login/index');
		}

		$name = M('VersionGame')->where(array(
			'status' => 1,
			'name'   => array('neq', 'shop')
			))->getField('name');

		if ($name) {
			redirect(U(ucwords($name) . '/index'));
		}

		$this->display();
	}
}

?>