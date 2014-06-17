<?php
class Paybill_model extends CI_Model {
	
	/*PAYBILL Custom Function */
	function record_transaction($input){
		$query=$this->db->insert('PioneerIPN', $input);
		if($query){
		return array(
				'message'=>"OK|Thankyou, IPN has been successfully been saved.",
				);
		}
	}
	
	
	function checkCustomer($idNo){
		$this->db->query('Use MergeFinals');
		$this->db->where(array('docnum'=>$idNo
						));
		$query=$this->db->get('clientdoc');
		
		if($query->num_rows()<1){
			return  array(
					'success'=>false,
			);
		}else{
			$balance = $this->getCustTransaction($query->row()->clientcode,2);
			return  array(
					'success'=>true,
					'balance'=>$balance
			);
		}
	}
	
	
	function getCustTransaction($customerId, $transactionId){
		$rs = $this->db->query('SELECT Dbo.SP_GetBalances(\''.$customerId.'\','.$transactionId.') AS balance');
		//echo $this->db->last_query();
	
		$balance = $rs->row()->balance;
		$this->db->query('Use mobileBanking');
		$currentBalance = $this->getPrevDeposits($customerId);
		return $balance+$currentBalance;
	}
	
	function getPrevDeposits($customerId){
		//Users transaction for Today
		$this->db->select_sum('mpesa_amt');
		$this->db->where(
				array(	'mpesa_acc'=>$customerId,
						'isRecorded' => '0'
				));
		$query=$this->db->get('PioneerIPN');
		$amount= $query->row()->mpesa_amt;
		return $amount;
	}
}	
?>