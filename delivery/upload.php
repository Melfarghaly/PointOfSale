<?php 

include('../server/connection.php');
	$error = array();
	$alert = array();
	
	if(isset($_POST['upload'])){
		$target   		= "../csv_files/".basename($_FILES['file']['name']);
		$supplier = $_POST['supplier'];
		$transaction = $_POST['transaction_no'];
		$trans = $_POST['transaction_no'];
		$user = $_SESSION['username'];
		$add = '';

		if($_FILES['file']['name']){
			$filename = explode(".", $_FILES['file']['name']);

			$query = "INSERT INTO delivery (transaction_no,supplier_id,username) VALUES('$transaction',$supplier,'$user')";
			$insert = mysqli_query($db,$query);
			if($insert == true){
				
				if($filename[1] == 'csv'){
					$handle = fopen($target, "r");
					while ($data = fgetcsv($handle)) {
						$barcode 		= mysqli_real_escape_string($db , $data[0]);
						$product_name 	= mysqli_real_escape_string($db , $data[1]);
						$buy_price 		= mysqli_real_escape_string($db , $data[2]);
						$tax_rate		= mysqli_real_escape_string($db , $data[3]);
						$quantity 		= mysqli_real_escape_string($db , $data[4]);
						$unit 			= mysqli_real_escape_string($db , $data[5]);
						$min_stocks 	= mysqli_real_escape_string($db , $data[6]);
						$sell     = $tax_rate/100;
						$sell_price = $buy_price * $sell;

						$query1 = "SELECT product_no,quantity FROM products WHERE product_no='$barcode'";
						$select = mysqli_query($db, $query1);

						if(mysqli_num_rows($select)>0){
							while($row = mysqli_fetch_array($select)){
								$newqty = $row['quantity'] + $quantity;
								$insert = "UPDATE products SET quantity=$newqty, sell_price=$sell_price WHERE product_no='$barcode'";
								mysqli_query($db, $insert);
							}
						}else{
							$add = "INSERT INTO products(product_no,product_name,sell_price,quantity,unit,min_stocks) VALUES ('$barcode','$product_name',$sell_price,$quantity,'$unit',$min_stocks)";

							mysqli_query($db, $add);

		  					$add1 = "INSERT INTO product_delivered(transaction_no,product_id,total_qty,buy_price,tax_rate) VALUES('$transaction1','$barcode',$quantity,$buy_price,$tax_rate)";
		  					mysqli_query($db, $add1);
		  					
						}	
					}

					if(move_uploaded_file($_FILES['file']['tmp_name'], $target) == true ){
						array_push($alert, "Successfully Imported!");
						fclose($handle);
						$logs 	= "INSERT INTO logs (username,purpose) VALUES('$user','Delivery Added')";
 						mysqli_query($db,$logs);
						header('location: ../delivery/delivery.php?added="1"');
					}else{
						array_push($error, "Something went wrong!");
						
					}
			}else{
				array_push($error, "CSV file is required!");
				
			}
		}
	}
}