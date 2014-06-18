<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
	
	// require APPPATH . '/libraries/AfricasTalkingGateway.php';
class CoreScripts {
	public function __construct() {
		$this->CI ()->load->library ( 'curl' );
		// $this->CI ()->load->model ( 'Transactions_Model', 'transactions' );
		//$this->CI ()->load->model ( 'Member_Model', 'members' );
	}
	public function CI() {
		$CI = & get_instance ();
		return $CI;
	}
	
	function updateCustomer($newMobile) {
		// updating customer Record
		$cust = array (
				'newMobile' => $newMobile 
		);
		// updating customer
		if (strlen ( $cust ['newMobile'] ) == 10) { // 0729472421
			$newInput = array (
					'phone' => $cust ['newMobile'] 
			);
			$this->CI ()->customers->UpdateCustomer ( $inp ['clCode'], $newInput );
		}
	}
	
	// ----------Function to send sms-------------------
	function _send_sms($recipient, $message) {
		$serverUrl = "http://api.smartsms.co.ke/api/sendsms/plain";
		
		if ($recipient == "") {
			return array (
					'error' => "Message not sent, No phoneNumber passed" 
			);
		}
		
		$recipient = "+254" . substr ( $recipient, 1 );
		
		$parameters = array (
				'user' => 'megarider',
				'password' => 'ZpmXSCdd',
				'sender' => 'PioneerFSA',
				'GSM' => $recipient,
				'SMSText' => $message 
		);
		
		$response = $this->CI ()->curl->simple_get ( $serverUrl, $parameters );
		
		// Validate Response
		// Ascertain -- the necessary return response is sent
		
		return true;
	}
	
	/* Africa Is Talking SMS-Sending */
	function _send_sms2($phoneNumber, $message) {
		if ($phoneNumber == "") {
			return array (
					'error' => "Message not sent, No phoneNumber passed" 
			);
		}
		
		$recipient = "+254" . substr ( $phoneNumber, 1 );
		
		// Create an instance of the gateway class
		$username = "TomKim";
		$shortCode = "PioneerFSA";
		$apiKey = "1473c117e56c4f2df393c36dda15138a57b277f5683943288c189b966aae83b4";
		$gateway = new AfricasTalkingGateway ( $username, $apiKey );
		
		try {
			// Send a response originating from the short code that received the message
			/*
			 * Bug:: If you put shortcode - It fails completely.
			 */
			
			$results = $gateway->sendMessage ( $recipient, $message, $shortCode);
			
			// Read in the gateway response and persist if necessary
			$response = $results [0];
			$status = $response->status;
			$cost = $response->cost;
			
			// echo $status . " " . $cost;
			
			if ($status = "Success") {
				return true;
			} else {
				return false;
			}
		} catch ( AfricasTalkingGatewayException $e ) {
			// Log the error
			$errorMessage = $e->getMessage ();
			return false;
		}
	}
	/*
	 * Get Customer Balance 
	 * <p>
	 * transactionId - Differentiates between Shares, Loan or Saving Balance
	 */
	function getCustTransaction($customerId, $transactionId) {
		$this->db->query ( 'Use MergeFinals' );
		$rs = $this->db->query ( 'SELECT Dbo.SP_GetBalances(\'' . $customerId . '\',' . $transactionId . ') AS balance' );
		// echo $this->db->last_query();
		
		$balance = $rs->row ()->balance;
		$this->db->query ( 'Use mobileBanking' );
		return $balance;
	}
}

/* End of file CoreScripts.php */