<?php
/*
 * This is helper file to send push notification using google firebase
 */

// function to send notification
function send_notification($device_token, $notificationArr = array()) {
    $ci =& get_instance();
    if($device_token != '') {
		$acccess_token = $ci->config->item('firebase_access_token');

		// FIREBASE_API_KEY_FOR_ANDROID_NOTIFICATION
        $headers = array('Authorization: Bearer '. $acccess_token, 'Content-Type: application/json');
		$msg = array(
            'body'              => (isset($notificationArr['notification_text']) && $notificationArr['notification_text']) ? $notificationArr['notification_text'] : 'Notification',
            'title'             => (isset($notificationArr['notification_title'])) ? $notificationArr['notification_title'] : 'Notification',

        );
		$access_code = $notificationArr['access_code'];


		// using tokens
        $fields = array('message' => array('token' => $device_token,
		'notification'=> $msg, 'data' => array('access_code' => "$access_code")));


        // Open connection
        $ch = curl_init();
        // Set the url, number of POST vars, POST data
        $curl_url = $ci->config->item('curl_url');
		curl_setopt($ch,CURLOPT_URL, $curl_url);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($fields));
        // Execute post
        $result = curl_exec($ch);

        if($result === false){ die('Curl failed:' .curl_errno($ch)); }
        // Close connection
        curl_close($ch);
        return $result;

    }
}

?>
