<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');

class Crypt {
    function encrypt($str,$ky='mysecretkey') {
        $ky = str_replace(chr(32), '', $ky);
        if (strlen($ky) < 8)
            exit('key minimal 8 character');
        $kl = strlen($ky) < 32 ? strlen($ky) : 32;
        $k = array();
        for ($i = 0; $i < $kl; $i++) {
            $k[$i] = ord($ky{$i}) & 0x1F;
        }
        $j = 0;
        for ($i = 0; $i < strlen($str); $i++) {
            $e = ord($str{$i});
            $str{$i} = $e & 0xE0 ? chr($e ^ $k[$j]) : chr($e);
            $j++;
            $j = $j == $kl ? 0 : $j;
        }
        return $str;
    }

    function decrypt($str,$ky='mysecretkey') {
        $ky = str_replace(chr(32), '', $ky);
        if (strlen($ky) < 8)
            exit('key minimal 8 character');
        $kl = strlen($ky) < 32 ? strlen($ky) : 32;
        $k = array();
        for ($i = 0; $i < $kl; $i++) {
            $k[$i] = ord($ky{$i}) & 0x1F;
        }
        $j = 0;
        for ($i = 0; $i < strlen($str); $i++) {
            $e = ord($str{$i});
            $str{$i} = $e & 0xE0 ? chr($e ^ $k[$j]) : chr($e);
            $j++;
            $j = $j == $kl ? 0 : $j;
        }
        return $str;
    }
	
	private function get_ord($str){
		$arr = str_split($str);
		
		$ord = array();
		
		foreach($arr as $val){
			$ord[] = ord($val);	
		}
		
		return $ord;
	}

	private function get_key_session($str){
		$key = implode("",$this->get_ord($str));
		$x = strlen($key) % 2;
		if($x > 0){
			$key .= substr($key, 0, 2-$x);
		}
		$s = strlen($key)/2;
		$t = array();
		for($i=0;$i<$s;$i++){
			$t[] = intval(substr($key,$i*2,2));
		}
		return $t;
	}

	private function get_key($str){
		$k2xrk = array();
		while(count($k2xrk)<128){
			$rk = $this->get_ord($str);
			$k = array();
			$k2 = array();
			$k2xrk = array();
			for($i=count($rk)-1;$i>=0;$i--){
				$k[] = $rk[$i];
			}

			$sum_k = 0;
			for($i=0;$i<count($k);$i++){
				$sum_k += ($k[$i] * ($i+1));
			}

			$mod_sum = $sum_k % count($k);
			for($i=0;$i<count($k);$i++){
				$k2[] = ($mod_sum+$k[$i]);
			}

			for($i=0;$i<count($k);$i++){
				$k2xrk[] = $k2[$i] * $rk[$i] * ($i+1);
			}
			$str = implode("",$k2xrk);
		}
		return $k2xrk;
	}
	
	private function acm($p,$q,$maks_offset){
		$arr_acm = array();	
		$dimensi = 1;
		while (($dimensi * $dimensi) < $maks_offset){
			$dimensi++;
		}
				
		for($i=0;$i<$dimensi;$i++){
			for($j=0;$j<$dimensi;$j++){
				$s = ($dimensi*$i)+$j;
					
				$rx = ((1*$i)+($p*$j))%$dimensi;
				$ry = (($q*$i)+($p*$q+1)*$j)%$dimensi;
				
				$rs = ($dimensi*$rx)+$ry;
				if($rs<=$maks_offset && $rs>0) $arr_acm[] = $rs-1;
			}
		}	
		return $arr_acm;	
	}
	
	function rand_encrypt($str, $key, $p=8279, $q=6371){
	
		$str = str_pad($str,10," ");
		
		$arr_str = $this->get_ord($str);
		$arr_key = $this->get_key($key);
		
		$arr_result = array();
		$arr_acm = $this->acm($p,$q,strlen($str)*2);
	
		$arr_result_char = array();
		for($i=0;$i<count($arr_str);$i++){
			$j = $i*2;
			$rand = rand();
				
			$c1 = ($arr_key[$i%count($arr_key)] + (2*$arr_str[$i]) + $rand) % 127;
			$c2 = ((2*$arr_key[$i%count($arr_key)]) + $arr_str[$i] + $rand) % 127;
			
			$arr_result[$arr_acm[$j]] = $c1;
			$arr_result[$arr_acm[$j+1]] = $c2;
			
			$arr_result_char[$arr_acm[$j]] = chr($c1);
			$arr_result_char[$arr_acm[$j+1]] = chr($c2);
		}	
		ksort($arr_result_char);
		$str_result_char = base64_encode(implode("",$arr_result_char));
			
		return $str_result_char;
	}
	
	function rand_decrypt($str, $key, $p=8279, $q=6371){
		$arr_tmp_result_char = $this->get_ord(base64_decode($str));
		$arr_key = $this->get_key($key);
		
		$arr_acm = $this->acm($p,$q,count($arr_tmp_result_char));
		
		$result = "";
		for($i=0;$i<(count($arr_tmp_result_char)/2);$i++){
			$j = $i*2;
			
			if(isset($arr_acm[$j+1]) && isset($arr_acm[$j])){
				$c1 = $arr_tmp_result_char[$arr_acm[$j]];
				$c2 = $arr_tmp_result_char[$arr_acm[$j+1]];
				
				$r = (($c1-$arr_key[$i%count($arr_key)] % 127))-(($c2-(2*$arr_key[$i%count($arr_key)])) % 127);
				if($r<0) $r+=127;
				else if($r>127) $r-=127;
				
				$result .= chr($r);
			}
		}
		if(strlen($result)==10) $result = trim($result);
		return $result;
	}

}

?>
