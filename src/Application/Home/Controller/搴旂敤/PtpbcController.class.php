<?php
/* 应用 - OTC场外交易 */
namespace Home\Controller;

class PtpbcController extends HomeController
{
    protected function _initialize() {
        parent::_initialize();
        $allow_action = ["index","index2","push","apply","buy","buylist","buyinfo",'buy_action','uppush'];
        if (!userid()) {
            redirect('/Login/index.html');
        }
        if (!in_array(ACTION_NAME, $allow_action)) {
            $this->error("非法操作！");
        }
        if(I('market'))S('market',I('market'));
        if(!S('market'))S('market','USDT');
        $this->assign('market',S('market'));
    }

    public function index()
	{
        // $lists=M('Ptpbc')->where(array('market'=>S('market'),'lang'=>LANG_SET))->select();
        $lists=M('Ptpbc')->where(array('market'=>S('market')))->select();
        foreach ($lists as $k=>$v){
            $lists[$k]['quota']=str_replace(',','-',$v['quota']);
        }
        $data = ['merchants' => $lists,];
        $this->assign('coin',S('market'));
        $this->assign('data', $data);
        $this->display();
    }
	
	public function index2() 
	{
        $lists=M('Ptpbc')->where(array('market'=>S('market')))->select();
        // $lists=M('Ptpbc')->where(array('market'=>S('market'),'lang'=>LANG_SET))->select();
        foreach ($lists as $k=>$v){
            $lists[$k]['quota']=str_replace(',','-',$v['quota']);
        }
        $data = ['merchants' => $lists,];
        $this->assign('data', $data);
        $this->display();
    }
	
    public function push()
	{
        $coin = 'btc';
        $Coins = M('Coin')->where(array('name' => $coin))->find();
        $myzc_min = ($Coins['zc_min'] ? abs($Coins['zc_min']) : 1);
        $myzc_max = ($Coins['zc_max'] ? abs($Coins['zc_max']) : 10000000);
        $this->assign('myzc_min', $myzc_min);
        $this->assign('myzc_max', $myzc_max);
        $this->assign('xnb', $coin);
        $Coins = M('Coin')->where(array(
            'status' => 1,
            'name'   => array('in', array('btc','usdt'))
            ))->select();

        foreach ($Coins as $k => $v) {
            $coin_list[$v['name']] = $v;
        }
        $btc=C('btc');
        $usdt=C('usdt');
        $this->assign('btc',$btc);
        $this->assign('usdt',$usdt);
        $this->assign('coin_list', $coin_list);
        $user_coin = M('UserCoin')->where(array('userid' => userid()))->find();
        $user_coin[$coin] = round($user_coin[$coin], 6);
        $user_coin[$coin] = sprintf("%.4f", $user_coin[$coin]);
        $this->assign('user_coin', $user_coin);
        $this->display();
    }
	
    public function uppush($coin,$type,$min,$max,$bz,$gj,$yj,$num,$price,$paypassword)
	{
        if (!userid()) {
            $this->error(L('您没有登录请先登录！'));
        }
        $user = M('user')->where(array('id'=>userid()))->find();
        if (md5($paypassword) != $user['paypassword']) {
            $this->error(L('交易密码错误！'));
        }
        // $this->error($coin);
        $num = abs($num);
        $min=abs($min);
        $max=abs($max);
        if($coin=='btc'){
            $map['unit']='CNY/BTC';
        }
         if($coin=='usdt'){
            $map['unit']='CNY/USDT';
        }
        $map['lang']='zh-cn';
        $map['nation']=$gj;
        $map['market']=$coin;
        $map['price']=$price;
        $map['type']=$type;
        $map['quota']=$min.','.$max;
        $map['addtime']=time();
        $map['merchant']=$user['username'];
        $ccc=M('ptpbc')->add($map);
        if( $ccc){
             $this->success(L('发布成功'));
        }else{
              $this->error(L('发布失败'));
        }

    }
	
    public function apply()
	{
        $merchant = [];
        $this->assign('merchant', $merchant);
        $this->display();
    }
	
    public function buy()
	{
        $id=I('get.mid',0);
        if(!$id)$this->error('参数错误');
        $info=M('Ptpbc')->where(array('id'=>$id))->find();
        $info['quota']=explode(',',$info['quota']);
        $payments=explode(',',$info['payments']);
        $receives=explode(',',$info['receives']);
        $pay_list=array();
        foreach ($payments as $k=>$v){
            $pay_list[]=array('payment'=>$payments[$k],'receive'=>$receives[$k]);
        }
        $info['pay_list']=$pay_list;
        $this->assign('data',$info);
        $this->display();
    }
	
    public function buy_action()
	{
        $id=I('post.id',0);
        if(!$id)$this->error('参数错误');
        $info=M('Ptpbc')->where(array('id'=>$id))->find();
        $pay=explode(':',I('post.payment'));
        $data=array(
            'userid'=>userid(),
            'market'=>$info['market'],
            'price'=>$info['price']*intval(I('post.num')),
            'unit'=>$info['unit'],
            'num'=>I('post.num'),
            'merchant'=>$info['merchant'],
            'payment'=>$pay[0],
            'receive'=>$pay[1],
            'addtime'=>time(),
        );
        $res=M('PtpbcLog')->add($data);
        if($res){
            $this->redirect('buyinfo',array('id'=>$res));
        }else{
            $this->error('购买失败！');
        }
    }
	
    public function buylist()
	{
        $this->display();
    }
	
    public function buyinfo()
	{
        $id=I('get.id');
        if(I('post.status')==1 && I('post.id')){
            $res=M('PtpbcLog')->where(array('id'=>I('post.id')))->save(array('status'=>1));
        }
        $info=M('PtpbcLog')->where(array('id'=>$id))->find();
        $this->assign('data',$info);
        $this->display();
    }
}