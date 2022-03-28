<?php 
/*
     I, Nguyen Duc Long, 000837437 certify that this material is my original work.
     No other person's work has been used without due acknowledgement.

     @date Dec 10, 2021
     @author DUC LONG NGUYEN (Paul)
     @A brief description of the file: Final project: St Joseph's Hospital Management;
        file php: interaction with databases including:
        	- connection the database
        	- select all the items in a table
        	- select an item in a table
        	- insert an item in a table
        	- update an item in a table
        	- delete an item in a table
        	- select an user from finalproject_users table through user's email and password
*/
	class ConnectionDb {
		/* 	<summary>CONNECT TO DATABASE</summary>
			<returns>a dbh</returns>
        */
		public function connectDBH()
		{
			$DBH=null;
			try {
				$DB_CONNECTION="mysql";
				$DB_HOST="localhost";
				$DB_DATABASE="sa000837437";
				$DB_USERNAME="sa000837437";
				$DB_PASSWORD="Sa_19921014";
				$DBH=new PDO($DB_CONNECTION.":host=".$DB_HOST.";dbname=".$DB_DATABASE,$DB_USERNAME,$DB_PASSWORD);
			} catch (PDOException  $e) {
				echo "ERROR: CANNOT CONNECT TO THE DATABASE. ".$e->getMessage();
			}
			return $DBH;
		}

		/* 	<summary>List of table and column for each table in the database</summary>
			<returns>an array represents a database including all the tables and columns for each table</returns>
        */
		public function listTablesAndColumns()
		{
			$admissions = ["admissions_id", "patient_id", "admission_date", "discharge_date", "primary_diagnosis", 
                "secondary_diagnoses", "attending_physician_id", "nursing_unit_id", "room","bed"];
		    $departments = ["department_id", "department_name", "manager_first_name", "manager_last_name"];
		    $encounters = ["encounter_id", "patient_id", "physician_id", "encounter_date_time", "notes"];
		    $medications = ["medication_id", "medication_description", "medication_cost", "package_size", "strength", 
		                    "sig", "units_used_ytd", "last_prescribed_date"];
		    $items = ["item_id", "item_description", "item_cost", "quantity_on_hand", "usage_ytd", "primary_vendor_id", 
		                    "primary_vendor_id", "primary_vendor_id"];
		    $nursing_units = ["nursing__id", "nursing_unit_id", "specialty", "manager_first_name", "manager_last_name", 
		                    "beds", "extension"];
		    $patients = ["patient_id", "first_name", "last_name", "gender", "birth_date", "street_address", "city",
		                     "province_id", "postal_code", "health_card_num", "allergies", "patient_height", "patient_weight"];
		    $physicians = ["physician_id", "first_name", "last_name", "specialty", "phone", "ohip_registration"];
		    $provinces = ["id", "province_id", "province_name"];
		    $purchase_orders = ["purchase_order_id", "order_date", "department_id", "vendor_id", "total_amount", "order_status"];
		    $purchase_order_lines = ["purchase_order_lines_id", "purchase_order_id", "line_num", "item_id", "quantity", 
		                                "unit_cost", "received", "cancelled", "last_arrived_date"];
		    $unit_dose_orders = ["unit_dose_order_id", "patient_id", "medication_id", "dosage", "sig", "dosage_route", 
		                            "pharmacist_initials", "entered_date"];
		    $vendors = ["vendor_id", "vendor_name", "street_address", "city", "province_id", "postal_code", "contact_first_name", 
		                    "contact_last_name", "purchases_ytd"];
			$users = ["user_id", "user_email", "user_password", "authoritative", "patient_id"];

		    $arrTable = [
		        'admissions' => $admissions, 
		        'departments' => $departments,
		        'encounters' => $encounters,
		        'medications' => $medications,
		        'items' => $items,
		        'nursing_units' => $nursing_units,
		        'patients' => $patients,
		        'physicians' => $physicians,
		        'provinces' => $provinces,
		        'purchase_orders' => $purchase_orders,
		        'purchase_order_lines' => $purchase_order_lines,
		        'unit_dose_orders' => $unit_dose_orders,
		        'vendors' => $vendors,
		        'users' => $users
		    ];
		    return $arrTable;
		}


	    /* 	<summary>SELECT ALL ITEMS IN TABLE</summary>
        	<param name="table">the name of the table</param>
        	<param name="id">the name of column id (the primary key) for this table (sort by id desc)</param>
			<returns>a json array all the items in this table</returns>
        */
	    public function selectAllItems($table, $id) {
	    	$items=array();
			$command='SELECT * FROM `'.$table.'` WHERE `isDelete`=0 ORDER BY '.$id.' ASC';
			$stmt=$this->connectDBH()->prepare($command);
			$success=$stmt->execute();
			if ($success){
				/*while ($rowElement=$stmt->fetch()){
					$row=array();
					foreach ($rowElement as $key => $value)
						if (!is_numeric($key))
							$row[$key] = $value;		
					array_push($items,$row);
				}*/
				while ($rowElement=$stmt->fetch())
					array_push($items,$rowElement);
			}
			return json_encode($items);
	    }

	    /* 	<summary>SELECT AN ITEM IN TABLE</summary>
        	<param name="table">the name of the table</param>
        	<param name="columns">an array of all the columns in this table (excluding isDelete column)</param>
        	<param name="id">the value of column id (the primary key) in this table</param>
			<returns>an item in this table if this item exist. Else, the method returns null value</returns>
        */
	    public function selectItem($table, $columns, $id) {
			$command='SELECT * FROM `'.$table.'` WHERE `isDelete`=0 AND `'.$columns[0].'`=?';
			$stmt=$this->connectDBH()->prepare($command);
			$params=[$id];
			$success=$stmt->execute($params);
			if ($success){
				while ($rowElement=$stmt->fetch())
					return $rowElement;
			}
			return NULL;
	    }

	    /* 	<summary>IF ITEM DOES NOT EXIST, INSERT THIS ITEM</summary>
        	<param name="table">the name of the table</param>
        	<param name="columns">an array of all the columns in this table (excluding isDelete column)</param>
        	<param name="listParam">an array of all the values 
        			for each column in this table (excluding isDelete column and id column)</param>
			<returns>true if insertion this item into the databae is successful. Else, return false.</returns>
        */
	    public function insertItem($table, $columns, $listParam){
	    	$sqlInsert = "";
	    	for ($i = 1; $i < count($columns); $i++) {			/*excluding id(i=1) and isDelete*/
	    		$sqlInsert = $sqlInsert."`".$columns[$i]."`,";
	    	}

	    	$sqlParam = "";
	    	for ($i = 0; $i < count($listParam); $i++) {
	    		$sqlParam = $sqlParam."?,";	
	    	}
	    	$command='INSERT into `'.$table.'`('.$sqlInsert.'`isDelete`) VALUES ('.$sqlParam.'0)';
			$stmt=$this->connectDBH()->prepare($command);

			$params = [];
			foreach ($listParam as $value) 
				array_push($params, $value);

			$success=$stmt->execute($params);
			if ($success)  return true;
			else return false;
	    }

	    /* 	<summary>IF ITEM EXISTED, UPDATE THIS ITEM</summary>
        	<param name="id">the value of id column ( the value of the primary key) for the table</param>
        	<param name="table">the name of the table</param>
        	<param name="columns">an array of all the columns in this table (excluding isDelete column)</param>
        	<param name="listParam">an array of all the values 
        			for each column in this table (excluding isDelete column and id column)</param>
			<returns>true if updation this item into the databae is successful. Else, return false.</returns>
        */
	    public function  updateItem($id, $table, $columns, $listParam){
	    	$sqlUpdate = "";
	    	for ($i = 1; $i < count($columns); $i++) {	/*excluding id($i = 1) and isDelete*/
	    		$sqlUpdate = $sqlUpdate."`".$columns[$i]."`=?, ";
	    	}

	    	$command='UPDATE `'.$table.'` SET '.$sqlUpdate.'`isDelete`=0 WHERE `'.$columns[0].'`=?';
			$stmt=$this->connectDBH()->prepare($command);

			$params = [];
			foreach ($listParam as $value) 
				array_push($params, $value);
			array_push($params, $id);
			
			$success=$stmt->execute($params);
			if ($success)  return true;
			else return false;
	    }

	    /* 	<summary>IF ITEM EXISTED, DELETE THIS ITEM</summary>
        	<param name="table">the name of the table</param>
        	<param name="columns">an array of all the columns in this table (excluding isDelete column)</param>
        	<param name="id">the value of id column ( the value of the primary key) for the table</param>
			<returns>true if deletion this item from the databae is successful. Else, return false.</returns>
        */
	    public function deleteItem($table, $columns, $id)
	    {
	    	$command='UPDATE `'.$table.'` SET `isDelete`=1 WHERE `'.$columns[0].'`=?';
	    	$stmt=$this->connectDBH()->prepare($command);
			$params=[$id];
			$success=$stmt->execute($params);
			if ($success)  return true;
			else return false;
	    }

	    /* 	<summary>LOGIN FUNCTION</summary>
        	<param name="email">the user's email</param>
        	<param name="password">the user's password</param>
			<returns>an user if this user exist. Else, the method returns null value</returns>
        */
	    public function login($email, $password)
	    {
	    	$command='SELECT * FROM `finalproject_users` WHERE `user_email`=? AND `user_password`=?';
			$stmt=$this->connectDBH()->prepare($command);
			$params=[$email, $password];
			$success=$stmt->execute($params);
			if ($success){
				while ($rowElement=$stmt->fetch())
					return $rowElement;
			}
			return NULL;
	    }
	}

 ?>