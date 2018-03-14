<?php
namespace app\index\model;

//导入模型类
use think\Model;
use think\Cookie;
use app\index\model\Tags;

class UsersTags extends Model {
	
	// 设置当前模型对应的完整数据表名称
    protected $table = 'BLT_user_tag';
	
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

	public function addTag($userid, $tagid){
		$tag = $this->where('userid', $userid)
		->where('tagid', $tagid)
		->limit(1)
		->find();
		if($tag){
			return "您已经添加了该标签";
		}else{
			$tag_count = $this->where('userid', $userid)
			->select();
			if(count($tag_count)>=20){
				return "您最多只能添加20个标签";
			}else{
				$user_tagid = uuid();
				$this->user_tagid = $user_tagid;
				$this->userid = $userid;
				$this->tagid = $tagid;
				$this->create_date = date('Y-m-d H:i:s',time());
				$result = $this->isUpdate(false)->save();
				if($result){
					$tags = new Tags;
					$new_tag = $tags->where('id',$tagid)->limit(1)->find();
					$str = "ok___".$new_tag->tag."___".$user_tagid;
					return $str;
				}else{
					return "添加失败";
				}
			}
		}
	}

	public function delTag($user_tagid, $userid){
		$result = $this->where('user_tagid', $user_tagid)
		->delete();
		if($result){
			$tags = $this->where('userid', $userid)
			->select();
			$str = "ok,".(string)count($tags);
			return $str;
		}else{
			return "删除失败";
		}
	}
}