<?php
defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
require APPPATH . '/libraries/AfricasTalkingGateway.php';
class Paybill extends CI_Controller {
	function Paybill() {
		parent::__construct ();
		$this->load->model ('Paybill_model', 'paybill' );
		$this->load->library ('CoreScripts' );
		$this->load->helper ( 'file' );
	}
	
	function index() {
		// Log the details
		$myFile = "application/controllers/mpesalog.txt";
		$input = $this->input->get ( NULL, TRUE );
		write_file ( $myFile, "=============================\n", 'a+' );
		foreach ( $input as $var => $value ) {
			if (! write_file ( $myFile, "$var = $value\n", 'a+' )) {
				echo "Unable to write to file!";
			}
		}
		
		// Get the input details
		$inp = array (
				'id' => $this->input->get ( 'id' ),
				'business_number' => $this->input->get ( 'business_number' ),
				'orig' => $this->input->get ( 'orig' ),
				'dest' => $this->input->get ( 'dest' ),
				'tstamp' => $this->input->get ( 'tstamp' ),
				'mpesa_code' => $this->input->get ( 'mpesa_code' ),
				'mpesa_acc' => $this->input->get ( 'mpesa_acc' ),
				'mpesa_msisdn' => $this->input->get ( 'mpesa_msisdn' ),
				'mpesa_trx_date' => $this->input->get ( 'mpesa_trx_date' ),
				'mpesa_trx_time' => $this->input->get ( 'mpesa_trx_time' ),
				'mpesa_amt' => $this->input->get ( 'mpesa_amt' ),
				'mpesa_sender' => $this->input->get ( 'mpesa_sender' ) 
		);
		$user = $this->input->get ( 'user' );
		$pass = $this->input->get ( 'pass' );
		
		if ($user == 'pioneerfsa' && $pass == 'financial@2013') {
			
			$firstName = $this->getFirstName($inp['mpesa_sender']); //JOASH NYADUNDO
			$amount = number_format ( $inp['mpesa_amt'] );
			
			
			if ($inp ['mpesa_acc']) {
				$transaction_registration = $this->paybill->record_transaction ( $inp );
				echo $transaction_registration['message'];
				
				$results = $this->paybill->checkCustomer($inp ['mpesa_acc']);
				if($results['success']){
					$balance = number_format($results['balance']+$amount);
					//Send SMS to Client
					$message ="Dear ". $firstName .", Your MPESA deposit of KES. ". $amount.
					" is confirmed. New balance KES. ".$balance." .Thanks for banking with us!";
					
					//echo $message;
					$sms_feedback = $this->corescripts->_send_sms ( '0729472421', $message);
				}else{
					//Send SMS to Client
					$message ="Dear ". $firstName .", Your MPESA deposit of KES. ". $amount.
					" confirmed. Always enter your Id Number as account number while sending MPESA deposit";
						
					//echo $message;
					$sms_feedback = $this->corescripts->_send_sms ( '0729472421', $message);
				}
				
				
			} else {
				$message = "Dear ".$firstName.". Your MPESA deposit of KES. ".$amount ." confirmed. Always enter your ID No. "
							."as the account number. Thanks for banking with us!";
				echo $message;
				//$sms_feedback = $this->corescripts->_send_sms ( '0729472421', $message );
			}
		} else {
			echo "FAIL|The payment could not be completed at this time.Incorrect username / password combination. Pioneer FSA";
		}
	}
	
	
	function getFirstName($names){
		$fullNames = explode(" ",$names);
		$firstName = $fullNames[0];
		$customString=substr($firstName,0,1).strtolower(substr($firstName,1));
		return $customString;
	}
}

?>