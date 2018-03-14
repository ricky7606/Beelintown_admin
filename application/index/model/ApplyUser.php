<?php
namespace app\index\model;

//导入模型类
use think\Model;
use app\index\model\Users;

class ApplyUser extends Model {
	
	// 设置当前模型对应的完整数据表名称
    protected $table = 'view_ApplyUser';

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

	public function getPendingApplyByQnaId($qnaid){
        $new_apply = $this->where(function ($query) use ($qnaid){
		$query->where('qnaid',$qnaid)
		->where('status',0)
		->field('userid,username,apply_date,applyid');
		})->select();          // 查询用户
        if (empty($new_apply)) {                 // 判断是否出错
            return false;
        }
        return $new_apply;   // 返回修改后的数据
	}
	
	public function getApplyInfoByApplyId($applyid){
        $apply = $this->where(function ($query) use ($applyid){
		$query->where('applyid',$applyid)
		->where('status',0)
		->field('userid,qna_userid,username,apply_date,applyid');
		})->find();          // 查询用户
        if (empty($apply)) {                 // 判断是否出错
            return false;
        }
        return $apply;   // 返回修改后的数据
	}
	
	public function getQnaByApplyUserId($userid, $status=0){
        $search_users = $this->where(function ($query) use ($userid, $status){
		$query->where('userid',$userid)
		->where('status',$status)
		->field('applyid,qnaid,title,content,content_text,thumb_img,coins,status')
		->order('apply_date','asc');
		})->select();          // 查询用户
        if (empty($search_users)) {                 // 判断是否出错
            return false;
        }
        return $search_users;   // 返回修改后的数据
	}

	
	public function users()
    {
        return $this->belongsTo('Users','userid');
    }
}