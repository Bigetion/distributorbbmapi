<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');

class Login extends Main {

    function index(){
        $post_data = $this->render->json_post();
		$this->gump->validation_rules(array(
			'username'    		=> 'required|alpha_numeric',
			'password'    		=> 'required',
		));

		$this->gump->filter_rules(array(
			'username' 			=> 'trim|sanitize_string',
			'password' 			=> 'trim',
		));
        $this->gump->run_validation($post_data);
        
        $user = strtolower($post_data['username']);
        $password = $post_data['password'];

        function random_string($length = 10) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }

        if (empty($user)|| empty($password))
            $this->set->error_message(true);
            
        $data = $this->db->select("users","*",["username"=>$user]);
        if (count($data) == 0)
            $this->set->error_message(true);
        else {
            if(!password_verify($password,$data[0]["password"])) $this->set->error_message(true);
            else if($data[0]["id_role"]==2) $this->set->error_message(true);
            else{
                try{
                    $payload = array(
                        'jti'       => random_string(),
                        'iat'       => time(),
                        'nbf'       => time() + 10,
                        'exp'       => time() + 7210,
                        'iss'       => get_header('origin'),
                        'data'      => array(
                                    'user'  => strtolower($user),
                                    )
                    );
                    $jwtTokenEncode = $this->jwt->encode($payload, base64_decode(secret_key));

                    $token['jwt'] = $jwtTokenEncode;
                    
                    $this->set->success_message(true, $token);
                }
                catch(Exception $ex){
                    $this->set->error_message(true, $ex);
                }
            }
        }
    }
}

?>