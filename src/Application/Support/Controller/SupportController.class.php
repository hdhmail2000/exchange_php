<?php
namespace Support\Controller;

class SupportController extends \Think\Controller
{
	protected function _initialize()
	{
	}
	
	public function __construct() 
	{
		parent::__construct();
		
		$config = (APP_DEBUG ? null : S('home_config'));
		if (!$config) {
			$config = M('Config')->where(array('id' => 1))->find();
			S('home_config', $config);
		}

		C($config);
	}
}

?>