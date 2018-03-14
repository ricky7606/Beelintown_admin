<?php
namespace app\index\controller;
use think\Controller;//引入Controller类
use think\Session;
use app\index\model\Ads;
use app\index\model\Report;
use app\index\model\Transactions;
use app\index\model\QnasReplyDetails;
use think\Db;
use think\Request;
use think\Cookie;
use Qiniu\Auth as Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use app\index\model\AdminLogs;

class Adslist extends Controller
{
    public function index()
    {
		if(!Cookie::has('adminid')){
			return $this->redirect('/index');
		}

		$report = new Report;
		$report_count = $report->getPendingReportCount();
		$this->assign('pending_report_count',$report_count->report_count);
		$trans = new Transactions;
		$trans_count = $trans->getPendingExtractCount();
		$this->assign('pending_extract_count',$trans_count->extract_count);
		$arbitrate = new QnasReplyDetails;
		$arbitrate_count = $arbitrate->getPendingArbitrateCount();
		$this->assign('pending_arbitrate_count',$arbitrate_count->arbitrate_count);
		$this->assign('total_pending_count',$arbitrate_count->arbitrate_count+$trans_count->extract_count+$report_count->report_count);
		
		$ads = new Ads;
		$ads_list = $ads->getAdsList();
		$this->assign('ads_list',$ads_list);
		$this->assign('admin_type',Cookie::get('admin_type'));
		$this->assign('admin_realname',Cookie::get('admin_realname'));
        return $this->fetch();
    }
	
    public function details()
    {
		if(!Cookie::has('adminid')){
			return $this->redirect('/index');
		}

		$report = new Report;
		$report_count = $report->getPendingReportCount();
		$this->assign('pending_report_count',$report_count->report_count);
		$trans = new Transactions;
		$trans_count = $trans->getPendingExtractCount();
		$this->assign('pending_extract_count',$trans_count->extract_count);
		$arbitrate = new QnasReplyDetails;
		$arbitrate_count = $arbitrate->getPendingArbitrateCount();
		$this->assign('pending_arbitrate_count',$arbitrate_count->arbitrate_count);
		$this->assign('total_pending_count',$arbitrate_count->arbitrate_count+$trans_count->extract_count+$report_count->report_count);
		
		$ads_id = Request::instance()->get('id');
		$ads_name = '';
		$ads_image = '';
		$ads_link = '';
		$position = '';
		$new_window = '';
		$clicks = '';
		$enable = '';
		if($ads_id != ''){
			$ads = new Ads;
			$detail = $ads->getAdDetails($ads_id);
			if($detail){
				$ads_name = $detail->ads_name;
				$ads_image = $detail->ads_image;
				$ads_link = $detail->ads_link;
				$position = $detail->position;
				$new_window = $detail->new_window;
				$clicks = $detail->clicks;
				$enable= $detail->enable;
			}
		}
		$this->assign('ads_id',$ads_id);
		$this->assign('ads_name',$ads_name);
		$this->assign('ads_image',$ads_image);
		$this->assign('ads_link',$ads_link);
		$this->assign('position',$position);
		$this->assign('new_window',$new_window);
		$this->assign('clicks',$clicks);
		$this->assign('enable',$enable);
		$this->assign('admin_type',Cookie::get('admin_type'));
		$this->assign('admin_realname',Cookie::get('admin_realname'));
        return $this->fetch('details');
    }
	
	//上传照片到七牛云
    public function uploadPic()
    {
		if(!Cookie::has('adminid')){
			return $this->redirect('/index');
		}
		require_once APP_PATH . '/../vendor/qiniusdk/autoload.php';
		
		//获取文件后缀  
		function getExt($file) {  
		$tmp = explode('.',$file);  
		return strtolower(end($tmp));  
		}  
		//随机随机文件名  
		function randName() {  
		$str = 'abcdefghijkmnpqrstwxyz23456789';  
		return substr(str_shuffle($str),0,16);  
		}  

		$accessKey = 'Z4wgnwBl_94hiUth4VEgUiVaj0KQntxR7ZNGR19d';  
		$secretKey = 'SL_tEi0tauni6lvsFD894L62HlrGjTk7Qny8mQh5';  
		$bucket = 'images';  
		// 构建鉴权对象
		$auth = new Auth($accessKey, $secretKey);
		// 生成上传 Token
		$token = $auth->uploadToken($bucket);

		$name=$_FILES['upFiles']['name'];
		$filePath=$_FILES['upFiles']['tmp_name'];
		
		// 初始化 UploadManager 对象并进行文件的上传。
		$uploadMgr = new UploadManager();

		$type = strtolower(substr($name,strrpos($name,'.')+1));//得到文件类型，并且都转化成小写
		$allow_type = array('jpg','jpeg','gif','png','bmp'); //定义允许上传的类型
		//把非法格式的图片去除
		if (!in_array($type,$allow_type)){
			return false;;
		}
		$newname = randName().".".$type;  //新文件名
		// 调用 UploadManager 的 putFile 方法进行文件的上传。
		list($ret, $err) = $uploadMgr->putFile($token, $newname, $filePath);
		$tmpStr="http://images.beelintown.com.cn/$newname";
		$tmpStr .= "-ads_right";

		return $tmpStr;
	}

	public function update_ads()
	{
		if(!Cookie::has('adminid')){
			return $this->redirect('/index');
		}
		$ads_id = Request::instance()->post('ads_id');
		$ads_name = Request::instance()->post('ads_name');
		$ads_link = Request::instance()->post('ads_link');
		$position = Request::instance()->post('position');
		$ads_image = Request::instance()->post('ads_image');
		$new_window = Request::instance()->post('new_window');
		$enable = Request::instance()->post('enable');
		$ads = new Ads;
		if($ads_id != ''){
			$result = $ads->updateAd($ads_id, $ads_name, $ads_link, $position, $ads_image, $new_window, $enable);
			if($result == "ok"){
				$log = new AdminLogs;
				$ip = Request::instance()->ip();
				$log->saveLog(Cookie::get('adminid'),$ip,5,"更新广告，参考ID：".$ads_id);
			}
			return $result;
		}else{
			$result = $ads->createAd($ads_name, $ads_link, $position, $ads_image, $new_window, $enable);
			if($result == "ok"){
				$log = new AdminLogs;
				$ip = Request::instance()->ip();
				$log->saveLog(Cookie::get('adminid'),$ip,5,"创建广告，参考ID：".$ads_id);
			}
			return $result;
		}
	}
}