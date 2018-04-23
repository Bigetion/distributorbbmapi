<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');

class Permissions extends Main {
    function __construct() {
        $this->auth->permission();
    }

    function getPermissions() {
        $data['data'] = $this->db->select("roles","*");
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
                                    if ($value3 != '__construct' && $value3 != '__get') $data['function'][substr(basename($value2), 0, -4)][] = $value3;
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

    function updatePermissions(){
        $post_data = $this->render->json_post();
        $permissions = $post_data['permissions'];
        $data['data'] = '';
        foreach ($permissions as $key => $val) {
            if($key==1) {
                $this->db->update("roles",["permission"=>''],["id_role"=>$key]);
            }
            else {
                $this->db->update("roles",["permission"=>$val],["id_role"=>$key]);
            }
        }
        $this->set->success_message(true);
    }
}    
?>