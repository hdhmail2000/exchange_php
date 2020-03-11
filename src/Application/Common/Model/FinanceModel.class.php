<?php
namespace Common\Model;

class FinanceModel extends \Think\Model
{
	protected $keyS = 'Finance';

	public function check_install()
	{
		$check_install = (APP_DEBUG ? null : S('check_install' . $this->keyS));

		if (!$check_install) {
			$tables = M()->query('show tables');
			$tableMap = array();

			foreach ($tables as $table) {
				$tableMap[reset($table)] = 1;
			}

			if (!isset($tableMap['tw_finance'])) {
				M()->execute("\r\n" . '                          CREATE TABLE `tw_finance` (' . "\r\n" . '                                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT \'自增id\',' . "\r\n" . '                                `userid` INT(11) UNSIGNED NOT NULL COMMENT \'用户id\',' . "\r\n" . '                                `coinname` VARCHAR(50) NOT NULL COMMENT \'币种\',' . "\r\n" . '                                `num` DECIMAL(20,8) UNSIGNED NOT NULL COMMENT \'之前数量\',' . "\r\n" . '                                `fee` DECIMAL(20,8) UNSIGNED NOT NULL COMMENT \'操作数量\',' . "\r\n" . '                                `mum` DECIMAL(20,8) UNSIGNED NOT NULL COMMENT \'剩余数量\',' . "\r\n" . '                                `type` VARCHAR(50) NOT NULL COMMENT \'操作类型\',' . "\r\n" . '                                `remark` VARCHAR(50) NOT NULL COMMENT \'备注\',' . "\r\n" . '                                `addtime` INT(11) UNSIGNED NOT NULL COMMENT \'添加时间\',' . "\r\n" . '                                `status` TINYINT(4) UNSIGNED NOT NULL COMMENT \'状态\',' . "\r\n" . '                                PRIMARY KEY (`id`),' . "\r\n" . '                                INDEX `userid` (`userid`),' . "\r\n" . '                                INDEX `coinid` (`coinname`),' . "\r\n" . '                                INDEX `status` (`status`)' . "\r\n" . '                            )' . "\r\n" . '                            COMMENT=\'财务记录表\'' . "\r\n" . '                            COLLATE=\'utf8_general_ci\'' . "\r\n" . '                            ENGINE=InnoDB' . "\r\n" . '                            ;' . "\r\n\t\t\t\t\t\t");
			}

			S('check_install' . $this->keyS, 1);
		}
	}

	public function updata($userid = NULL, $coinname = NULL, $type = NULL, $remark = NULL)
	{
	}
}

?>