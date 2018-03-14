<?php
namespace app\index\model;

//导入模型类
use think\Model;
use app\index\model\QnasPending;
use app\index\model\QnasReplyDetails;
use app\index\model\Transactions;
use app\index\model\Users;

class Wallet extends Model {
	
	// 设置当前模型对应的完整数据表名称
    protected $table = 'BLT_wallet';

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

	public function getWalletByUserId($userid){
		$wallet_list = $this->where('userid', $userid)
		->where('status', 0)
		->order('create_date', 'desc')
		->select();
		return $wallet_list;
	}

	public function saveNewWallet($userid, $wallet_tag, $wallet_address){
		$this->walletid = uuid();
		$this->userid = $userid;
		$this->wallet_tag = $wallet_tag;
		$this->wallet_address = $wallet_address;
		$this->create_date = date('Y-m-d H:i:s',time());
		return $this->isUpdate(false)->save();
	}
	
	public function deleteWallet($walletid){
		$this->walletid = $walletid;
		$this->status = 1;
		return $this->isUpdate(true)->save();
	}

	public function users()
    {
        return $this->belongsTo('Users','userid');
    }
}