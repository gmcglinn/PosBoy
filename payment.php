<html>
<link rel="stylesheet" type="text/css" href="mystyle.css">
<div class="screen">

<?php
require_once 'config.php';
require_once 'db.php';

session_start();

date_default_timezone_set("America/New_York");

if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] == false)
{
    header("Location: login.php");
}

echo '<head><h3>[Checkout - Payment]</h3></head>';

$receiptData = $_SESSION['receipt'];

echo '<body>
    <p>Please select a payment type:</p>

    <form method="post">
        <input type="button" onclick="openForm(\'cash\')" value ="Cash">
        <input type="button" onclick="openForm(\'credit\')" value ="Credit">
        <input type="button" onclick="openForm(\'debit\')" value ="Debit">
        <input type="submit" name="cancel" value ="Cancel">
    </form>

    <div class ="form-popup" id ="cash" style="display:none;">
        <form method="post">
            <h3>[Cash]</h3>
        <p>Amount due: ' . $receiptData['Total'] . '</p>
            <input type="text" name="cashAmt" placeholder="Cash Amount"><br>
        <input type="submit" name="finalize" value="Finalize">
        <input type="button" onclick="closeForm(\'cash\')" value="Cancel">
        </form>
    </div>

    <div class ="form-popup" id="credit" style="display:none;">
        <form method="post">
            <h3>[Credit]</h3>
        <p>Amount Due: ' . $receiptData['Total'] . '</p>
        <input type="password" placeholder="Card Number" required><br>
        <input type="text" placeholder="Expiration" required><br>
            <input type="password" placeholder="CRN" required><br>
            <input type="text" placeholder="Name" required><br>
        <input type="hidden" name="cardType" value="credit">
        <input type="submit" name="process" value="Process">
            <input type="button" onclick="closeForm(\'credit\')" value ="Cancel">
        </form>
    </div>

    <div class ="form-popup" id="debit" style="display:none;">
        <form method="post">
            <h3>[Debit]</h3>
            <p>Amount Due: ' . $receiptData['Total'] . '</p>
            <input type="password" placeholder="Card Number" required><br>
        <input type="password" placeholder="Pin" required><br>
        <input type="text" placeholder="Expiration" required><br>
        <input type="password" placeholder="CRN" required><br>
        <input type="text" placeholder="Name" required><br>
        <input type="hidden" name="cardType" value="debit">
        <input type="submit" name="process" value="Process"> 
            <input type="button" onclick="closeForm(\'debit\')" value ="Cancel">
        </form>
    </div>
    </body>';
//Clears important session data for less overhead and more security.
//Then navigates back to the original checkout window.
if(isset($_POST['cancel']))
{
    $_SESSION['customer'] = null;
    $_SESSION['receipt'] = null;
    header("Location: checkout.php");
}
//Checks to see if the amount entered for cash payment is enough
//If it is, then we move to the receipt.
if(isset($_POST['finalize']))
{
    $db = connect(DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD);

    if(floatval($_POST['cashAmt']) < floatval(str_replace("$", "", $receiptData['Total'])))
    {
	    echo "Cash given is less than amount due.";
    }
    else
    {
        $customerData = $_SESSION['customer'];
        $changeDue = floatval($_POST['cashAmt']) - floatval(str_replace("$", "", $receiptData['Total']));
        $_SESSION['receipt'] += array('PaymentType' => 'cash');
        $_SESSION['receipt'] += array('ChangeDue' => $changeDue);
        $_SESSION['receipt'] += array('CustomerID' => $customerData['Customer_ID']);
        $_SESSION['receipt'] += array('EmployeeID' => $_SESSION['userid']);
        $_SESSION['receipt'] += array('DOS' => date("Y-m-d"));	
        createReceipt($db, $_SESSION['receipt']);
        $result = mysqli_fetch_array(getTransIDforSale($db, $_SESSION['receipt']));
        $_SESSION['receipt'] += array('TransID' => $result['TRANSID']);
        saveSalesData($db, $_SESSION['receipt']);
        header("Location: receipt.php");
    }
}
if(isset($_POST['process']))
{
    //When fully implemented these statements would carry on the next
    //instructions for properly processing credit card or debit card
    //transactions.
    if($_POST['cardType'] == 'credit')
    {
        $_SESSION['receipt'] += array('PaymentType' => 'credit');
    }
    else
    {
        $_SESSION['receipt'] += array('PaymentType' => 'debit');    
    }
    $customerData = $_SESSION['customer'];
    $_SESSION['receipt'] += array('CustomerID' => $customerData['Customer_ID']);
    $_SESSION['receipt'] += array('EmployeeID' => $_SESSION['userid']);
    $_SESSION['receipt'] += array('DOS' => date("Y-m-d"));
    createReceipt($db, $_SESSION['receipt']);
    $result = mysqli_fetch_array(getTransIDforSale($db, $_SESSION['receipt']));
    $_SESSION['receipt'] += array('TransID' => $result['TRANSID']);
    saveSalesData($db, $_SESSION['receipt']);
    header("Location: receipt.php");
}
?>
</div>
</html>

<script>
function openForm(formName)
{
    document.getElementById(formName).style.display = "block";
}
function closeForm(formName)
{
    document.getElementById(formName).style.display = "none";
}
</script>
