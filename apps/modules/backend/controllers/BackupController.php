<?php
namespace  Multiple\Backend\Controllers;
use Multiple\Components\Base;
/**
 * Class BackupController
 * @package Multiple\Backend\Controllers
 * 备份数据
 */
class BackupController extends Base{
	public function indexAction(){
		$path = DATABASE_BACKUP;

		$handle = opendir($path);
		$arr = array();
		while($file = readdir($handle)){
			if($file!='.'&&$file!='..'){
				$info['file'] = $file;
				$info['size'] = sprintf("%0.1f",(filesize($path.$file))/(1024));
				$time = explode('_',$file);
				$info['time'] = $time[0].'-'.$time[1].'-'.$time[2].'  '.$time[3].":".$time[4];
				$arr[] = $info;
			}
		}

		$this->view->setVar('file',$arr);
	}

	public function createAction(){
		$path = DATABASE_BACKUP;
		$tableList = $this->db->listTables (DATABASE_NAME);
		$mysql='';
		foreach($tableList as $tableName){
			$result = $this->db->fetchOne("SHOW CREATE TABLE ".$tableName,\Phalcon\Db::FETCH_ASSOC);
			$mysql .= "DROP TABLE "."`".$result['Table']."`".";\r\n";
			$mysql .= $result['Create Table'].";\r\n";
			$result = $this->db->fetchAll("select * from ".$tableName,\Phalcon\Db::FETCH_ASSOC);
			if(count($result)>0){
				foreach($result as $k=>$item){
					if($k==0){
						$keys=array_keys($item);
						$keys=array_map('addslashes', $keys);
						$keys=join('`,`',$keys);
						$keys="`".$keys."`";
						$mysql.="insert into `$tableName`($keys) values";
					}
					$vals=array_values($item);
					$vals=array_map('addslashes',$vals);
					$vals=join("','",$vals);
					$vals="'".$vals."'";
					 $mysql.="($vals),";
				}
				$mysql=rtrim($mysql,',');
				$mysql.=";\r\n";
			}
		}
		file_put_contents($path.date('Y_m_d_H_i_s',time()).'.sql', $mysql."\r\n",FILE_APPEND);
		return $this->sendJson(array('status'=>1,'location'=>'/admin/backup/index'));
	}
}