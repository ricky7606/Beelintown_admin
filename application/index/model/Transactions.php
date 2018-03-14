<?php
namespace app\index\model;

//导入模型类
use think\Model;
use app\index\model\Users;
use app\index\model\Message;
use app\index\model\TransactionDetails;

class Transactions extends Model {
	
	// 设置当前模型对应的完整数据表名称
    protected $table = 'BLT_transaction';

	//在子类重写父类的初始化方法initialize()
	protected function initialize(){
		
	  //继承父类中的initialize()
		parent::initialize();
		
	   //初始化数据表字段信息	
	   $this->field = $this->db()->getTableInfo('', 'fields');  
	
	   //初始化数据表字段类型
	   $this->type = $this->db()->getTableInfo('', 'type'); 
	
	   //初始化数据表主键
	   $this->pk = $this->db()->getTableInfo('', 'pk');     
		
		
		}

	public function getPendingInviteTransactionByQnaId($qnaid){
        $trans = $this->where('qnaid', $qnaid)
		->where('transaction_type',1)
		->where('reference_userid','exp','is NULL')
		->field('transactionid')
		->find();          // 查询所有用户的所有字段资料
        if (empty($trans)) {                 // 判断是否出错
            return false;
        }
        return $trans;   // 返回修改后的数据
	}
	
	public function getTransFormatDetailsByUserId($userid){
		$trans = new TransactionDetails;
        $trans_list = $trans->where('userid', $userid)
		->field("*,DATE_FORMAT(transaction_date,'%Y-%m-%d %H:%i:%s') as format_date")
		->order('transaction_date','desc')
		->paginate(10);          // 查询所有用户的所有字段资料
        if (empty($trans_list)) {                 // 判断是否出错
            return false;
        }
		foreach($trans_list as $tran){
			$tran['coins'] = floatval($tran['coins']);
			$tran['coins_before'] = floatval($tran['coins_before']);
			$tran['coins_after'] = floatval($tran['coins_after']);
			$tran['title'] = getContentText($tran['title'], 50);
			$tran['transaction_type_desc'] = getTransactionDesc($tran['transaction_type']);
		}
        return $trans_list;   // 返回修改后的数据
	}
	
//	交易记录(Transaction)状态码说明：
//	0 - 注册奖励；
//	1 - 问答冻结；
//	2 - 问答佣金冻结；
//	3 - 问答解冻；
//	4 - 问答佣金解冻；
//	5 - 问答支付；
//	6 - 问答悬赏佣金扣除；
//	7 - 问答入账佣金扣除；
//	8 - 问答分享奖励入账；
//	9 - 问答入账；
//	10 - 问答仲裁处理解冻；
//	11 - 问答仲裁处理支付；
//	12 - 问答仲裁处理入账；
//	13 - 提现冻结；
//	14 - 提现成功（扣除）；
//	15 - 提现失败（解冻返回）；
//	16 - 充值；
//	99 - 其它；
	public function saveTransaction($userid, $coins, $transaction_type, $qnaid = NULL, $reference_userid = NULL, $reference_pendingid = NULL, $wallet = NULL, $comments = NULL, $serial_number = NULL){
		$this->startTrans();
		$user = new Users;
		$userinfo = $user->getUserDetails($userid);
		$frozen_coins = $userinfo->frozen_coins;
		$coins_before = $userinfo->coins;
		
		switch($transaction_type){
			//注册奖励
			case 0:
				$coins_after = $coins;
				$result_user = $user->where('userid', $userid)->update(['coins' => $coins]);
				break;
			//问答冻结
			case 1:
				$coins_after = $coins_before;
				$frozen_coins = bcadd($frozen_coins, $coins, 8);
				$result_user = $user->where('userid', $userid)->update(['frozen_coins' => $frozen_coins]);
				break;
			//问答佣金冻结
			case 2:
				$coins_after = $coins_before;
				$frozen_coins = bcadd($frozen_coins, $coins, 8);
				$result_user = $user->where('userid', $userid)->update(['frozen_coins' => $frozen_coins]);
				break;
			//问答解冻
			case 3:
				$coins_after = $coins_before;
				$frozen_coins = bcsub($frozen_coins, $coins, 8);
				$result_user = $user->where('userid', $userid)->update(['frozen_coins' => $frozen_coins]);
				break;
			//问答佣金解冻
			case 4:
				$coins_after = $coins_before;
				$frozen_coins = bcsub($frozen_coins, $coins, 8);
				$result_user = $user->where('userid', $userid)->update(['frozen_coins' => $frozen_coins]);
				break;
			//问答支付
			case 5:
				$coins_after = bcsub($coins_before, $coins, 8);
				$frozen_coins = bcsub($frozen_coins, $coins, 8);
				$result_user = $user->where('userid', $userid)->update(['frozen_coins' => $frozen_coins, 'coins' => $coins_after]);
				break;
			//问答悬赏佣金扣除
			case 6:
				$coins_after = bcsub($coins_before, $coins, 8);
				$frozen_coins = bcsub($frozen_coins, $coins, 8);
				$result_user = $user->where('userid', $userid)->update(['frozen_coins' => $frozen_coins, 'coins' => $coins_after]);
				break;
			//问答入账佣金扣除
			case 7:
				$coins_after = bcsub($coins_before, $coins, 8);
				$result_user = $user->where('userid', $userid)->update(['coins' => $coins_after]);
				break;
			//问答分享奖励入账
			case 8:
				$coins_after = bcadd($coins_before, $coins, 8);
				$result_user = $user->where('userid', $userid)->update(['coins' => $coins_after]);
				break;
			//问答入账
			case 9:
				$coins_after = bcadd($coins_before, $coins, 8);
				$result_user = $user->where('userid', $userid)->update(['coins' => $coins_after]);
				break;
			//问答仲裁处理解冻
			case 10:
				$coins_after = $coins_before;
				$frozen_coins = bcsub($frozen_coins, $coins, 8);
				$result_user = $user->where('userid', $userid)->update(['frozen_coins' => $frozen_coins]);
				break;
			//问答仲裁处理支付
			case 11:
				$coins_after = bcsub($coins_before, $coins, 8);
				$frozen_coins = bcsub($frozen_coins, $coins, 8);
				$result_user = $user->where('userid', $userid)->update(['frozen_coins' => $frozen_coins, 'coins' => $coins_after]);
				break;
			//问答仲裁处理入账
			case 12:
				$coins_after = bcadd($coins_before, $coins, 8);
				$result_user = $user->where('userid', $userid)->update(['coins' => $coins_after]);
				break;
			//提现冻结
			case 13:
				$coins_after = $coins_before;
				$frozen_coins = bcadd($frozen_coins, $coins, 8);
				$result_user = $user->where('userid', $userid)->update(['frozen_coins' => $frozen_coins]);
				break;
			//提现成功（扣除）
			case 14:
				$coins_after = bcsub($coins_before, $coins, 8);
				$frozen_coins = bcsub($frozen_coins, $coins, 8);
				$result_user = $user->where('userid', $userid)->update(['frozen_coins' => $frozen_coins, 'coins' => $coins_after]);
				break;
			//提现失败，解冻返回
			case 15:
				$coins_after = $coins_before;
				$frozen_coins = bcsub($frozen_coins, $coins, 8);
				$result_user = $user->where('userid', $userid)->update(['frozen_coins' => $frozen_coins]);
				break;
			//其他原因的系统操作直接扣除比邻币
			case 99:
				$coins_after = bcsub($coins_before, $coins, 8);
				$result_user = $user->where('userid', $userid)->update(['coins' => $coins_after]);
				break;
		}
		
		$this->transactionid = uuid();
		$this->userid = $userid;
		$this->coins = $coins;
		$this->transaction_type = $transaction_type;
		$this->coins_before = $coins_before;
		$this->coins_after = $coins_after;
		$this->reference_userid = $reference_userid;
		$this->reference_pendingid = $reference_pendingid;
		$this->comments = $comments;
		$this->walletid = $wallet;
		$this->qnaid = $qnaid;
		$this->serial_number = $serial_number;
		$this->transaction_date = udate('Y-m-d H:i:s.u');
		$result = $this->isUpdate(false)->save();
		//交易消息提醒
		$user->addNewTransactionReminder($userid);
		//强制暂停一点时间以确保交易记录先后发生
		usleep(1);

		if($result === false || $result_user === false){
			$this->rollBack();
			return false;
		}else{
			$this->commit();
			return true;
		}
	}
	
	public function getExtractCoinsList($status){
		$tran = new TransactionDetails;
		$trans = $tran->where('transaction_type', 13)
		->where('extract_status', 'in', $status)
		->order('transaction_date', 'desc')
		->paginate(20);
        if (empty($trans)) {                 // 判断是否出错
            return false;
        }
        return $trans;   // 返回修改后的数据
	}
	
	public function getPendingExtractCount(){
		$extract_count = $this->where('extract_status', 'in', '0,1')
		->where('transaction_type', 13)
		->field('count(*) as extract_count')
		->find();
		return $extract_count;
	}

	public function updateExtract($transactionid, $status, $serial_number){
		$this->startTrans();
		$this->transactionid = $transactionid;
		$this->extract_status = $status;
		$this->extract_update_date = udate('Y-m-d H:i:s.u');
		$result = $this->isUpdate(true)->save();
		$message = new Message;
		$tran_info = $this->where('transactionid', $transactionid)
		->find();
		$userid = $tran_info->userid;
		$coins = $tran_info->coins;
		if($result && ($status == 2 || $status == 3)){
			switch($status){
				case 2:
					$result = $this->saveTransaction($userid, $coins, 14, NULL, NULL, NULL, NULL, NULL, $serial_number);
					$message_text = "您申请提现".floatval($coins)."比邻币已经通过，详情请查看<a href=\"\\index\\usercoins\">比邻币交易记录</a>。";
					$result_message = $message->saveNewMessage($userid, $message_text);
					break;
				case 3:
					$result = $this->saveTransaction($userid, $coins, 15, NULL, NULL, NULL, NULL, NULL, $serial_number);
					$message_text = "对不起，您申请提现".floatval($coins)."比邻币已被拒绝，如有疑问，可联系平台。";
					$result_message = $message->saveNewMessage($userid, $message_text);
					break;
			}
		}else{
			$message_text = "您申请提现".floatval($coins)."比邻币已经被受理，请耐心等待，我们将尽快处理。";
			$result_message = $message->saveNewMessage($userid, $message_text);
		}
		if($result === false){
			$this->rollBack();
			return "error";
		}else{
			$this->commit();
			return "ok";
		}
	}
	
	public function users()
    {
        return $this->belongsTo('Users','userid');
    }
}