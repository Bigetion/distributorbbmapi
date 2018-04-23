<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');

class Params extends Main {
    function __construct() {
        $this->auth->permission();
    }

    function getParams() {
        $data = array();
        $a = load_file('project');
		if(count($a)>0){
        foreach ($a as $value) {
            if ($value != '.' && $value != '..') {
                $data['project'][] = $value;
                $b = load_file('project/' . $value);
                if (in_array('controllers', $b)) {
                    $b = load_recursive('project/' . $value . '/controllers');
					if(count($b)>0){ 
                        foreach ($b as $value2) {
                            require($value2);
                            $c = get_class_methods(substr(basename($value2), 0, -4));
                            if(count($c)>0){ 
                                foreach ($c as $value3) {
                                    if ($value3 != '__construct' && $value3 != '__get') {
                                        $data['function'][substr(basename($value2), 0, -4)][] = $value3;
                                        
                                        $module = $value;
                                        $function = substr(basename($value2), 0, -4);
                                        $this->dir->create_dir('project/'.$module.'/params');
                                        if(!file_exists('project/'.$module.'/params/'.$function.'.json')){
                                            $json_data = json_encode(array());
                                            file_put_contents('project/'.$module.'/params/'.$function.'.json', $json_data);
                                        }
                                        $data['params'][$module][$function] = json_decode(file_get_contents('project/'.$module.'/params/'.$function.'.json'), true);
                                    }
                                }
                            }
                            $data['controller'][$value][] = substr(basename($value2), 0, -4);
                        }
                    }
                } else $data['controller'][$value][] = ' ----- ';
            }
        }}
        if (empty($data)) $data[] = '-----';
        $this->render->json($data);
    }

    function updateParams(){
        $post_data = $this->render->json_post();
        $this->dir->create_dir('project/'.$post_data['module'].'/params');
        if(file_put_contents('project/'.$post_data['module'].'/params/'.$post_data['controller'].'.json', json_encode($post_data['validation']))){
            $this->set->success_message(true);
        }
        $this->set->error_message(true);
    }
}    
?>