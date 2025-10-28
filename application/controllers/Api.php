<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends MY_Controller {
    public function __construct()
    {
        parent::__construct();
        //Load Libraries
        $this->load->helper('push_notification');
        //pagination settings
        //load models
        $this->load->model(array('api_model'));

    }
	//Add new Account
	public function add_account() {
		$result;
		$post_data = json_decode(file_get_contents('php://input'), true);
		if (is_array($post_data) && empty($post_data)) {
			$result = array('status' => false, 'message' => $this->lang->line("error_empty_data"));
		} else {
			$account_name = $post_data['accountName'];
			$account_code = strtolower(preg_replace('/\s+/', '_', $account_name));
			$email = $post_data['email'];
			$device_id = $post_data['deviceId'];
			$lat = $post_data['latitude'];
			$lng = $post_data['longitude'];
			$location_name = $post_data['locationName'];
			$register_data = array(
								'account_name' 	=> $account_name,
								'account_key'	=> $email,
								'latitude'		=> $lat,
								'longitude'		=> $lng,
								'location_name' => $location_name,
								'device_id'		=> $device_id,
								'account_code'	=> $account_code,
								'created_on'	=> date('Y-m-d h:i:s'),
								'updated_on'	=> date('Y-m-d h:i:s')
							);
			$response = $this->api_model->AddAccount($register_data);
			if ($response) {
				$result = array('status' => true, 'message' => $this->lang->line('success_register'));
			} else {
				$result = array('status' => false, 'message' => $this->lang->line('error_database'));
			}
		}
		echo json_encode($result);
	}

	public function device_token() {
		$result;
		$post_data = json_decode(file_get_contents('php://input'), true);
		if (is_array($post_data) && empty($post_data)) {
			$result = array('status' => false, 'message' => $this->lang->line("error_empty_data"));
		} else {
			$deviceId = $post_data['deviceId'];
			$deviceToken = $post_data['deviceToken'];
			$deviceType = $post_data['deviceType'];
			$data = array(
						'device_id' 	=> $deviceId,
						'device_type' 	=> $deviceType,
						'device_token'	=> $deviceToken,
					);
			$response = $this->api_model->save_device_token($data);
			if ($response) {
				$result = array('status' => true, 'message' => $this->lang->line("success_save"));
			} else {
				$result = array('status' => false, 'message' => $this->lang->line("error_database"));
			}
		}
		echo json_encode($result);
	}
	public function send_device_location() {
		$post_data = json_decode(file_get_contents('php://input'), true);
		if (is_array($post_data) && empty($post_data)) {
			$result = array('status' => false, 'message' => $this->lang->line("error_empty_data"));
		} else {
			$device_id = $post_data['device_id'];
			$lat = $post_data['lat'];
			$lng = $post_data['lng'];
			$data = array(
						'device_id' => $device_id,
						'lat' 		=> $lat,
						'lng'		=> $lng,
					);
			$response = $this->api_model->save_recent_device_location($data);
			if ($response) {
				$result = array('status' => true, 'message' => $this->lang->line("success_save"));
			} else {
				$result = array('status' => false, 'message' => $this->lang->line("error_database"));
			}
		}
		echo json_encode($result);
	}

	public function get_accounts_list() {
		$post_data = json_decode(file_get_contents('php://input'), true);
		if (is_array($post_data) && empty($post_data)) {
			$result = array('status' => false, 'message' => $this->lang->line("error_empty_data"));
		} else {
			$data = array('device_id' => $post_data['deviceId']);
			$response = $this->api_model->get_accounts_list($data);
			if ($response['status']) {
				$result = array('status' => true, 'message' => $response['message'], 'data' => $response['data']);
			} else {
				$result = array('status' => false, 'message' => $response['message']);
			}
		}
		echo json_encode($result, JSON_NUMERIC_CHECK);
	}


    //Get activation code
    public function send_activation_code() {
         if($this->input->post()){
            $this->form_validation->set_rules('email','Email','trim|required|valid_email');

            if ($this->form_validation->run() == FALSE) {
                $success = FALSE;
                $message = validation_errors();

            } else {
                $activation_code = mt_rand(100000, 999999);
                $post_data = array(
                    'account_key'           => $this->input->post('email'),
					'account_code'			=> $this->input->post('account_code'),
                    'access_code'			=> $activation_code
                );

                $post_data = $this->security->xss_clean($post_data);
                $result = $this->api_model->access_code($post_data);
                if ($result['status']==TRUE &&$result['label']=='SUCCESS') {
					$device_token_result = $this->api_model->get_device_token($this->input->post('account_code'), $this->input->post('email'));
					$notificationArr = array(
	                    'access_code'   		=> $activation_code,
	                    'notification_text' 	=> $this->lang->line("text_access_code_notification"),
	                    'notification_title' 	=> $this->lang->line("text_access_code_notification_title"),
                	);
					$results = send_notification($device_token_result->device_token, $notificationArr);

                    $success = TRUE;
                    $user_id = $result['data']['id'];
					$account_key = $result['data']['account_key'];
                    $message = $this->lang->line("alert_send_otp_success");

                }elseif($result['status']==FALSE &&$result['label']=='INACTIVE'){
                    $success = FALSE;
                    $user_id = NULL;
					$account_key = NULL;
                    $message = $this->lang->line("alert_login_inactive");
                }elseif($result['status']==FALSE &&$result['label']=='ERROR'){
                    $success = FALSE;
                    $user_id = NULL;
					$account_key = NULL;
                    $message = $this->lang->line("alert_login_invalid");
                }elseif($result['status']==FALSE &&$result['label']=='BLOCKED'){
                    $success = FALSE;
                    $user_id = NULL;
					$account_key = NULL;
                    $message = $this->lang->line("alert_user_blocked");
                }elseif($result['status']==FALSE &&$result['label']=='INVALID'){
                    $success = FALSE;
                    $user_id = NULL;
					$account_key = NULL;
                    $message = $this->lang->line("alert_user_notfound");
                }
            }
         }
        $json_array = array('success' => $success, 'message' => $message, 'user_id' => $user_id, 'email' => $account_key);
        echo json_encode($json_array);
        exit();
    }

	// Fetch device recent location
	public function device_location() {
		$data = "";
	        if($this->input->post()){
	            $this->form_validation->set_rules('user_id','User','trim|required' );
				$data = "";
	            if ($this->form_validation->run() == FALSE) {
	                $success = FALSE;
	                $message = validation_errors();
	            } else {
	                $result = $this->api_model->device_location($this->input->post('user_id'));

	                if ($result['status']==TRUE &&$result['label']=='SUCCESS') {

	                    $data = $result['data'];
						$lat = $data['lat'];
						$lng = $data['lng'];
						$distance_result = $this->api_model->device_distance($this->input->post('user_id'), $lat, $lng);
						if ($distance_result['status']) {
							$distance  = $distance_result['data']['distance']*1000;
							if ($distance <= 100) {
								$success = TRUE;
								$message = $this->lang->line("text_success");
								$data = $distance;
							} else {
								$success = FALSE;
								$message = $this->lang->line("alert_device_location_mismatch");
							}
						} else {
							$success = FALSE;
							$message = $this->lang->line("alert_device_location_mismatch");
						}
	                } elseif ($result['status']==FALSE &&$result['label']=='INVALID'){
	                    $success = FALSE;
	                    $message = $this->lang->line("alert_user_notfound");
	                } else {
						$success = FALSE;
	                    $message = $this->lang->line("error_invalidata");
					}
	            }
	        }
	        $json_array = array('success' => $success, 'message' => $message, 'data' => $data);
	        echo json_encode($json_array);
	}


    //update login info
    public function update_login_info($user_id){
        $update_data = array(
            'login_ip'              => $this->get_user_ip(),
            'login_agent'           => $this->get_user_agent(),
            'last_login'            => date('Y-m-d H:i:s'),
            'sms_activation_code'   => ''
        );
        $this->auth_model->update_login_info($user_id,$update_data);
    }

		//location code Validation
	 	    public function location_code_validate($user_id=NULL){
	 	        if($user_id!=NULL){
	 	            if($this->input->post()){
						$account_key = NULL;
	 	                $this->form_validation->set_rules('location_activation_code','Location Code','trim|required');

	 	                if ($this->form_validation->run() == FALSE) {
	 	                    $success = FALSE;
	 	                    $message = validation_errors();

	 	                }else{
	 	                    $location_activation_code=$this->input->post('location_activation_code');
	 	                    $user_id=$this->input->post('user_id');

	 	                    $post_data = array();

	 	                    $result = $this->api_model->location_code_validate($user_id, $location_activation_code);
	 	                    //echo "<pre>";print_r($result);die;
	 	                    if ($result['status']==TRUE &&$result['label']=='SUCCESS') {
								$account_key = $result['user']['account_key'];
	 	                        $success = TRUE;
	 	                        $message = $this->lang->line("alert_login_success");
	 	                    }elseif($result['status']==FALSE &&$result['label']=='ERROR'){
	 	                        $success = FALSE;
	 	                        $message = $this->lang->line("alert_login_invalid");
	 	                    }elseif($result['status']==FALSE &&$result['label']=='EMAIL_INVALID'){
	 	                        $success = FALSE;
	 	                        $message = $this->lang->line("alert_invalid_otp");
	 	                    }
	 	                }
	 	                $json_array = array('success' => $success, 'message' => $message, 'account_key' => $account_key);
	 	                echo json_encode($json_array);
	 	                exit();
	 	            }

	 	        }else{

	 	        }
	 	    }

    //otp Validation
    public function otp_validate($user_id=NULL){
        if($user_id!=NULL){
            if($this->input->post()){
                $this->form_validation->set_rules('mail_activation_code','OTP','trim|required');
				$account_key = NULL;
                if ($this->form_validation->run() == FALSE) {
                    $success = FALSE;
                    $message = validation_errors();

                }else{
                    $mail_activation_code=$this->input->post('mail_activation_code');
                    $user_id=$this->input->post('user_id');

                    $post_data = array();

                    $result = $this->api_model->otp_validate($user_id, $mail_activation_code);
                
                    if ($result['status']==TRUE &&$result['label']=='SUCCESS') {
                        $account_key = $result['user']['account_key'];
                        $success = TRUE;
                        $message = $this->lang->line("alert_login_success");
                    }elseif($result['status']==FALSE &&$result['label']=='ERROR'){
                        $success = FALSE;
                        $message = $this->lang->line("alert_login_invalid");
                    }elseif($result['status']==FALSE &&$result['label']=='EMAIL_INVALID'){
                        $success = FALSE;
                        $message = $this->lang->line("alert_invalid_otp");
                    }
                }
                $json_array = array('success' => $success, 'message' => $message, 'account_key' => $account_key);
                echo json_encode($json_array);
                exit();
            }
        }
    }
}
