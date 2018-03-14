<?php
namespace app\index\model;

//导入模型类
use think\Model;

class QnasUser extends Model {
	
	// 设置当前模型对应的完整数据表名称
    protected $table = 'view_QnaUser';

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
        $new_qnauser = $this->order('create_date','desc')
		->paginate(10);          
        if (empty($new_qnauser)) {                 // 判断是否出错
            return false;
        }
        return $new_qnauser;   // 返回修改后的数据
	}

	public function getQnasByUserId($userid){
        $qnauser = $this->where('userid',$userid)
		->order('create_date','desc')
		->paginate(10);          // 查询所有用户的所有字段资料
        if (empty($qnauser)) {                 // 判断是否出错
            return false;
        }
        return $qnauser;   // 返回修改后的数据
	}
	
	public function getRandomQnasUsers($limit){
        $random_users = $this->limit($limit)
		->field('distinct username')
		->order('ROUND(RAND()*1000)','desc')
		->select();          // 查询随机用户
        if (empty($random_users)) {                 // 判断是否出错
            return false;
        }
        return $random_users;   // 返回修改后的数据
	}    
	
	public function searchQnas($keywords){
		$search_qnas = $this->where('title','like','%'.$keywords.'%')
		->order('create_date','desc')
		->paginate(10,false,['query'=>request()->param()]);  
        if (empty($search_qnas)) {                 // 判断是否出错
            return false;
        }
        return $search_qnas;   // 返回修改后的数据
	}
}