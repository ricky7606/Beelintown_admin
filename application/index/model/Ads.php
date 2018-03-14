<?php
namespace app\index\model;

//导入模型类
use think\Model;
use app\index\model\Users;
use app\index\model\FollowDetails;

class Ads extends Model {
	
	// 设置当前模型对应的完整数据表名称
    protected $table = 'BLT_ads';

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

	public function getAdsList(){
		$list = $this->order('create_date','desc')
		->paginate(20);
		return $list;
	}
	
	public function getAdDetails($ads_id){
		$detail = $this->where('ads_id', $ads_id)
		->find();
		return $detail;
	}

	public function updateAd($ads_id, $ads_name, $ads_link, $position, $ads_image, $new_window, $enable){
		$this->ads_id = $ads_id;
		if($ads_name != ''){
			$this->ads_name = $ads_name;
		}
		if($ads_link != ''){
			$this->ads_link = $ads_link;
		}
		if($position != ''){
			$this->position = $position;
		}
		if($ads_image != ''){
			$this->ads_image = $ads_image;
		}
		if($new_window != ''){
			$this->new_window = $new_window;
		}
		if($enable != ''){
			$this->enable = $enable;
		}
		$result = $this->isUpdate(true)->save();
		if($result === false){
			return "数据错误";
		}else{
			return "ok";
		}
	}

	public function createAd($ads_name, $ads_link, $position, $ads_image, $new_window, $enable){
		$this->ads_id = uuid();
		$this->ads_name = $ads_name;
		$this->ads_link = $ads_link;
		$this->position = $position;
		$this->ads_image = $ads_image;
		$this->new_window = $new_window;
		$this->enable = $enable;
		$this->create_date = date('Y-m-d H:i:s',time());
		$result = $this->isUpdate(false)->save();
		if($result){
			return "ok";
		}else{
			return "数据错误";
		}
	}
}