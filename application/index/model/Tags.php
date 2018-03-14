<?php
namespace app\index\model;

//导入模型类
use think\Model;
use think\Cookie;

class Tags extends Model {
	
	// 设置当前模型对应的完整数据表名称
    protected $table = 'BLT_tag';
	
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

	public function getRandomTags($num){
		$tag_list = $this->limit($num)
		->order('ROUND(RAND()*1000)','desc')
		->select();
        if (empty($tag_list)) {                 // 判断是否出错
            return false;
        }
		$tmpStr = "";
		foreach($tag_list as $tag){
			$tmpTag = $this->where('parentid', $tag->id)
			->limit(1)
			->find();
			if($tmpTag){
				$tmpStr .= $tag->tag."___".$tag->id."___true$$$";
			}else{
				$tmpStr .= $tag->tag."___".$tag->id."___false$$$";
			}
		}
        return $tmpStr;   // 返回修改后的数据
	}
	
	public function searchTags($tag, $num = 20, $page = 1){
		$noSearched = false;
		if(!$tag == ''){
			$tag_list = $this->where('tag','like','%'.$tag.'%')
			->field('count(*) as search_count')
			->find();
       		if ($tag_list->search_count<1) {
				$noSearched = true;
			}
		}else{
			$noSearched = true;
		}
        if ($noSearched) {                 // 判断是否出错
			$tag_list = $this->where('level',1)
			->field('count(*) as search_count')
			->find();
        }
		if($tag_list->search_count<1){
			return "";
		}
		$total_page = ceil($tag_list->search_count / $num);
		if($page > $total_page){$page = $total_page;}
		if($page <1){$page = 1;}
		if($noSearched){
			$tag_result = $this->where('level',1)
			->order('convert(tag using gbk)','asc')
			->limit($num*($page-1), $num)
			->select();
		}else{
			$tag_result = $this->where('tag','like','%'.$tag.'%')
			->order('convert(tag using gbk)','asc')
			->limit($num*($page-1), $num)
			->select();
		}
		$tmpStr = $noSearched."___".$total_page."___".$page."###";
		foreach($tag_result as $tag){
			$tmpTag = $this->where('parentid', $tag->id)
			->limit(1)
			->find();
			if($tmpTag){
				$tmpStr .= $tag->tag."___".$tag->id."___true$$$";
			}else{
				$tmpStr .= $tag->tag."___".$tag->id."___false$$$";
			}
		}
        return $tmpStr;   // 返回修改后的数据
	}

	public function getMoreTags($tagid, $num = 50, $page = 1){
		$tag_count = $this->where('parentid',$tagid)
		->field('count(*) as search_count')
		->find();
		if($tag_count->search_count<1){
            return "";
        }
		$total_page = ceil($tag_count->search_count / $num);
		if($page > $total_page){$page = $total_page;}
		if($page <1){$page = 1;}
		$tag_list = $this->where('parentid',$tagid)
		->order('convert(tag using gbk)','asc')
		->limit($num*($page-1), $num)
		->select();
		$tmpStr = $total_page."___".$page."###";
		foreach($tag_list as $tag){
			$tmpTag = $this->where('parentid', $tag->id)
			->limit(1)
			->find();
			if($tmpTag){
				$tmpStr .= $tag->tag."___".$tag->id."___true$$$";
			}else{
				$tmpStr .= $tag->tag."___".$tag->id."___false$$$";
			}
		}
        return $tmpStr;   // 返回修改后的数据
	}
}