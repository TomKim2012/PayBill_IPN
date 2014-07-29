<?php
class Paybill_model extends CI_Model {
	
	/* PAYBILL Custom Function */
	function record_transaction($input) {
		$this->db->query ( 'Use MobileBanking' );
		$query = $this->db->insert ( 'PioneerIPN', $input );
		if ($query) {
			return array (
					'message' => "OK|Thankyou, IPN has been successfully been saved." 
			);
		}
	}
	function checkCustomer($idNo, $phoneNumber) {
		// Check Customer by Id
		$this->db->query ( 'Use MergeFinals' );
		$this->db->where ( array (
				'docnum' => $idNo 
		) );
		$query = $this->db->get ( 'clientdoc' );
		
		if ($query->num_rows () < 1) {
			return $this->checkCustomer_by_phone ( $phoneNumber );
		} else {
			$balance = $this->getCustTransaction ( $query->row ()->clientcode, 2, $idNo );
			return array (
					'success' => true,
					'balance' => $balance 
			);
		}
	}
	
	/*
	 * Get customer by phone Number
	 */
	function checkCustomer_by_phone($phoneNumber) {
		// Check Customer by phone
		$this->db->query ( 'Use mobileBanking' );
		$this->db->where ( array (
				'phone' => $phoneNumber 
		) );
		$query = $this->db->get ( 'Client' );
		
		return array (
				'success' => false,
				'clCode' => isset ( $query->row ()->clcode ) ? $query->row ()->clcode : " ",
				'customerNames' => isset ( $query->row ()->clname ) ? $query->row ()->clname . " " . 
					$query->row ()->clsurname : " " 
		);
	}
	function getCustTransaction($customerId, $transactionId, $idNo) {
		$rs = $this->db->query ( 'SELECT Dbo.SP_GetBalances(\'' . $customerId . '\',' . $transactionId . ') AS balance' );
		
		$balance = $rs->row ()->balance;
		$this->db->query ( 'Use mobileBanking' );
		$currentBalance = $this->getPrevDeposits ( $idNo );
		return $balance + $currentBalance;
	}
	function getPrevDeposits($idNo) {
		// Users UnProcessed Transactions
		$this->db->select_sum ( 'mpesa_amt' );
		$this->db->where ( array (
				'mpesa_acc' => $idNo,
				'isProcessed' => '0' 
		) );
		$query = $this->db->get ( 'PioneerIPN' );
		$amount = $query->row ()->mpesa_amt;
		return $amount;
	}
	function updateRecords($trxNo, $idNo, $clientCode) {
		//update transactions
		$input = array('mpesa_acc'=>$idNo,'isProcessed'=>0);
		$this->db->where ( 'mpesa_code', $trxNo );
		$query = $this->db->update ( 'PioneerIPN', $input );
		if ($query) {
			 return $this->updateClientCode($idNo,$clientCode);
		} else {
			return false;
		}
	}
	
	function updateClientCode($idNo,$clientCode){
		$this->db->query ( 'Use mergeFinals' );
		$input = array('docnum'=>$idNo);
		$this->db->where ( 'clientcode', $clientCode );
		$query = $this->db->update ( 'clientdoc', $input );
		echo $this->db->last_query();
		
		if ($query) {
			return true;
		} else {
			return false;
		}
	}
}
?>