<?php
namespace app\index\model;

//导入模型类
use think\Model;

class QnaPendingDetails extends Model {
	
	// 设置当前模型对应的完整数据表名称
    protected $table = 'view_QnaPending';

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

	public function getPendingDetailsByUserId($userid, $pending_status = 1){
        $new_pending = $this->where('pending_userid',$userid)
		->where('status',$pending_status)
		->field('qnaid,pendingid,qna_userid,qna_username,title,content,content_text,thumb_img,coins,status,attentionid,followid,qna_user_personal_pic')
		->order('pending_date','asc')
		->select();          // 查询用户
        if (empty($new_pending)) {                 // 判断是否出错
            return false;
        }
        return $new_pending;   // 返回修改后的数据
	}

	public function getPendingDetailsByPendingId($pendingid){
        $new_pending = $this->where('pendingid',$pendingid)
		->field('pendingid,pending_type,qnaid,title,content,content_text,thumb_img,coins,status,payer_coins,payee_coins,qna_userid,qna_username,pending_userid,pending_username,transactionid,payer_frozen_coins')
		->find();         // 查询用户
        return $new_pending;   // 返回修改后的数据
	}

	public function getPendingDetailsByQnaId($qnaid, $status = 0){
        $new_pending = $this->where('qnaid', $qnaid)
		->where('status', $status)
		->field('pendingid,qnaid,status,pending_userid,pending_username,title,content,content_text,thumb_img,coins,pending_date')
		->select();          // 查询用户
        if (empty($new_pending)) {                 // 判断是否出错
            return false;
        }
        return $new_pending;   // 返回修改后的数据
	}

	public function getPendingInviteByQnaId($qnaid){
        $new_pending = $this->where('qnaid', $qnaid)
		->where('pending_type', 1)
		->field('pendingid,qnaid,status,pending_userid,pending_username,pending_date,pending_user_personal_pic')
		->select();          // 查询用户
        if (empty($new_pending)) {                 // 判断是否出错
            return false;
        }
        return $new_pending;   // 返回修改后的数据
	}
}