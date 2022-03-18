<?php
session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/securimage/securimage.php';
$securimage = new Securimage();
require "inc/mpgClasses.php";
?>


<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
	<title>Make a Payment</title>

	<link href="css/bootstrap.css" rel="stylesheet">
	<link href="css/main.css" rel="stylesheet">
	<link href="css/hover.css" rel="stylesheet">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">

	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>
 <body> 
	<?php include 'inc/header.php'; ?>
  
	<div class="container-fluid">
		<div class="col-md-3">
			<?php include 'inc/sidebar.php'; ?>
		</div>
		<div class="col-md-6">
			<div class="panel panel-default">
				<h1 style="border-bottom: 2px solid #87BC23;">Make a Payment</h1>
				<?php
				if (isset($_POST['submitPayment']))
				{
				if (($securimage->check($_POST['captcha_code']) == true))
				{
					if ((isset($_POST['submitPayment'])) && ($_POST["currency"] == "CAD"))
					{
						$store_id='store12121';
						$api_token='42342343ffdfsf';
						/********************* Transactional Variables ************************/
						$type='purchase';
						$order_id=$_POST['invoiceNumber'];
						$cust_id=$_POST['firstName'] ." ".  $_POST['lastName'];
						$amount=$_POST['dollars'];
						$pan=$_POST['cardNumber'];
						$expiry_date=$_POST['month'] . $_POST['year'];		//December 2008
						$crypt='7';
						$cvd_indicator = '1';
						$cvd_value = $_POST['CVD'];

						$cvdTemplate = array(
											 'cvd_indicator' => $cvd_indicator,
											 'cvd_value' => $cvd_value
											);

						$mpgCvdInfo = new mpgCvdInfo ($cvdTemplate);

						$txnArray=array(
										'type'=>$type,
										'order_id'=>$order_id,
										'cust_id'=>$cust_id,
										'amount'=>$amount,
										'pan'=>$pan,
										'expdate'=>$expiry_date,
										'crypt_type'=>$crypt
										);

						$mpgTxn = new mpgTransaction($txnArray);

						$mpgTxn->setCvdInfo($mpgCvdInfo);

						$mpgRequest = new mpgRequest($mpgTxn);
						$mpgRequest->setProcCountryCode("CA");
						//$mpgRequest->setTestMode(true); //false or comment out this line for production transactions

						$mpgHttpPost  =new mpgHttpsPost($store_id,$api_token,$mpgRequest);

						$mpgResponse=$mpgHttpPost->getMpgResponse();
						
						$to = $_POST['email'];
						$subject = 'Invoice ' . $order_id;
						$headers = 'From: .com' . "\r\n" .
							'Reply-To: .com' . "\r\n" .
							'Bcc: .com' . "\r\n" .
							'X-Mailer: PHP/' . phpversion();
							
						if ($_POST['language'] == "ENG")
						{
							$emessage = "Online Receipt \r\n";
							$emessage .= "\r\n";
							$emessage .= "\r\n";
							$emessage .= "Transaction Type: Sale\r\n";
							$emessage .= "Order ID: ".$_POST["invoiceNumber"]."\r\n";
							$emessage .= "Date/Time: ".$mpgResponse->getTransDate()." ".$mpgResponse->getTransTime()." \t Approval Code: ".$mpgResponse->getAuthCode()."\r\n";
							$emessage .= "Reference Number: ".$mpgResponse->getReferenceNum()." \t Response Code ".$mpgResponse->getResponseCode()."\r\n";
							$emessage .= "Transaction Amount: $".$mpgResponse->getTransAmount()." (CAD)\r\n";
							$emessage .= "Card Number: ************".substr($_POST['cardNumber'], -4)."\r\n";
							$emessage .= "\r\n";
							$emessage .= "".$mpgResponse->getMessage()."\r\n";
							$emessage .= "\r\n";
							$emessage .= "Goods Description: Services - Payment of Invoice\r\n";
							$emessage .= "Total Amount ".$mpgResponse->getTransAmount()." CAD\r\n";
							$emessage .= "\r\n";
							$emessage .= "Customer Information\r\n";
							$emessage .= "".$_POST['firstName']." ".$_POST['lastName']."\r\n";
							$emessage .= "".$_POST['address']."\r\n";
							$emessage .= "".$_POST['city']."\r\n";
							$emessage .= "\r\n";
							$emessage .= "No Refunds or Returns.\r\n";

							mail($to, $subject, $emessage, $headers);
						}
						else
						{
							$emessage = "Réception en Ligne \r\n";
							$emessage .= "\r\n";
							$emessage .= "\r\n";
							$emessage .= "Type de Transaction: Sale\r\n";
							$emessage .= "Numéro de Commande: ".$_POST["invoiceNumber"]."\r\n";
							$emessage .= "Jour: ".$mpgResponse->getTransDate()." ".$mpgResponse->getTransTime()." \t Approval Code: ".$mpgResponse->getAuthCode()."\r\n";
							$emessage .= "Numéro de Réference: ".$mpgResponse->getReferenceNum()." \t Response Code ".$mpgResponse->getResponseCode()."\r\n";
							$emessage .= "Montant de la Transaction: $".$mpgResponse->getTransAmount()." (CAD)\r\n";
							$emessage .= "Numéro de Carte: ************".substr($_POST['cardNumber'], -4)."\r\n";
							$emessage .= "\r\n";
							$emessage .= "".$mpgResponse->getMessage()."\r\n";
							$emessage .= "\r\n";
							$emessage .= "Description des Marchandises: Services - Paiement des Factures\r\n";
							$emessage .= "Montant Total: ".$mpgResponse->getTransAmount()." CAD\r\n";
							$emessage .= "\r\n";
							$emessage .= "Informations Client\r\n";
							$emessage .= "".$_POST['firstName']." ".$_POST['lastName']."\r\n";
							$emessage .= "".$_POST['address']."\r\n";
							$emessage .= "".$_POST['city']."\r\n";
							$emessage .= "\r\n";
							$emessage .= "Aucun Remboursement ou Retour.\r\n";
							
							mail($to, $subject, $emessage, $headers);
						}
						
						echo '<p class="lead">Payment status: '.$mpgResponse->getMessage().'. Please check your '.$_POST['email'].'</p>';
					}
					else if ((isset($_POST['submitPayment'])) && ($_POST["currency"] == "USD"))
					{
						$store_id='store343443';
						$api_token='56564656y';
						/********************* Transactional Variables ************************/
						$type='purchase';
						$order_id=$_POST['invoiceNumber'];
						$cust_id=$_POST['firstName'] ." ".  $_POST['lastName'];
						$amount=$_POST['dollars'];
						$pan=$_POST['cardNumber'];
						$expiry_date=$_POST['month'] . $_POST['year'];		//December 2008
						$crypt='7';
						$cvd_indicator = '1';
						$cvd_value = $_POST['CVD'];

						$cvdTemplate = array(
											 'cvd_indicator' => $cvd_indicator,
											 'cvd_value' => $cvd_value
											);

						$mpgCvdInfo = new mpgCvdInfo ($cvdTemplate);

						$txnArray=array(
										'type'=>$type,
										'order_id'=>$order_id,
										'cust_id'=>$cust_id,
										'amount'=>$amount,
										'pan'=>$pan,
										'expdate'=>$expiry_date,
										'crypt_type'=>$crypt
										);

						$mpgTxn = new mpgTransaction($txnArray);

						$mpgTxn->setCvdInfo($mpgCvdInfo);

						$mpgRequest = new mpgRequest($mpgTxn);
						$mpgRequest->setProcCountryCode("US"); //"US" for sending transaction to US environment
						//$mpgRequest->setTestMode(true); //false or comment out this line for production transactions

						$mpgHttpPost  =new mpgHttpsPost($store_id,$api_token,$mpgRequest);

						$mpgResponse=$mpgHttpPost->getMpgResponse();
						
						$to = $_POST['email'];
						$subject = 'Invoice ' . $order_id;
						$headers = 'From: .com' . "\r\n" .
							'Reply-To: .com' . "\r\n" .
							'Bcc: .com' . "\r\n" .
							'X-Mailer: PHP/' . phpversion();
						
						if ($_POST['language'] == "ENG")
						{
							$emessage = "Online Receipt \r\n";
							$emessage .= "\r\n";
							$emessage .= "\r\n";
							$emessage .= "Transaction Type: Sale\r\n";
							$emessage .= "Order ID: ".$_POST["invoiceNumber"]."\r\n";
							$emessage .= "Date/Time: ".$mpgResponse->getTransDate()." ".$mpgResponse->getTransTime()." \t Approval Code: ".$mpgResponse->getAuthCode()."\r\n";
							$emessage .= "Reference Number: ".$mpgResponse->getReferenceNum()." \t Response Code ".$mpgResponse->getResponseCode()."\r\n";
							$emessage .= "Transaction Amount: $".$mpgResponse->getTransAmount()." (USD)\r\n";
							$emessage .= "Card Number: ************".substr($_POST['cardNumber'], -4)."\r\n";
							$emessage .= "\r\n";
							$emessage .= "".$mpgResponse->getMessage()."\r\n";
							$emessage .= "\r\n";
							$emessage .= "Goods Description: Services - Payment of Invoice\r\n";
							$emessage .= "Total Amount ".$mpgResponse->getTransAmount()." US\r\n";
							$emessage .= "\r\n";
							$emessage .= "Customer Information\r\n";
							$emessage .= "".$_POST['firstName']." ".$_POST['lastName']."\r\n";
							$emessage .= "".$_POST['address']."\r\n";
							$emessage .= "".$_POST['city']."\r\n";
							$emessage .= "\r\n";
							$emessage .= "No Refunds or Returns.\r\n";

							mail($to, $subject, $emessage, $headers);
						}
						else
						{
							$emessage = "Réception en Ligne \r\n";
							$emessage .= "\r\n";
							$emessage .= "\r\n";
							$emessage .= "Type de Transaction: Sale\r\n";
							$emessage .= "Numéro de Commande: ".$_POST["invoiceNumber"]."\r\n";
							$emessage .= "Jour: ".$mpgResponse->getTransDate()." ".$mpgResponse->getTransTime()." \t Approval Code: ".$mpgResponse->getAuthCode()."\r\n";
							$emessage .= "Numéro de Réference: ".$mpgResponse->getReferenceNum()." \t Response Code ".$mpgResponse->getResponseCode()."\r\n";
							$emessage .= "Montant de la Transaction: $".$mpgResponse->getTransAmount()." (USD)\r\n";
							$emessage .= "Numéro de Carte: ************".substr($_POST['cardNumber'], -4)."\r\n";
							$emessage .= "\r\n";
							$emessage .= "".$mpgResponse->getMessage()."\r\n";
							$emessage .= "\r\n";
							$emessage .= "Description des Marchandises: Services - Paiement des Factures\r\n";
							$emessage .= "Montant Total: ".$mpgResponse->getTransAmount()." US\r\n";
							$emessage .= "\r\n";
							$emessage .= "Informations Client\r\n";
							$emessage .= "".$_POST['firstName']." ".$_POST['lastName']."\r\n";
							$emessage .= "".$_POST['address']."\r\n";
							$emessage .= "".$_POST['city']."\r\n";
							$emessage .= "\r\n";
							$emessage .= "Aucun Remboursement ou Retour.\r\n";
							
							mail($to, $subject, $emessage, $headers);
						}
						echo '<p class="lead">Payment status: '.$mpgResponse->getMessage().'. Please check your '.$_POST['email'].'</p>';
					}
				}
				else
				{
					echo "Invalid Captcha!";
				}
				}
				?>
				<form class="form-horizontal" method="POST">
					<div class="form-group">
						<label class="col-md-2 control-label">Invoice #</label>
						<div class="col-md-4">
							<input type="text" name="invoiceNumber" class="form-control" />
						</div>
						
						<label class="col-md-2 control-label">Amount ($)</label>
						<div class="col-md-2">
							<div class="input-group">
							  <input type="text" class="form-control" id="exampleInputAmount" name="dollars" placeholder="100.00">
							</div>
						</div>
						<div class="col-md-2">
							<select name="currency" class="form-control">
								<option value="CAD">CAD</option>
								<option value="USD">USD</option>
							</select>
						</div>
					</div>
					
					<div class="form-group">
						<label class="col-sm-2 control-label">First</label>
						<div class="col-sm-4">
							<input type="text" class="form-control" name="firstName"/>
						</div>
						<label class="col-sm-2 control-label">Last</label>
						<div class="col-sm-4">
							<input type="text" class="form-control" name="lastName"/>
						</div>
					</div>
					
					<div class="form-group">
						<label class="col-sm-2 control-label">Address</label>
						<div class="col-sm-4">
							<input type="text" class="form-control" name="address"/>
						</div>
						<label class="col-sm-2 control-label">City</label>
						<div class="col-sm-4">
							<input type="text" class="form-control" name="city"/>
						</div>
					</div>
					
					<div class="form-group">
						<label class="col-md-2 control-label">Email: </label>
						<div class="col-md-10">
							<input type="email" name="email" class="form-control" />
						</div>
					</div>
				  
					<div class="form-group">
						<label class="col-sm-2 control-label">Card Number</label>
						<div class="col-sm-4">
							<input type="text" class="form-control" name="cardNumber"/>
						</div>
						<label class="col-sm-2 control-label">CVD</label>
						<div class="col-sm-4">
							<input type="text" class="form-control" name="CVD" />
						</div>
					</div>
				 
					<div class="form-group">
						<label class="col-md-2 control-label">Exp Date</label>
						<div class="col-md-2">
							<input type="text" name="month" class="form-control" placeholder="month"/>
						</div>
						<div class="col-md-2">
							<input type="text" name="year" class="form-control" placeholder="year"/>
						</div>
						
					</div>
					 <div class="form-group">
						<div class="col-md-10 col-md-offset-2">
							<img id="captcha" src="/securimage/securimage_show.php" alt="CAPTCHA Image" />
							<input type="text" name="captcha_code" size="10" maxlength="6" />
							<a href="#" onclick="document.getElementById('captcha').src = '/securimage/securimage_show.php?' + Math.random(); return false">[ Different Image ]</a>
						</div>
					 </div>
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-4">
							<button type="submit" class="btn btn-default" name="submitPayment">Confirm Payment Details</button>
						</div>
						<div class="col-md-3 col-md-offset-3">
							<select name="language" class="form-control">
								<option value="ENG">English</option>
								<option value="FR">French</option>
							</select>
						</div>
					</div>
				</form>
				
				<br />
			</div>
		</div>
		<div class="col-md-3">
			<?php include 'inc/sidebar_right.php'; ?>
		</div>
	</div>
	
	<?php include 'inc/footer.php'; ?>	

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>
