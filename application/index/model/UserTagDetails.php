<?php
namespace app\index\model;

//导入模型类
use think\Model;
use think\Cookie;

class UserTagDetails extends Model {
	
	// 设置当前模型对应的完整数据表名称
    protected $table = 'view_UserTagDetails';
	
	//protected $resultSetType = 'collection';

	//在子类重写父类的初始化方法initialize()
	protected function initialize(){
		
	  //继承父类中的initialize()
		parent::initialize();
		
	   //初始化数据表字段信息	
	   $this->field = $this->db()->getTableInfo('', 'fields');  
	
	   //初始化数据表字段类型
	   $this->type = $this->db()->getTableInfo('', 'type'); 
	
	   
	   $this->pk = $this->db()->getTableInfo('', 'pk');     
		}

	public function getTagListByUserId($userid, $limit = NULL){
		if($limit == NULL){$limit = 100;}
		$user_tag_list = $this->where('userid', $userid)
		->limit($limit)
		->select();
		return $user_tag_list;
	}

	public function getTagUsers($tagid){
		$user_list = $this->where('tagid', $tagid)
		->field('userid,username,create_date')
		->paginate(20,false,['query'=>request()->param()]);
        return $user_list;   // 返回修改后的数据
	}
	
	public function getTagCount($tagid){
		$tag_count = $this->where('tagid', $tagid)
		->field('tag, count(*) as tag_count')
		->find();
		return $tag_count;
	}
	
	public function getTagSortList(){
		$tag_list = $this->group('tagid')
		->field('tag,tagid,count(*) as tag_num')
		->order('count(*)','desc')
		->paginate(20);
		return $tag_list;
	}
}