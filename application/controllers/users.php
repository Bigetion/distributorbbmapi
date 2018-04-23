<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');

class Users extends Main {
    function __construct() {
        $this->auth->permission();
    }

    function getData(){
        $data['data'] = $this->db->select("users",[
            "[>]roles" => "id_role"
        ],[
            "users.id_user", "users.id_role","users.username", "roles.role_name"
        ]);
        $this->render->json($data);
    }

    function submitAdd(){
        $post_data = $this->render->json_post();
        $data = array(
            'username'  => $post_data['userName'],
            'id_role'   => $post_data['idRole'],
        );
        if($this->db->insert("users", $data)){
            $id = $this->db->id();
            $this->set->success_message(true, array('id'=>$id));
        }
    }

    function submitEdit(){
        $post_data = $this->render->json_post();
        $data = array(
            'username'     => $post_data['userName'],
            'id_role'   => $post_data['idRole'],
        );
        if($this->db->update("users", $data, ["id_user" => $post_data['idUser']])){
            $this->set->success_message(true);
        }
    }

    function submitDelete(){
        $post_data = $this->render->json_post();
        if($this->db->delete("users", ["id_user" => explode(',',$post_data['idUser'])])){
            $this->set->success_message(true);
        }
    }
}    
?>