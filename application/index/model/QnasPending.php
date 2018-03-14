<?php
namespace app\index\model;

//导入模型类
use think\Model;
use app\index\model\QnaPendingDetails;
use app\index\model\Qnas;
use app\index\model\Users;
use app\index\model\Transactions;
use app\index\model\Message;

class QnasPending extends Model {
	
	// 设置当前模型对应的完整数据表名称
    protected $table = 'BLT_qna_pending';

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
	
	public function getPendingByUserId($qnaid, $userid){
        $new_pending = $this->where('pending_userid',$userid)
		->where('qnaid', $qnaid)
		->field('status,pendingid')
		->limit(1)
		->find();          // 查询用户
        if (empty($new_pending)) {                 // 判断是否出错
            return false;
        }
        return $new_pending;   // 返回修改后的数据
	}
	
	public function getMonthAcceptCount(){
		$new_accept = $this->where('','exp','YEAR(pending_date)=YEAR(CURDATE()) and MONTH(pending_date)=MONTH(CURDATE())')
		->where('status',4)
		->field('count(*) as accept_count')
		->find();
		return $new_accept;
	}

	public function getMonthArbitrateCount(){
		$new_arbitrate = $this->where('','exp','YEAR(pending_date)=YEAR(CURDATE()) and MONTH(pending_date)=MONTH(CURDATE())')
		->where('status','in','7,8')
		->field('count(*) as arbitrate_count')
		->find();
		return $new_arbitrate;
	}
}