<?php
namespace Mobile\Controller;

class AjaxController extends MobileController
{
	protected function _initialize()
	{
		parent::_initialize();
		$allow_action=array("getJsonMenu","allfinance","allsum","allcoin","trends","getJsonTop","getTradelog","getDepth","getEntrustAndUsercoin","getChat","upChat","upcomment","subcomment","getJsonMobile","top_coin_menu","allcoin_a");
		if(!in_array(ACTION_NAME,$allow_action)){
			$this->error(L("非法操作！"));
		}
	}

	public function getJsonMenu($ajax = 'json')
	{
		$data = (APP_DEBUG ? null : S('getJsonMenu'));

		if (!$data) {
			foreach (C('market') as $k => $v) {
				$v['xnb'] = explode('_', $v['name'])[0];
				$v['rmb'] = explode('_', $v['name'])[1];
				$data[$k]['name'] = $v['name'];
				$data[$k]['img'] = $v['xnbimg'];
				$data[$k]['title'] = $v['title'];
			}

			S('getJsonMenu', $data);
		}

		if ($ajax) {
			exit(json_encode($data));
		} else {
			return $data;
		}
	}
	
	/** 自定义分区查询  改.HAOMA20181030 **/
	public function allcoin_a($id=1,$ajax = 'json')
	{
		//$data = (APP_DEBUG ? null : S('trandata_allcoin'));
		$trandata_data = array();

		$trandata_data['info'] = L("数据异常");
		$trandata_data['status'] = 0;
		$trandata_data['url'] = "";

        // 市场交易记录
        $marketLogs = array();
        foreach (C('market') as $k => $v) {

			$_tmp = null;
            if (!empty($_tmp)) {
                $marketLogs[$k] = $_tmp;
            } else {
                $tradeLog = M('TradeLog')->where(array('status' => 1, 'market' => $k))->order('id desc')->limit(50)->select();
                $_data = array();
                foreach ($tradeLog as $_k => $v) {
                    $_data['tradelog'][$_k]['addtime'] = date('m-d H:i:s', $v['addtime']);
                    $_data['tradelog'][$_k]['type'] = $v['type'];
                    $_data['tradelog'][$_k]['price'] = $v['price'] * 1;
                    $_data['tradelog'][$_k]['num'] = round($v['num'], 6);
                    $_data['tradelog'][$_k]['mum'] = round($v['mum'], 2);
                }
                $marketLogs[$k] = $_data;
                S('getTradelog' . $k, $_data);
            }
        }

		$volume_24h = array();
		$tradeAmount_24h = array();	
        if ($marketLogs) {
            foreach (C('market') as $k => $v) {
				$_tradeLogs['num'] = M('TradeLog')->where(array(
					'status' => 1,
					'market' => $k,
					'addtime' => array('gt', time() - (60 * 60 * 24))
				))->sum('num');
				
				$_tradeLogs['mum'] = M('TradeLog')->where(array(
					'status' => 1,
					'market' => $k,
					'addtime' => array('gt', time() - (60 * 60 * 24))
				))->sum('mum');
				
                if ($_tradeLogs) {
					$volume_24h[$k] = round($_tradeLogs['num'], 4); // 24小时 交易量
                    $tradeAmount_24h[$k] = round($_tradeLogs['mum'], 4); // 24小时 交易额
                }
            }
        }

		if (!$data) {
			$trandata_data['info']=L("数据正常");
			$trandata_data['status']=1;
			$trandata_data['url']="";
			
			foreach (C('market') as $k => $v) {
				if ($v['jiaoyiqu'] == $id) {
					$xnb = strtoupper(explode('_', $v['name'])[0]);
					$market = strtoupper(explode('_', $v['name'])[1]);
					
					//币种简称
					$trandata_data['url'][$k][0] = $xnb;
					//币种市场
					$trandata_data['url'][$k][1] = $market;
					//最新成交价
					$trandata_data['url'][$k][2] = round($v['new_price'], $v['round']);
					//买一价
					$trandata_data['url'][$k][3] = round($v['buy_price'], $v['round']);
					//卖一价
					$trandata_data['url'][$k][4] = round($v['sell_price'], $v['round']);
					//交易额
					$trandata_data['url'][$k][5] = isset($tradeAmount_24h[$k]) ? $tradeAmount_24h[$k] : 0;//round($v['volume'] * $v['new_price'], 2) * 1;
					
					$trandata_data['url'][$k][6] = '';
					
					//交易量
					$trandata_data['url'][$k][7] = isset($volume_24h[$k]) ? $volume_24h[$k] : 0;//round($v['volume'], 4) * 1;
					
					//涨跌幅
					$trandata_data['url'][$k][8] = round($v['change'], 2);
					//链接专用
					$trandata_data['url'][$k][9] = $v['name'];
					//图图标地址
					$trandata_data['url'][$k][10] = $v['xnbimg'];
					//最高价
					$trandata_data['url'][$k][11] = round($v['max_price'], $v['round']);
					//最低价
					$trandata_data['url'][$k][12] = round($v['min_price'], $v['round']);
					
					
					$rmbs = 0;
					$market = explode('_', $v['name'])[1];
					if ($market==Anchor_CNY) { //锚定法币
						$rmbs =  bcdiv($v['new_price'] * C('MYCOIN'),1,$v['round']) * 1;
					}
					if ($market=='btc') {
						//$rmbs = round($v['info']['new_price'] * C('BTC'),2);
						$rmbs = bcdiv($v['new_price'] * C('market')['btc_'.Anchor_CNY]['new_price'],1,$v['round']) * 1;
					}
					if ($market=='eth') {
						//$rmbs = NumToStr(round($v['info']['new_price'] * C('market')['eth_'.Anchor_CNY]['new_price']),6);
						$rmbs = bcdiv($v['new_price'] * C('market')['eth_'.Anchor_CNY]['new_price'],1,$v['round']) * 1;
					}
					if ($market=='usdt') {
						$rmbs = bcdiv($v['new_price'] * C('market')['usdt_'.Anchor_CNY]['new_price'],1,$v['round']) * 1;
					}
					if ($market=='mob') {
						$rmbs = bcdiv($v['new_price'] * C('market')['mob_'.Anchor_CNY]['new_price'],1,$v['round']) * 1;
					}
					
					$trandata_data['url'][$k][14] = $rmbs;
				}
			}
		}

		if ($ajax) {
			echo json_encode($trandata_data);
			unset($trandata_data);
			exit();
		} else {
			return $trandata_data;
		}
	}
	
	public function index_b_trends($ajax = 'json')
	{
		$data = (APP_DEBUG ? null : S('trends'));
		if (!$data) {
			foreach (C('market') as $k => $v) {
				$tendency = json_decode($v['tendency'], true);
				$data[$k]['data'] = $tendency;
				$data[$k]['yprice'] = $v['new_price'];
			}
			S('trends', $data);
		}

		if ($ajax) {
			exit(json_encode($data));
		} else {
			return $data;
		}
	}

	public function allfinance($ajax = 'json')
	{
		if (!userid()) {
			return false;
		}

		$UserCoin = M('UserCoin')->where(array('userid' => userid()))->find();
		$cny['zj'] = 0;

		foreach (C('coin') as $k => $v) {
			if ($v['name'] == 'cny') {
				$cny['ky'] = $UserCoin[$v['name']] * 1;
				$cny['dj'] = $UserCoin[$v['name'] . 'd'] * 1;
				$cny['zj'] = $cny['zj'] + $cny['ky'] + $cny['dj'];
			} else {
				if (C('market')[$v['name'] . '_cny']['new_price']) {
					$jia = C('market')[$v['name'] . '_cny']['new_price'];
				} else {
					$jia = 1;
				}

				$cny['zj'] = round($cny['zj'] + (($UserCoin[$v['name']] + $UserCoin[$v['name'] . 'd']) * $jia), 2) * 1;
			}
		}

		$data = round($cny['zj'], 8);
		// $data = NumToStr($data);
		$data = number_format($data,2);//千分位显示
		if ($ajax) {
			exit(json_encode($data));
		} else {
			return $data;
		}
	}

	public function allsum($ajax = 'json')
	{
		$data = (APP_DEBUG ? null : S('allsum'));
		if (!$data) {
			$data = M('TradeLog')->sum('mum');
			S('allsum', $data);
		}

		$data = round($data);
		$data = str_repeat('0', 12 - strlen($data)) . (string) $data;
		if ($ajax) {
			exit(json_encode($data));
		} else {
			return $data;
		}
	}

	public function allcoin($ajax = 'json')
	{
		$data = (APP_DEBUG ? null : S('allcoin'));
		if (!$data) {
			foreach (C('market') as $k => $v) {
				$data[$k][0] = $v['title'];
				$data[$k][1] = round($v['new_price'], $v['round']);
				$data[$k][2] = round($v['buy_price'], $v['round']);
				$data[$k][3] = round($v['sell_price'], $v['round']);
				$data[$k][4] = round($v['volume'] * $v['new_price'], 2) * 1;
				$data[$k][5] = '';
				$data[$k][6] = round($v['volume'], 2) * 1;
				$data[$k][7] = round($v['change'], 2);
				$data[$k][8] = $v['name'];
				$data[$k][9] = $v['xnbimg'];
				$data[$k][10] = '';
			}

			S('allcoin', $data);
		}

		if ($ajax) {
			exit(json_encode($data));
		} else {
			return $data;
		}
	}

	public function trends($ajax = 'json')
	{
		$data = (APP_DEBUG ? null : S('trends'));
		if (!$data) {
			foreach (C('market') as $k => $v) {
				$tendency = json_decode($v['tendency'], true);
				$data[$k]['data'] = $tendency;
				$data[$k]['yprice'] = $v['new_price'];
			}

			S('trends', $data);
		}

		if ($ajax) {
			exit(json_encode($data));
		} else {
			return $data;
		}
	}
	
	public function top_coin_menu($ajax = 'json')
	{
		$data = (APP_DEBUG ? null : S('trandata_getTopCoinMenu'));

		$trandata_getCoreConfig = getCoreConfig();
		if(!$trandata_getCoreConfig){
			$this->error('核心配置有误');
		}
		if (!$data) {
			$data = array();

			foreach($trandata_getCoreConfig['indexcat'] as $k=>$v){
				$data[$k][title] = $v;
			}

			foreach (C('market') as $k => $v) {

 				$v['xnb'] = explode('_', $v['name'])[0];
				$v['rmb'] = explode('_', $v['name'])[1];

				$data_tmp['img'] = $v['xnbimg'];
				$data_tmp['title'] = $v['navtitle'];

				$data[$v['jiaoyiqu']]['data'][$k] = $data_tmp;

				unset($data_tmp);
			}

			S('trandata_getTopCoinMenu', $data);
		}

		if ($ajax) {
			exit(json_encode($data));
		} else {
			return $data;
		}
	}

/*	public function getJsonTop($market = NULL, $ajax = 'json')
	{
		// 过滤非法字符----------------S
		if (checkstr($market)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E
		$data = (APP_DEBUG ? null : S('getJsonTop' . $market));
		// var_dump( S('getJsonTop' . $market));die;
		if (!$data) {
			if ($market) {
				$xnb = explode('_', $market)[0];
				$rmb = explode('_', $market)[1];

				foreach (C('market') as $k => $v) {
					$v['xnb'] = explode('_', $v['name'])[0];
					$v['rmb'] = explode('_', $v['name'])[1];
					$data['list'][$k]['name'] = $v['name'];
					$data['list'][$k]['img'] = $v['xnbimg'];
					$data['list'][$k]['title'] = $v['title'];
					$data['list'][$k]['new_price'] = $v['new_price'];
				}

				$data['info']['img'] = C('market')[$market]['xnbimg'];
				$data['info']['title'] = C('market')[$market]['title'];
				$data['info']['new_price'] = C('market')[$market]['new_price'];
				$data['info']['max_price'] = C('market')[$market]['max_price'];
				$data['info']['min_price'] = C('market')[$market]['min_price'];
				$data['info']['buy_price'] = C('market')[$market]['buy_price'];
				$data['info']['sell_price'] = C('market')[$market]['sell_price'];
				$data['info']['volume'] = C('market')[$market]['volume'];
				$data['info']['change'] = C('market')[$market]['change'];
				
				
				//以下是本地测试,或者其他使用
				if($market['jiaoyiqu']==0){//交易区0为usdt
					$data['info']['rmb'] =  round(C('market')[$market]['new_price']*C('usdt'),2);
				}
				if($market['jiaoyiqu']==1){//交易区1为USDT
					$data['info']['rmb'] =  round(C('market')[$market]['new_price']*C('btc'),2);
				}
				if($market['jiaoyiqu']==2){//交易区2为ETH
					$data['info']['rmb'] =  round(C('market')[$market]['new_price']*C('eth'),2);
				}
				if($market['jiaoyiqu']==3){//交易区0为自己的币种
					$data['info']['rmb'] =  round(C('market')[$market]['new_price']*C('mycoin'),2);
				}
				S('getJsonTop' . $market, $data);
			}
		}

		if ($ajax) {
			exit(json_encode($data));
		} else {
			return $data;
		}
	}*/
	
	// 交易中心调用
	public function getJsonTop($market = NULL, $ajax = 'json')
	{
		// 过滤非法字符----------------S
		if (checkstr($market)) {
			$this->error('您输入的信息有误！');
		}
		// 过滤非法字符----------------E
		
		$data = (APP_DEBUG ? null : S("getJsonTop" . $market));
		if (!$data) {
			if ($market) {
				$xnb = explode("_", $market)[0];
				$rmb = explode("_", $market)[1];
				
				// 24小时 交易量
				$volume_24h = 0;
				$volume_24h = M('TradeLog')->where(array(
					'market'  => $market,
					'addtime' => array('gt', time() - (60 * 60 * 24))
				))->sum('num');
				$volume_24h = round($volume_24h, 4);

/*				foreach (C("market") as $k => $v) {
					$v["xnb"] = explode("_", $v["name"])[0];
					$v["rmb"] = explode("_", $v["name"])[1];
					$data["list"][$k]["name"] = $v["name"];
					$data["list"][$k]["img"] = $v["xnbimg"];
					$data["list"][$k]["title"] = $v["title"];
					$data["list"][$k]["new_price"] = $v["new_price"];
					$data["list"][$k]["change"] = $v["change"];
					$data["list"][$k]['coin_name'] = strtoupper($v["xnb"]);
				}*/
				
				$data["info"]["img"] = C("market")[$market]["xnbimg"];
				$data["info"]["title"] = C("market")[$market]["title"];
				$data["info"]["new_price"] = C("market")[$market]["new_price"];
				$data["info"]["max_price"] = C("market")[$market]["max_price"];
				$data["info"]["min_price"] = C("market")[$market]["min_price"];
				$data["info"]["buy_price"] = C("market")[$market]["buy_price"];
				$data["info"]["sell_price"] = C("market")[$market]["sell_price"];
				$data["info"]["volume"] = isset($volume_24h) ? $volume_24h : 0;//C("market")[$market]["volume"];
				$data["info"]["change"] = C("market")[$market]["change"];
				
				S("getJsonTop" . $market, $data);
			}
		}

		if ($ajax) {
			exit(json_encode($data));
		} else {
			return $data;
		}
	}

	public function getJsonMobile($market = NULL, $ajax = 'json')
	{
		// $data = (APP_DEBUG ? null : S('getJsonMobile' . $market));
		// var_dump( S('getJsonMobile' . $market));die;
        // var_dump(C('market'));die;
		foreach (C('market') as $k => $v) {
			$v['xnb'] = explode('_', $v['name'])[0];
			$v['rmb'] = explode('_', $v['name'])[1];
			// $data[$k] = $k;
			$data[$k]['name'] = $v['xnb'];
			// $data[$k]['name'] = $v['name'];
			// $data[$k]['img'] = $v['xnbimg'];
			// $data[$k]['title'] = $v['title'];
			$data[$k]['new_price'] = $v['new_price'];
			$data[$k]['max_price'] = $v['max_price'];
			$data[$k]['min_price'] = $v['min_price'];
			$data[$k]['buy_price'] = $v['buy_price'];
			$data[$k]['sell_price'] = $v['sell_price'];
			$data[$k]['volume'] =  round( $v['volume'],2);
			$data[$k]['change'] = $v['change'];
			$data[$k]['cje'] =round($v['volume'] * $v['new_price'], 2);

			if ($data[$k]['volume'] > 10000 && $data[$k]['volume'] < 100000000) {
				$data[$k]['cjl'] = (intval($data[$k]['volume'] / 10000*100)/100) . L("万");
			}
			if ($data[$k]['volume'] > 100000000) {
				$data[$k]['cjl'] = (intval($data[$k]['volume'] / 100000000*100)/100) . L("亿");
			}
			if ($data[$k]['cje'] > 10000 && $data[$k]['cje'] < 100000000) {
				$data[$k]['cje']= (intval($data[$k]['cje'] / 10000*100)/100) . L("万");
			}
			if ($data[$k]['cje'] > 100000000) {
				$data[$k]['cje'] = (intval($data[$k]['cje'] / 100000000*100)/100) . L("亿");
			}
		}
		// var_dump($data);die;
		// $data['info']['img'] = C('market')[$market]['xnbimg'];
		// $data['info']['title'] = C('market')[$market]['title'];
		// $data['info']['new_price'] = C('market')[$market]['new_price'];
		// $data['info']['max_price'] = C('market')[$market]['max_price'];
		// $data['info']['min_price'] = C('market')[$market]['min_price'];
		// $data['info']['buy_price'] = C('market')[$market]['buy_price'];
		// $data['info']['sell_price'] = C('market')[$market]['sell_price'];
		// $data['info']['volume'] = C('market')[$market]['volume'];
		// $data['info']['change'] = C('market')[$market]['change'];
		S('getJsonMobile' , $data);
		 // var_dump($data);die;
		if ($ajax) {
			exit(json_encode($data));
		} else {
			// var_dump($data);
			return $data;
		}
	}

	public function getTradelog($market = NULL, $ajax = 'json')
	{
		// 过滤非法字符----------------S
		if (checkstr($market)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		$data = (APP_DEBUG ? null : S('getTradelog' . $market));
		if (!$data) {
			$tradeLog = M('TradeLog')->where(array('status' => 1, 'market' => $market))->order('id desc')->limit(20)->select();

			if ($tradeLog) {
				foreach ($tradeLog as $k => $v) {
					$data['tradelog'][$k]['addtime'] = date('m-d H:i:s', $v['addtime']);
					$data['tradelog'][$k]['type'] = $v['type'];
					$data['tradelog'][$k]['price'] = $v['price'] * 1;
					$data['tradelog'][$k]['num'] = round($v['num'], 6);
					$data['tradelog'][$k]['mum'] = round($v['mum'], 6);
				}

				S('getTradelog' . $market, $data);
			}
		}

		if ($ajax) {
			exit(json_encode($data));
		} else {
			return $data;
		}
	}

	public function getDepth($market = NULL, $trade_moshi = 1,$limts = 5, $ajax = 'json')
	{
		// 过滤非法字符----------------S
		if (checkstr($market) || checkstr($trade_moshi) || checkstr($limts)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!C('market')[$market]) {
			return null;
		}

		$data_getDepth = (APP_DEBUG ? null : S('getDepth'));
		if (!$data_getDepth[$market][$trade_moshi]) {
			$limt = $limts;
			
			$mo = M();
			if ($trade_moshi == 99) {
				$buy = $mo->query('select id,price,sum(num-deal)as nums from tw_trade where status=0 and type=1 and market =\'' . $market . '\' group by price order by price desc limit ' . $limt . ';');
				$sell = $mo->query('select id,price,sum(num-deal)as nums from tw_trade where status=0 and type=2 and market =\'' . $market . '\' group by price order by price asc limit ' . $limt . ';');
			}
			if ($trade_moshi == 1) {
				$buy = $mo->query('select id,price,sum(num-deal)as nums from tw_trade where status=0 and type=1 and market =\'' . $market . '\' group by price order by price desc limit ' . $limt . ';');
				$sell = array_reverse($mo->query('select id,price,sum(num-deal)as nums from tw_trade where status=0 and type=2 and market =\'' . $market . '\' group by price order by price asc limit ' . $limt . ';'));
			}
			if ($trade_moshi == 3) {
				$buy = $mo->query('select id,price,sum(num-deal)as nums from tw_trade where status=0 and type=1 and market =\'' . $market . '\' group by price order by price desc limit ' . $limt . ';');
				$sell = null;
			}
			if ($trade_moshi == 4) {
				$buy = null;
				$sell = array_reverse($mo->query('select id,price,sum(num-deal)as nums from tw_trade where status=0 and type=2 and market =\'' . $market . '\' group by price order by price asc limit ' . $limt . ';'));
			}

			if ($buy) {
				$maxNums = maxArrayKey($buy, 'nums') / 2;
				foreach ($buy as $k => $v) {
					$data['depth']['buy'][$k] = array(floatval($v['price'] * 1), floatval($v['nums'] * 1));
					$data['depth']['buypbar'][$k] = ((($maxNums < $v['nums'] ? $maxNums : $v['nums']) / $maxNums) * 100);
				}
			} else {
				$data['depth']['buy'] = '';
				$data['depth']['buypbar'] = '';
			}

			if ($sell) {
				$maxNums = maxArrayKey($sell, 'nums') / 2;
				foreach ($sell as $k => $v) {
					$data['depth']['sell'][$k] = array(floatval($v['price'] * 1), floatval($v['nums'] * 1));
					$data['depth']['sellpbar'][$k] = ((($maxNums < $v['nums'] ? $maxNums : $v['nums']) / $maxNums) * 100);
				}
			} else {
				$data['depth']['sell'] = '';
				$data['depth']['sellpbar'] = '';
			}
			
			//print_r($data);

			$data_getDepth[$market][$trade_moshi] = $data;
			S('getDepth', $data_getDepth);
		} else {
			$data = $data_getDepth[$market][$trade_moshi];
		}
		
		if ($ajax) {
			exit(json_encode($data));
		} else {
			return $data;
		}
	}

	public function getEntrustAndUsercoin($market = NULL, $ajax = 'json')
	{
		// 过滤非法字符----------------S
		if (checkstr($market)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			return null;
		}
		if (!C('market')[$market]) {
			return null;
		}

		$result = M()->query('select id,price,num,deal,mum,type,fee,status,addtime from tw_trade where status=0 and market=\'' . $market . '\' and userid=' . userid() . ' order by id desc limit 10;');
		if ($result) {
			foreach ($result as $k => $v) {
				$data['entrust'][$k]['addtime'] = date('m-d H:i:s', $v['addtime']);
				$data['entrust'][$k]['addtime2'] = date('H:i:s', $v['addtime']);
				$data['entrust'][$k]['type'] = $v['type'];
				$data['entrust'][$k]['price'] = $v['price'] * 1;
				$data['entrust'][$k]['num'] = round($v['num'], 6);
				$data['entrust'][$k]['deal'] = round($v['deal'], 6);
				$data['entrust'][$k]['id'] = round($v['id']);
			}
		} else {
			$data['entrust'] = null;
		}

		$userCoin = M('UserCoin')->where(array('userid' => userid()))->find();
		if ($userCoin) {
			$xnb = explode('_', $market)[0];
			$rmb = explode('_', $market)[1];
			$data['usercoin']['xnb'] = floatval($userCoin[$xnb]);
			$data['usercoin']['xnbd'] = floatval($userCoin[$xnb . 'd']);
			$data['usercoin']['cny'] = floatval($userCoin[$rmb]);
			$data['usercoin']['cnyd'] = floatval($userCoin[$rmb . 'd']);
		} else {
			$data['usercoin'] = null;
		}
		
		// 处理开盘闭盘交易时间===开始
		$times = date('G',time());
		$minute = date('i',time());
		$minute = intval($minute);
		$data['time_state'] = 0;
		if (($times <= C('market')[$market]['start_time'] && $minute< intval(C('market')[$market]['start_minute']))|| ( $times > C('market')[$market]['stop_time'] && $minute>= intval(C('market')[$market]['stop_minute'] ))) {
			$data['time_state'] = 1;
		}
		if (($times <C('market')[$market]['start_time'] )|| $times > C('market')[$market]['stop_time']) {
			$data['time_state'] = 1;
		} else {
			if ($times == C('market')[$market]['start_time']) {
				if ($minute< intval(C('market')[$market]['start_minute'])) {
					$data['time_state'] = 1;
				}
			} elseif ($times == C('market')[$market]['stop_time']) {
				if (($minute > C('market')[$market]['stop_minute'])) {
					$data['time_state'] = 1;
				}
			}
		}
		// 处理周六周日是否可交易===开始
		$weeks = date('N',time());
		if(!C('market')[$market]['agree6']){
			if($weeks == 6){
				$data['time_state'] = 1;
			}
		}
		if(!C('market')[$market]['agree7']){
			if($weeks == 7){
				$data['time_state'] = 1;
			}
		}
		//处理周六周日是否可交易===结束
		if ($ajax) {
			exit(json_encode($data));
		} else {
			return $data;
		}
	}

	public function getChat($ajax = 'json')
	{
		$chat = (APP_DEBUG ? null : S('getChat'));
		if (!$chat) {
			$chat = M('Chat')->where(array('status' => 1))->order('id desc')->limit(500)->select();
			S('getChat', $chat);
		}

		asort($chat);

		if ($chat) {
			foreach ($chat as $k => $v) {
				$data[] = array((int) $v['id'], $v['username'], $v['content']);
			}
		} else {
			$data = '';
		}

		if ($ajax) {
			exit(json_encode($data));
		} else {
			return $data;
		}
	}

	public function upChat($content)
	{
		exit;
		if (!userid()) {
			$this->error(L('请先登录...'));
		}

		$content = msubstr($content, 0, 20, 'utf-8', false);
		if (!$content) {
			$this->error(L('请先输入内容'));
		}

		if (APP_DEMO) {
			$this->error(L('测试站暂时不能聊天！'));
		}

		if (time() < (session('chat' . userid()) + 10)) {
			$this->error(L('不能发送过快'));
		}

		$id = M('Chat')->add(array('userid' => userid(), 'username' => username(), 'content' => $content, 'addtime' => time(), 'status' => 1));
		if ($id) {
			S('getChat', null);
			session('chat' . userid(), time());
			$this->success($id);
		} else {
			$this->error(L('发送失败'));
		}
	}

	public function upcomment($msgaaa, $s1, $s2, $s3, $xnb)
	{
		exit;
		if (empty($msgaaa)) {
			$this->error(L('提交内容错误'));
		}
		if (!check($s1, 'd')) {
			$this->error(L('技术评分错误'));
		}
		if (!check($s2, 'd')) {
			$this->error(L('应用评分错误'));
		}
		if (!check($s3, 'd')) {
			$this->error(L('前景评分错误'));
		}
		if (!userid()) {
			$this->error(L('请先登录！'));
		}

		if (M('CoinComment')->where(array(
			'userid'   => userid(),
			'coinname' => $xnb,
			'addtime'  => array('gt', time() - 60)
			))->find()) {
			$this->error(L('请不要频繁提交！'));
		}

		if (M('Coin')->where(array('name' => $xnb))->save(array(
			'tp_zs' => array('exp', 'tp_zs+1'),
			'tp_js' => array('exp', 'tp_js+' . $s1),
			'tp_yy' => array('exp', 'tp_yy+' . $s2),
			'tp_qj' => array('exp', 'tp_qj+' . $s3)
			))) {
			if (M('CoinComment')->add(array('userid' => userid(), 'coinname' => $xnb, 'content' => $msgaaa, 'addtime' => time(), 'status' => 1))) {
				$this->success(L('提交成功'));
			} else {
				$this->error(L('提交失败！'));
			}
		} else {
			$this->error(L('提交失败！'));
		}
	}

	public function subcomment($id, $type)
	{
		// 过滤非法字符----------------S
		if (checkstr($id) || checkstr($type)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if ($type != 1) {
			if ($type != 2) {
				if ($type != 3) {
					$this->error(L('参数错误！'));
				} else {
					$type = 'xcd';
				}
			} else {
				$type = 'tzy';
			}
		} else {
			$type = 'cjz';
		}

		if (!check($id, 'd')) {
			$this->error(L('参数错误'));
		}

		if (!userid()) {
			$this->error(L('请先登录！'));
		}

		if (S('subcomment' . userid() . $id)) {
			$this->error(L('请不要频繁提交！'));
		}

		if (M('CoinComment')->where(array('id' => $id))->setInc($type, 1)) {
			S('subcomment' . userid() . $id, 1);
			$this->success(L('提交成功'));
		} else {
			$this->error(L('提交失败！'));
		}
	}
}
?>