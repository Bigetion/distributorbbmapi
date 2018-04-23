<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');

class Auth {
    function permission() {
        if(get_header('Authorization')){
            $authorization_header = get_header('Authorization');
            $bearer_header_list = explode('Bearer',get_header('Authorization'));
            $bearer_pos = stripos($authorization_header, 'bearer ');
            if ($authorization_header !== false && ($bearer_pos !== false)) {
                $authorization_header = substr($authorization_header,7);
                define('Bearer',$authorization_header);

                $db = & load_class('DB');
                $jwt = & load_class('JWT');
                
                $jwt_payload = $jwt->decode(Bearer, base64_decode(secret_key));
                $payload = json_decode(json_encode($jwt_payload), true);

                $header_origin_payload = $payload['iss'];
                $header_origin = get_header('origin');

                if($header_origin_payload == $header_origin){
                    $username = $payload['data']['user'];

                    $data = $db->query("select * from users where username = '$username'")->fetchAll();
                    $id_role = $data[0]['id_role'];

                    if($id_role!=1){
                        show_error('Authentication','Please login to access this page');
                    }

                    if (!defined('id_role')) define('id_role', $id_role);
                }else{
                    show_error('Permission','Origin unauthorized');
                }
            }else{
                define('Bearer',false);
            }
        }else{
            define('Bearer',false);
        }

        if(Bearer == false){
            show_error('Authentication', 'Bearer undefined');
        }
    }
}

?>