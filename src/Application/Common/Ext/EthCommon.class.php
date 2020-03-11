<?php
namespace Common\Ext;

class EthCommon
{
    protected $host, $port, $version;
    protected $id = 0;
    public $base = 1000000000000000000;//1e18 wei  基本单位

	/**
	* 构造函数
	* Common constructor.
	* @param $host
	* @param string $port
	* @param string $version
	*/
    function __construct($host, $port = "80", $version = "2.0")
    {
        $this->host = $host;
        $this->port = $port;
        $this->version = $version;
    }

	/**
	* 发送请求
	* @author qiuphp2
	* @since 2017-9-21
	* @param $method
	* @param array $params
	* @return mixed
	*/
    function request($method, $params = array())
    {
        $data = array();
        $data['jsonrpc'] = $this->version;
        $data['id'] = $this->id + 1;
        $data['method'] = $method;
        $data['params'] = $params;
        // echo json_encode($data);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->host);
        curl_setopt($ch, CURLOPT_PORT, $this->port);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $ret = curl_exec($ch);
        //返回结果
        if ($ret) {
            curl_close($ch);
            return json_decode($ret, true);
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            return json_decode('ETH钱包服务器连接失败', true);
            // throw new Exception("curl出错，错误码:$error");
        }
    }

	/**
	* @author qiuphp2
	* @since 2017-9-21
	* @param $weiNumber 16进制wei单位
	* @return float|int 10进制eth单位【正常单位】
	*/
    function fromWei($weiNumber)
    {
        $ethNumber = hexdec($weiNumber) / $this->base;
        return $ethNumber;
    }

	/**
	* @author qiuphp2
	* @since 2017-9-21
	* @param $ethNumber 10进制eth单位
	* @return string    16进制wei单位
	*/
    function toWei($ethNumber)
    {
        $weiNumber = dechex($ethNumber * $this->base);
        // $weiNumber = float($weiNumber);
        return $weiNumber;
    }

	/**
	* 判断是否是16进制
	* @author qiuphp2
	* @since 2017-9-21
	* @param $a
	* @return int
	*/
    function assertIsHex($a)
    {
        if (ctype_xdigit($a)) {
            return true;
        } else {
            return false;
        }
    }
	
	/**
	* 获取版本信息,判断是否连接
	* @author qiuphp2
	* @since 2017-9-19
	*/
    function web3_clientVersion()
	{
		$params = array();
		$data = $this->request(__FUNCTION__, $params);
        if ($data['result']) {
            return true;
        } else {
            return false;
        }
        //return $data['result'];
    }
	
	/**
	* 获取主账户
	* @author qiuphp2
	* @since 2017-9-19
	*/
    function eth_coinbase()
	{
		$params = array();
        $data = $this->request(__FUNCTION__, $params);
        if ($data['result']) {
            return $data['result'];
        } else {
            return $data['error']['message'];
        }
        // return $data['result'];
    }
	
	/**
	* 获取区块数量
	* @author qiuphp2
	* @since 2017-9-19
	*/
    function eth_blockNumber()
	{
		$params = array();
        $data = $this->request(__FUNCTION__, $params);
        if ($data['result']) {
            return $data['result'];
        } else {
            return $data['error']['message'];
        }
        // return $data['result'];
    }
	
	/**
	* 新建账号 有点耗时 最好给用户生成的之后，密码保存在数据库里面
	* @author qiuphp2
	* @since 2017-9-19
	*/
    function personal_newAccount($password='')//一般用账户名作为加密密码
    {
        // $password = "123";//密码
        $params = array($password);
        $data = $this->request(__FUNCTION__, $params);
        if (empty($data['error']) && !empty($data['result'])) {
            return $data['result'];//新生成的账号公钥
        } else {
            return $data['error']['message'];
        }
    }
	
	/**
	* @author qiuphp2
	* @since 2017-9-21
	* @return float|int 返回eth数量 10进制
	*/
    function eth_getBalance($account='')
    {
//		var_dump($account);
//		$account = $_REQUEST['account'];//获得账号公钥
		if ($account=='') {
			echo '请传入账号公钥';
			return false;
		}
        $params = [$account,"latest"];
        $data = $this->request(__FUNCTION__, $params);
        if (empty($data['error']) && !empty($data['result'])) {
            // return $this->fromWei($data['result']);//返回eth数量，自己做四舍五入处理
            return $data['result'];//返回eth数量，自己做四舍五入处理
        } else {
            return $data['error']['message'];
        }
    }

	/**
	* @author qiuphp2
	* @since 2017-9-21
	* @return float|int 返回eth数量 10进制
	*/
    function eth_getBalancehex($account='')
    {
//		var_dump($account);
//		$account = $_REQUEST['account'];//获得账号公钥
		if ($account=='') {
			// echo '请传入账号公钥';
			return false;
		}
        $params = [$account,"latest"];
        $data = $this->request(eth_getBalance, $params);
        if (empty($data['error']) && !empty($data['result'])) {
            return $data['result'];//返回eth数量，自己做四舍五入处理
        } else {
            return $data['error']['message'];
        }
    }
	
	/**
	* 转账
	* @author qiuphp2
	* @since 2017-9-15
	*/
    function eth_sendTransaction($from='',$to='',$password='',$value='')
    {
        if ($from=='' || $to=='' || $password=='' || $value=='') {
             // echo '传入参数缺失';
            return false;
        }

        if (!ctype_xdigit($value)) {
           $value = $this->toWei($value);//这里是发送10进制的方法
        }
		
		$value = '0x'.$value;//转换成可识别格式
        $gas = $this->eth_estimateGas($from, $to, $value);//16进制 消耗的gas 0x5209
        $gasPrice = $this->eth_gasPrice();//价格 0x430e23400
        $status = $this->personal_unlockAccount($from, $password);//解锁
        if (!$status) {
			 return '解锁失败';
            // return false;
        }
        $params = array(
            "from" => $from,
            "to" => $to,
            "gas" => $gas,//2100
            "gasPrice " => $gasPrice,//18000000000
            "value" => $value,//2441406250
            "data" => "",
        );

        $data = $this->request(__FUNCTION__, [$params]);
        if (empty($data['error']) && !empty($data['result'])) {
            return $data['result'];//转账之后，生成HASH
            // return true;
        } else {
            // return $data['error']['message'];
            return false;
        }
    }
	
	function eth_sendTransactionraw($from='',$to='',$password='',$data='')
    {
        if($from=='' || $to=='' || $password=='' || $data==''){
			// return 'lost.';
            return false;
        }

        $status = $this->personal_unlockAccount($from, $password);//解锁
        if (!$status) {
			 // return 'unlock fail';
            return false;
        }
		
        $params = array(
            "from" => $from,
            "to" => $to,
            "data" => $data,
        );
        $data = $this->request(eth_sendTransaction, [$params]);
        if (empty($data['error']) && !empty($data['result'])) {
            return $data['result'];//转账之后，生成HASH
            // return true;
        } else {
            // return $data['error']['message'];
            return false;
        }
    }
	
	/**
	* 转账详细信息
	* @author qiuphp2
	* @since 2017-9-20
	*/
    function eth_getTransactionReceipt($transactionHash='')
    {
        //$transactionHash = "0x536135ef85aa8015b086e77ab8c47b8d40a3d00a975a5b0cc93b2a6345f538cd";
        if($transactionHash==''){
            echo '缺少交易hash';
            return false;
        }
        // 交易 hash
        $params = array(
            $transactionHash,
        );
        $data = $this->request(__FUNCTION__, $params);
        if (empty($data['error'])) {
            if (count($data['result']) == 0) {
                $result['status'] = 0;
                $result['data'] = '等待确认';
                // echo '等待确认';
            } else {
                $result['status'] = 1;
                $result['data'] = $data['result']['blockHash'];
                // return $data['result']['blockHash'];//返回blockhash值
                // return $data['result'];//转账成功了
            }
        } else {
            $result['status'] = 0;
            $result['data'] = $data['error']['message'];
            // return $data['error']['message'];
        }
         return $result;
    }

	/**
	* 获得消耗多少 GAS
	* @author qiuphp2
	* @since 2017-9-15
	*/
    function eth_estimateGas($from, $to, $value)
    {
        $params = array(
            "from" => $from,
            "to" => $to,
            "value" => $value
        );
        $data = $this->request(__FUNCTION__, [$params]);
        return $data['result'];
    }
	
	/**
	* 获得当前 GAS 价格
	* @author qiuphp2
	* @since 2017-9-15
	*/
    function eth_gasPrice()
    {
        $params = array();
        $data = $this->request(__FUNCTION__, $params);
        return $data['result'];
    }

	/**
	* 解锁账号 此函数可能比较耗时
	* @author qiuphp2
	* @since 2017-9-15
	*/
    function personal_unlockAccount($account, $password)
    {
        $params = array($account,$password,100);
        $data = $this->request(__FUNCTION__, $params);
        if (!empty($data['error'])) {
            return $data['error']['message'];//解锁失败
        } else {
            return $data['result'];//成功返回true
        }
    }

    function personal_listAccounts(){
        $params = array();
        $data = $this->request(__FUNCTION__, $params);
        if (empty($data)) {
            return false;
        } else {
            return $data['result'];//成功返回true
        }
    }
}
?>