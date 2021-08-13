<?php
#This file contains functions that access the database.
#Connect - establishes a mysqli connection using the information passed as a parameter.
function connect($dbHost, $dbName, $dbUsername, $dbPassword)
{
    $db = new mysqli(
        $dbHost,
        $dbUsername,
        $dbPassword,
        $dbName
    );
    if($db->connect_error)
    {
        die("Cannot connect to database: <br>"
        . $db->connect_error 
        . "<br>"
        . $dp->connect_errno
        );
    }
    return $db;
}

/**
 * Function for inserting new customer data into the database..
 * @param mysqli $db
 * @param array $record
 * @return array
*/
function insertRecord(mysqli $db, array $record)
{
    $sql = "INSERT INTO `CustomerData` "; #Database name
    $sql.= "(`First_Name`, `Last_Name`, `Email`, `Phone`, `Address`) ";
    $sql.= "VALUES ";
    $sql.= "(";
    $sql.= "'".$record['First_Name']."', ";
    $sql.= "'".$record['Last_Name']."', ";
    $sql.= "'".$record['Email']."', ";
    $sql.= "'".$record['Phone']."', ";
    $sql.= "'".$record['Address']."'";
    $sql.= ");";

    $db->query($sql);

    return $db;
}
#Function for sending a new customer's data to the database.
function createCustomer(mysqli $db, array $record)
{
    $sql = "INSERT INTO `CustomerData` "; #Database name
    $sql.= "(`First_Name`, `Last_Name`, `Email`, `Phone`, `Address`) ";
    $sql.= "VALUES ";
    $sql.= "(";
    $sql.= "'".$record['First_Name']."', ";
    $sql.= "'".$record['Last_Name']."', ";
    $sql.= "'".$record['Email']."', ";
    $sql.= "'".$record['Phone']."', ";
    $sql.= "'".$record['Address']."'";
    $sql.= ");";

    $db->query($sql);
}
#Function for sending timesheet data to database.
function createTimesheet(mysqli $db, array $record)
{
    $sql = "INSERT INTO `TimesheetData` ";
    $sql.= "(`employeeID`, `actionType`, `date`, `time`) ";
    $sql.= "VALUES ";
    $sql.= "(";
    $sql.= "'".$record['employeeID']."', ";
    $sql.= "'".$record['actionType']."', ";
    $sql.= "'".$record['date']."', ";
    $sql.= "'".$record['time']."'";
    $sql.= ");";

    $db->query($sql);
}
#Function for finding customer in database based on their name.
#The "%" symbols allow us to find matching data containing the specified input.
function findCustomerByName(mysqli $db, array $customerName)
{  
    #If the user has only entered a first or last name.
    if(sizeof($customerName) == 1)
    {
        $sql = "SELECT * FROM `CustomerData` WHERE `First_Name` LIKE '%";
        $sql.= $customerName[0] . "%'";
        $sql.= "OR `Last_Name` LIKE '%";
        $sql.= $customerName[0] . "%'";

        return $db->query($sql);
    }
    #If the user entered both a first and last name.
    else if(sizeof($customerName) > 1)
    {
        $sql = "SELECT * FROM `CustomerData` WHERE `First_Name` LIKE '%";
        $sql.= $customerName[0] . "%'";
        $sql.= "AND `Last_Name` LIKE '%";
        $sql.= $customerName[1] . "%'";
        
        return $db->query($sql);
    }
}
#Basic function for finding customers by email, input must be an exact match.
function findCustomerByEmail(mysqli $db, string $email)
{
    $sql = "SELECT * FROM `CustomerData` WHERE `Email` LIKE '";
    $sql.= $email . "'";

    return $db->query($sql);
}
#Basic function for finding customers by phone number, input must be an exact match.
function findCustomerByPhone(mysqli $db, string $phone)
{
    $sql = "SELECT * FROM `CustomerData` WHERE `Phone` LIKE '";
    $sql.= $phone . "'";

    return $db->query($sql);
}
#Function to find product at checkout (sale.php).
function findProduct(mysqli $db, string $product)
{
    $sql = "SELECT * FROM `Inventory` WHERE `ProductName` LIKE '%";
    $sql.= $product . "%' OR ";
    $sql.= "`SKU` LIKE '%";
    $sql.= $product . "%'";

    return $db->query($sql);
}
#Function used when a payment is successfully processed.
function createReceipt(mysqli $db, array $receiptData)
{
    $sql = "INSERT INTO `Sale` ";
    $sql.= "(`Customer_ID`, `EmployeeID`, `DOS`, `PaymentType`, `NumberOfItemsSold`, `TaxAmount`, `PreTax`, `TotalSaleAmount`) ";
    $sql.= "VALUES ";
    $sql.= "(";
    $sql.= "'".$receiptData['CustomerID']."', ";
    $sql.= "'".$receiptData['EmployeeID']."', ";
    $sql.= "'".$receiptData['DOS']."', ";
    $sql.= "'".$receiptData['PaymentType']."', ";
    $sql.= "'".$receiptData['NumItems']."', ";
    $sql.= "'".floatval(str_replace("$", "", $receiptData['Tax']))."', ";
    $sql.= "'".floatval(str_replace("$", "", $receiptData['Subtotal']))."', ";
    $sql.= "'".floatval(str_replace("$", "", $receiptData['Total']))."'";
    $sql.= ");";

    $db->query($sql);
}
#Function used after a payment is successfully processed to get that transaction id.
function getTransIDforSale(mysqli $db, $receiptData)
{
    $sql = "SELECT * FROM `Sale` WHERE `Customer_ID` LIKE '";
    $sql.= $receiptData['CustomerID'] . "' AND ";
    $sql.= "`EmployeeID` LIKE '";
    $sql.= $receiptData['EmployeeID'] . "' AND ";
    $sql.= "`DOS` LIKE '";
    $sql.= $receiptData['DOS'] . "'";

    return $db->query($sql);
}
#Last function used after payment, matches transaction id with sku's to create a transaction history.
function saveSalesData(mysqli $db, array $receiptData)
{
    #Grabs the cart data from passed array.
    $cart = explode("|", $receiptData['Cart']);

    #Loops through the cart array grabbing only SKU's
    for($i = 1; $i < count($cart); $i = $i + 4)
    {
	    $sql = "INSERT INTO `SalesData` ";
	    $sql.= "(`SKU`, `TRANSID`) ";
	    $sql.= "VALUES ";
	    $sql.= "(";
	    $sql.= "'".$receiptData['TransID']."', ";
	    $sql.= "'".$cart[$i]."'";
	    $sql.= ");";

	    $db->query($sql);
    }
}
?>
