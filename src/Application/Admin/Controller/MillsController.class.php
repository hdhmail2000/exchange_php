<?php

namespace Admin\Controller;

class MillsController extends AdminController{

	public function index($p = 1, $r = 15, $str_addtime = '', $end_addtime = '', $order = '', $status = '', $type = '', $field = '', $name = ''){

		if (empty($order)) {
			$order = 'id_desc';
		}

		$order_arr = explode('_', $order);

		if (count($order_arr) != 2) {
			$order = 'id_desc';
			$order_arr = explode('_', $order);
		}

		$order_set = $order_arr[0] . ' ' . $order_arr[1];

		if (empty($status)) {
			$map['status'] = array('egt', 0);
		}

		if (($status == 1) || ($status == 2) || ($status == 3) || ($status == 4)) {
			$map['status'] = $status - 1;
		}


		$data = M('Mill')->where($map)->order($order_set)->page($p, $r)->select();
		$count = M('Mill')->where($map)->count();
		$parameter['p'] = $p;
		$parameter['status'] = $status;
		$parameter['order'] = $order;
		$parameter['type'] = $type;
		$parameter['name'] = $name;
		$builder = new BuilderList();
		$builder->title('矿机产品');
		$builder->titleList('产品列表', U('Mills/index'));
		$builder->button('add', '添 加', U('Mills/add'));
		$builder->button('delete', '删 除', U('Mills/status', array('model' => 'Mill', 'status' => -1)));
		$builder->setSearchPostUrl(U('Mills/index'));
		$builder->search('order', 'select', array('id_desc' => 'ID降序', 'id_asc' => 'ID升序'));
		$builder->search('status', 'select', array('全部状态', '开放购买', '未开放购买','已售罄'));
		$builder->search('name', 'text', '请输入查询内容');
		$builder->keyId();
		$builder->keyText('name', '矿机名称');
		$builder->keyText('cny_price', '矿机价格');
		$builder->keyText('total', '矿机库存');
		$builder->keyText('num', '已销售');
		$builder->keyText('type', '虚拟币类型');
		$builder->keyText('coin_price', '虚拟币价格');
		$builder->keyText('level', '矿机级别');
		$builder->keyTime('addtime', '添加时间');
		$builder->keyStatus('status', '状态', array('开放购买', '未开放购买','已售罄'));
		$builder->keyDoAction('Mills/add?id=###', '编辑', '操作'); 
		$builder->keyDoAction('Mills/delete?id=###', '删除', '操作');
		foreach ($data as $key => $value) {
		 	$data[$key]['type'] = coinname($value['type']);
		 } 
		$builder->data($data);
		$builder->pagination($count, $r, $parameter);
		$builder->display();
		
	}

	public function add( $id = NULL ){
		if(IS_POST){

			if( empty($_POST['name']) ){
				$this->error('名称不能为空');
			}
			
			if (floatval($_POST['cny_price']) <0 ) {
				$this->error('人民币格式错误');
			}

			if( floatval($_POST['coin_price']) < 0 ){
				$this->error('虚拟币格式错误');
			}

			if( floatval($_POST['fees']) <0 ){
				$this->error('电费格式错误');
			}

			if (!check($_POST['total'], 'd')) {
				$this->error('库存格式错误');
			}

			if( floatval($_POST['profit']) <=0 ){
				$this->error('收益格式错误');
			}
			// 添加时间
			if ($_POST['addtime']) {
				if (addtime(strtotime($_POST['addtime'])) == '---') {
					$this->error('添加时间格式错误');
				}
				else {
					$_POST['addtime'] = strtotime($_POST['addtime']);
				}
			}
			else {
				$_POST['addtime'] = time();
			}
			// 修改时间
			$_POST['modifytime'] = time();

			if (check($_POST['id'], 'd')) {
				$rs = M('Mill')->save($_POST);
			}
			else {
				$rs = M('Mill')->add($_POST);
			}

			if ($rs) {
				$this->success('操作成功');
			}
			else {
				$this->error('操作失败');
			}
		}else{

			$builder = new BuilderEdit();
			$builder->title('矿机管理');
			$builder->titleList('矿机列表', U('Mills/index'));

			if ($id) {
				$builder->keyReadOnly('id', '类型id');
				$builder->keyHidden('id', '类型id');
				$data = M('Mill')->where(array('id' => $id))->find();
				$data['addtime'] = addtime($data['addtime']);
				$data['endtime'] = addtime($data['endtime']);
				$builder->data($data);
			}

			$builder->keyText('name', '矿机名称', '请输入矿机名称');
			$builder->keySelect('level', '矿机级别', '必选', array(1=>'免费矿机（T1）',2=>'T5矿机',3=>'T20矿机',4=>'T50矿机'));
			$builder->keyImage('images', '矿机图片', '矿机图片', array('width' => 408, 'height' => 300, 'savePath' => 'shop', 'url' => U('Shop/images')));
			$builder->keyTextArea('description','矿机描述','简短填写矿机描述');
			$builder->keyText('cny_price', '人民币价格', '保留2位小数');
			// 货币分类
			$coin_list = D('Coin')->get_all_name_list();
			$builder->keySelect('type', '虚拟币支付', '请选择支付的虚拟币类型', $coin_list);
			
			$builder->keyText('coin_price', '虚拟币价格', '请填写虚拟币购买价格');
			$builder->keyText('fees', '矿机电费', '元/度');
			$builder->keyText('maintian', '矿机维护费', '单位(元/天)，免维护费不添');
			$builder->keyText('total', '库存', '台');
			$builder->keyText('profit', '预计收益', '台/天');
			$builder->keyText('limit', '购买限制', '台，0是无限制');
			$builder->keyText('day', '天数', '矿机有效天数，整数');

			$builder->keyAddTime();
			$builder->keyStatus('status','状态','',array('开放购买','未开放购买','已售罄'));
			$builder->savePostUrl(U('Mills/add'));
			$builder->display();
		}
	}

	public function payment($p = 1, $r = 15,  $level = '',$starttime='',$endtime='', $username = ''){
		
		if ($starttime && $endtime) {
			$str_addtime = strtotime($starttime);
			$end_addtime = strtotime($endtime);
			if ((addtime($str_addtime) != '---') && (addtime($end_addtime) != '---')) {
				$map['addtime'] = array(
					array('egt', $str_addtime),
					array('elt', $end_addtime)
					);
			}
		}

		if( $starttime || $endtime ){
			$str_addtime = strtotime($starttime);
			$end_addtime = strtotime($endtime);
			if ( (addtime($str_addtime) != '---') || (addtime($end_addtime) != '---') ) {
				if( $str_addtime ){
					$map['addtime'] = array(
						array('egt', $str_addtime),
					);
				}else{
					$map['addtime'] = array(
						array('elt', $end_addtime)
					);
				}
				
			}
		}

		if( !empty($level) ){
			$map['level'] = $level;
		}

		if( !empty($username) ){
			$map['userid'] = userid($username);
		}

		$data = M('MillLog')->where($map)->order('id desc')->page($p, $r)->select();
		$count = M('MillLog')->where($map)->count();
		

		$parameter['p'] = $p;
		$parameter['level'] = $level;
		$parameter['starttime'] = $starttime;
		$parameter['endtime'] = $endtime;
		$parameter['username'] = $username;
		$builder = new BuilderList();
		$builder->title('交易记录');
		$builder->setSearchPostUrl(U('Mills/payment'));
		$mill_first[0] = '请选择矿机';
		$mill_type = array_merge($mill_first,C('MILL_TYPE'));
		$builder->search('level', 'select', $mill_type);
		$builder->search('starttime', 'time','开始日期');
		$builder->search('endtime', 'time','结束日期');
		$builder->search('username', 'text', '请输入查询内容');
		$builder->keyId();
		$builder->keyText('userid', '用户ID');
		$builder->keyText('username', '用户名');
		$builder->keyText('coinname', '矿机名称');
		$builder->keyText('num', '购买数量');
		$builder->keyText('paytype', '支付方式');
		$builder->keyText('price', '支付总额');
		$builder->keyTime('addtime', '购买时间');
		$builder->keyText('level', '矿机级别');
		$builder->keyStatus('status', '运行状态', array('停止', '运行','作废'));
		$builder->keyDoAction('Mills/deleteMill?id=###', '作废', '操作'); 
		foreach ($data as $key => $value) {
			$data[$key]['username'] = username($value['userid']);
			$data[$key]['paytype']  = coinname($value['paytype']);
		}
		//dump($data);
		$builder->data($data);
		$builder->pagination($count, $r, $parameter);
		$builder->display();
	}


	public function deleteMill($id){
		if( M('MillLog')->where(array('id'=>$id))->save(array('status'=>2)) ){
			$this->success('操作成功');
		}else{
			$this->error('操作失败');
		}
	}

	public function status($id, $status, $model)
	{
		$builder = new BuilderList();
		$builder->doSetStatus($model, $id, $status);
	}

	public function delete($id){
		if( M('Mill')->delete($id) ){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}


	public function fenhong($p = 1, $r = 15, $order = '', $username = ''){

		if (empty($order)) {
			$order = 'id_desc';
		}

		$order_arr = explode('_', $order);

		if (count($order_arr) != 2) {
			$order = 'id_desc';
			$order_arr = explode('_', $order);
		}

		if( $username ){
			$map['username'] = $username;
		}

		$order_set = $order_arr[0] . ' ' . $order_arr[1];


		$data = M('MillFenhong')->where($map)->order($order_set)->page($p, $r)->select();

		$count = M('MillFenhong')->where($map)->count();
		$parameter['p'] = $p;
		$parameter['order'] = $order;
		$parameter['username'] = $username;
		$builder = new BuilderList();
		$builder->title('分红管理');
		$builder->titleList('发送列表', U('Mills/fenhong'));
		$builder->button('add', '发送分红', U('Mills/add_fenhong'));
		$builder->setSearchPostUrl(U('Mills/fenhong'));
		$builder->search('order', 'select', array('id_desc' => 'ID降序', 'id_asc' => 'ID升序'));
		$builder->search('username', 'text', '请输入查询内容');
		$builder->keyId();
		$builder->keyText('username', '用户名');
		$builder->keyText('type', '货币类型');
		$builder->keyText('money', '货币金额');
		$builder->keyTime('addtime', '发送时间');
		foreach ($data as $key => $value) {
		 	$data[$key]['type'] = coinname($value['type']);
		} 
		$builder->data($data);
		$builder->pagination($count, $r, $parameter);
		$builder->display();
		
	}


	public function add_fenhong( $id = NULL ){
		if(IS_POST){
			// 货币分类
			$coin_list = D('Coin')->get_all_name_list();
			
			if( empty($_POST['username']) ){
				$this->error('用户名不能为空');
			}

			$mutil_user = false;
			// 多用户判断
			if( strpos($_POST['username'], ',') !== false ){
				$username = explode(',', $_POST['username']);
				$mutil_user = true;
			} 

			if( !$mutil_user ){
				$userid = M('User')->where(array('username'=>trim($_POST['username'])))->field('id')->find();
	
				if( !$userid['id'] ){
					$this->error('用户不存在');
				}
			}
			

			
			// 添加时间
			if ($_POST['addtime']) {
				if (addtime(strtotime($_POST['addtime'])) == '---') {
					$this->error('添加时间格式错误');
				}
				else {
					$_POST['addtime'] = strtotime($_POST['addtime']);
				}
			}
			else {
				$_POST['addtime'] = time();
			}
			

			if( empty($_POST['type']) ){
				$this->error('货币类型错误');
			} 

			if( !$mutil_user ){
				$result = M('userCoin')->where(array('userid'=>$userid['id']))->setInc($_POST['type'],$_POST['money']);

				if( !$result ){
					//dump(M('userCoin')->getLastSql());
					$this->error('发送分红失败');
				}else{
					M('MillFenhong')->add(
							array(
								'userid'   => $userid['id'],
								'money'    => $_POST['money'],
								'type' 	   => $_POST['type'],
								'addtime'  => $_POST['addtime'],
								'username' => $_POST['username'],
								'content'  => $_POST['content'],
								'coinname' => $coin_list[$_POST['type']]
							)
						);
					$this->success('发送分红成功');
				}
			}else{
				// 多用户
				$num = 0;
				foreach ($username as  $value) {
					$userid = M('User')->where(array('username'=>trim($value)))->field('id')->find();
					if( $userid ){
						$result = M('userCoin')->where(array('userid'=>$userid['id']))->setInc($_POST['type'],$_POST['money']);

						if( $result ){
							M('MillFenhong')->add(
								array(
									'userid'   => $userid['id'],
									'money'    => $_POST['money'],
									'type' 	   => $_POST['type'],
									'addtime'  => $_POST['addtime'],
									'username' => $value,
									'content'  => $_POST['content'],
									'coinname' => $coin_list[$_POST['type']]
								)
							);
							$num++;	
						}
					}
				}

				$this->success('多用户发送分红成功，共发送'.$num.'个用户！！');
			}
			

			
		}else{

			$builder = new BuilderEdit();
			$builder->title('发布分红');
			$builder->titleList('分红管理', U('Mills/fenhong'));

			$builder->keyText('username', '用户名', '请输入用户名，如果多个用户用英文逗号分割');
	
			// 货币分类
			$coin_list = D('Coin')->get_all_name_list();
			$builder->keySelect('type', '货币类型', '请选择支付的虚拟币类型', $coin_list);
			
			$builder->keyText('money', '发送金额', '填写发送的总额');
			$builder->keyText('content', '分红说明', '填写分红的说明');

			$builder->keyAddTime();

			$builder->savePostUrl(U('Mills/add_fenhong'));
			$builder->display();
		}
	}


	public function setting(){
		if( IS_POST ){
			$data['config'] = serialize($_POST);
			
			if( M('millConfig')->where(array('id'=>1))->save($data) ){
				$this->success('保存成功');
			}else{
				$this->error('保存失败');
			}
		}else{

			$builder = new BuilderEdit();
			$builder->title('分销设置');
			$builder->titleList('分销记录','fenxiao');
			$mill_type = C('MILL_TYPE');

			unset($mill_type[1]);
			// 货币分类
			$coin_list = D('Coin')->get_all_name_list();
			$builder->keySelect('type', '发放货币类型', '请选择支付的虚拟币类型', $coin_list);
			
			foreach ($mill_type as $key => $value) {

				$builder->keyText('mill_'.$key, $value, '%，请使用英文逗号 , 来分割五级分销的利率');
			}

			//$builder->keyText('mill_'.$key, $value, '%，请使用英文逗号 , 来分割五级分销的利率');
			$config = M('millConfig')->where(array('id' => 1))->find();
			$data  = unserialize($config['config']);
			$builder->data($data);

			$builder->savePostUrl(U('Mills/setting'));
			$builder->display();
		}
	}


	public function fenxiao($p = 1, $r = 15, $starttime='',$endtime='', $username = ''){
		
		if ($starttime && $endtime) {
			$str_addtime = strtotime($starttime);
			$end_addtime = strtotime($endtime);
			if ((addtime($str_addtime) != '---') && (addtime($end_addtime) != '---')) {
				$map['addtime'] = array(
					array('egt', $str_addtime),
					array('elt', $end_addtime)
					);
			}
		}

		if( $starttime || $endtime ){
			$str_addtime = strtotime($starttime);
			$end_addtime = strtotime($endtime);
			if ( (addtime($str_addtime) != '---') || (addtime($end_addtime) != '---') ) {
				if( $str_addtime ){
					$map['addtime'] = array(
						array('egt', $str_addtime),
					);
				}else{
					$map['addtime'] = array(
						array('elt', $end_addtime)
					);
				}
				
			}
		}



		if( !empty($username) ){
			$map['userid'] = userid($username);
		}

		$data = M('MillFenxiao')->where($map)->order('id desc')->page($p, $r)->select();
		$count = M('MillFenxiao')->where($map)->count();
		

		$parameter['p'] = $p;
		$parameter['level'] = $level;
		$parameter['starttime'] = $starttime;
		$parameter['endtime'] = $endtime;
		$parameter['username'] = $username;
		$builder = new BuilderList();
		$builder->title('分销记录');
		$builder->titleList('分销设置',U('Mills/setting'));
		$builder->setSearchPostUrl(U('Mills/fenxiao'));
		$builder->search('starttime', 'time','开始日期');
		$builder->search('endtime', 'time','结束日期');
		$builder->search('username', 'text', '分销商名称');
		$builder->keyId();
		$builder->keyText('userid', '用户ID');
		$builder->keyText('fenuser', '分销用户');
		$builder->keyText('username', '购买矿机用户');
		$builder->keyText('coinname', '矿机名称');
		$builder->keyText('level', '分销级别');
		$builder->keyText('type', '支付方式');
		$builder->keyText('money', '获得金额');
		$builder->keyTime('addtime', '购买时间');
		foreach ($data as $key => $value) {
			$data[$key]['fenuser'] = username($value['userid']);
			$data[$key]['type']  = coinname($value['type']);
		}
		//dump($data);
		$builder->data($data);
		$builder->pagination($count, $r, $parameter);
		$builder->display();
	}

}
        
        
        
        