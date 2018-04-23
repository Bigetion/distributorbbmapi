<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');
class URI {
	function __construct()
	{	
		$main_config = include('application/config/config.php');
        foreach ($main_config as $key => $value) {
            define($key, $value);
        }
		$database_config = include('application/config/database.php');
        foreach ($database_config as $key => $value) {
            define($key, $value);
        }
	}
	
    function segment($nomor){
		$db = & load_class('DB');		
		$uri_base = explode('?', (isset($_SERVER['HTTPS']) ? "https" : "http")."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
		$uri_link = explode('/',$uri_base[0]);
		$uri_new = $uri_base[0];
		
		$ext = array(".html", ".aspx", ".asp");
		
		$tabel = $db->get_table();
		if(in_array('short_link', $tabel)){
			for($i=0;$i<count($uri_link);$i++){
				$link = $db->query("select * from short_link where short_link='".str_replace($ext, '', $uri_link[$i])."'")->fetchAll();
				if(count($link)>0) {
					$link = $link[0];
					$uri_new = str_replace($uri_link[$i],$link["link"],$uri_new);
				}
			}
		}
		
		$uri_new = str_replace(base_url,'', $uri_new);
		$data = explode('/', $uri_new);
		if ($nomor > count($data)) return '';
		else return str_replace($ext, '', $data[$nomor-1]);
	}
	
	function subsegment($from=-1, $to=0){
		$uri_base = explode('?', $_SERVER['REQUEST_URI']);
		$uri_link = explode('/',$uri_base[0]);
		$count =  count($uri_link);
		$segment = '';
		if($from<0 || $from>$count){
			$segment = $uri_link[$count-1];
		}else if($from==$to) {
			$segment = $this->segment($from);
		}else if($count>=$to){
			if($to==0) $to = $count;
			if($to<0) $to = $count + $to;
			for($i=$from;$i<$to;$i++){
				$segment .= $uri_link[$i].'/';
			}
			$segment = substr($segment, 0, -1);
		}
		return $segment;
	}
}
?>