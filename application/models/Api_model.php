<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Api_model extends MY_Model{
	//Register app user
    public function AddAccount($post_data){
		$this->db->select('*');
        $this->db->from('auth_accounts');
        $this->db->where('account_code', $post_data['account_code']);
		$this->db->where('account_key', $post_data['account_key']);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
			return FALSE;
		} else {
			$this->db->trans_start();
	        $this->db->insert('auth_accounts', $post_data);
	        $this->db->trans_complete();
	        if ($this->db->trans_status() === FALSE) {
	            return FALSE;
	        }
	        else {
	            return TRUE;
	        }
		}

    }

	//Save/update app user's device token
    public function save_device_token($post_data){
		$this->db->select('*');
        $this->db->from('device_tokens');
        $this->db->where('device_id', $post_data['device_id']);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
			$this->db->trans_start();
			$this->db->where('device_id', $post_data['device_id']);
	        $this->db->update('device_tokens', array('device_token' => $post_data['device_token']));
	        $this->db->trans_complete();
	        if ($this->db->trans_status() === FALSE) {
	            return FALSE;
	        }
	        else {
	            return TRUE;
	        }
		} else {
			$this->db->trans_start();
	        $this->db->insert('device_tokens', $post_data);
	        $this->db->trans_complete();
	        if ($this->db->trans_status() === FALSE) {
	            return FALSE;
	        }
	        else {
	            return TRUE;
	        }
		}

    }

	public function get_device_token($account_code, $email) {
			$this->db->select('d.device_token');
	        $this->db->from('device_tokens d');
			$this->db->join('auth_accounts a', 'd.device_id=a.device_id');
	        $this->db->where('a.account_key', $email);
			$this->db->where('a.account_code', $account_code);
	        $query = $this->db->get();
			if ($query->num_rows() == 1) {
				return $query->row();
			}
		}
	//Save/update app user's device location
    public function save_recent_device_location($post_data){
		$this->db->select('*');
        $this->db->from('device_locations');
        $this->db->where('device_id', $post_data['device_id']);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
			$this->db->trans_start();
			$this->db->where('device_id', $post_data['device_id']);
	        $this->db->update('device_locations', array('lat' => $post_data['lat'], 'lng' => $post_data['lng'], 'updated_on' => date('Y-m-d H:i:s')));
	        $this->db->trans_complete();
	        if ($this->db->trans_status() === FALSE) {
	            return FALSE;
	        }
	        else {
	            return TRUE;
	        }
		} else {
			$post_data['updated_on'] = date('Y-m-d H:i:s');
			$post_data['created_on'] = date('Y-m-d H:i:s');
			$this->db->trans_start();
	        $this->db->insert('device_locations', $post_data);
	        $this->db->trans_complete();
	        if ($this->db->trans_status() === FALSE) {
	            return FALSE;
	        }
	        else {
	            return TRUE;
	        }
		}

    }

	public function access_code($post_data) {
        $this->db->select('*');
        $this->db->from('auth_accounts');
        $this->db->where('account_key', $post_data['account_key']);
		$this->db->where('account_code', $post_data['account_code']);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            $result=$query->row_array();
            $user_data = $result;
			$this->db->trans_start();
			$this->db->where('account_key', $post_data['account_key']);
			$this->db->where('account_code', $post_data['account_code']);
			$this->db->update('auth_accounts', array('access_code' => $post_data['access_code']));
			$this->db->trans_complete();
			if($this->db->trans_status() === FALSE) {
				$return_data=array(
					'status'=>FALSE,
					'label'=>'ERROR',
				);
				return $return_data;
			} else {
				$return_data=array(
					'status'=>TRUE,
					'label'=>'SUCCESS',
					'data' =>$user_data
				);
				return $return_data;
			}

        } else {
            $return_data=array(
                'status'=>FALSE,
                'label'=>'INVALID',
            );
            return $return_data;
        }
    }


	//Get account list of user
	public function get_accounts_list($post_data){
		$this->db->select('id, account_name, account_key');
        $this->db->from('auth_accounts');
        $this->db->where('device_id', $post_data['device_id']);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
			$return['status'] = true;
			$return['message'] = "Fetched account list";
			$return['data'] = $query->result_array();
		} else {
			$return['status'] = false;
			$return['message'] = "No acount found";
		}

		return $return;

    }

	//otp validate
    public function otp_validate($user_id,$mail_activation_code){
        $this->db->select('account_key');
        $this->db->from('auth_accounts');
        $this->db->where('id', $user_id);
        $this->db->where('access_code', $mail_activation_code);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            $result=$query->row_array();
            $this->_table_name='auth_accounts';
            $this->_timestamps=TRUE;
            //update id
            $update_data=array(
                'access_code'=>'',
            );
            //update article
            $update_id=$this->save($data=$update_data, $id = $user_id);
            if($update_id){
                //if updated
                $return_data=array(
                    'status'=>TRUE,
                    'label'=>'SUCCESS',
                    'user'=>$result
                );
                return $return_data;
            }else{
                //if not updated
                $return_data=array(
                    'status'=>FALSE,
                    'label'=>'ERROR',
                );
                return $return_data;
            }
        }else{
                //If invalid activation code
                $return_data=array(
                    'status'=>FALSE,
                    'label'=>'EMAIL_INVALID',
                );
                return $return_data;
        }
    }

	public function device_location($user_id) {
		$this->db->select('auth_accounts.account_key, auth_accounts.id, device_locations.lat, device_locations.lng');
		$this->db->from('auth_accounts');
		$this->db->join('device_locations', 'auth_accounts.device_id = device_locations.device_id')	;
		$this->db->where('auth_accounts.id', $user_id);
		$query = $this->db->get();
        if ($query->num_rows() == 1) {
            $result=$query->row_array();
			$return_data=array(
				'status'=>TRUE,
				'label'=>'SUCCESS',
				'data' =>$result
			);
			return $return_data;
        } else {
            $return_data=array(
                'status'=>FALSE,
                'label'=>'INVALID',
            );
            return $return_data;
        }
    }

	public function device_distance($user_id, $lat, $lng) {
		$sql = "SELECT id, ( 6371 * acos( cos( radians($lat) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians($lng) ) + sin( radians($lat) ) * sin( radians( latitude ) ) ) ) AS distance FROM auth_accounts WHERE id = $user_id HAVING distance < 5 ORDER BY distance LIMIT 0 , 20";
		$query = $this->db->query($sql);
        if ($query->num_rows() == 1) {
            $result=$query->row_array();
			$return_data=array(
				'status'=>TRUE,
				'label'=>'',
				'data' => $result
			);
        } else {
            $return_data=array(
                'status'=>FALSE,
                'label'=>'INVALID',
            );
        }
		return $return_data;
    }


	//location validate
    public function location_code_validate($user_id,$location_code){
        $this->db->select('account_key');
        $this->db->from('auth_accounts');
        $this->db->where('id', $user_id);
        $this->db->where('location_name', $location_code);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            $result=$query->row_array();

			$return_data=array(
				'status'=>TRUE,
				'label'=>'SUCCESS',
				'user'=>$result
			);
                return $return_data;
            }else{
                //if not updated
                $return_data=array(
                    'status'=>FALSE,
                    'label'=>'ERROR',
                );
                return $return_data;
            }
        }


}
