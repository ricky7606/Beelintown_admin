<?php
namespace app\index\model;

//导入模型类
use think\Model;
use app\index\model\QnasPending;
use app\index\model\Transactions;
use app\index\model\Users;
use app\index\model\Message;

class Qnas extends Model {
	
	// 设置当前模型对应的完整数据表名称
    protected $table = 'BLT_qna';

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
	public function getNewQnas(){
        $new_qnas = $this->with('users')->limit(10)
		->order('create_date','desc')
		->select();          // 查询所有用户的所有字段资料
        if (empty($new_qnas)) {                 // 判断是否出错
            return false;
        }
        return $new_qnas;   // 返回修改后的数据
	}
	
	public function getMonthQnaCount(){
		$new_qna = $this->where('','exp','YEAR(create_date)=YEAR(CURDATE()) and MONTH(create_date)=MONTH(CURDATE())')
		->field('count(*) as qna_count')
		->find();
		return $new_qna;
	}
	
	public function getQnaDetailsByQnaId($qnaid){        
		$qna = $this->where('qnaid',$qnaid)		
		->field('qnaid,userid,title,content,content_text,coins,thumb_img,status')		
		->limit(1)		
		->find();          // 查询用户       
		if (empty($qna)) {                 // 判断是否出错         
		   return false;        
		}        
		return $qna;   // 返回修改后的数据	
	}	
	
	public function users()    {
		return $this->belongsTo('Users','userid');    
	}
}