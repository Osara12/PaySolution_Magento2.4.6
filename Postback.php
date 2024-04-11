<?php

$entityBody = file_get_contents('php://input');

echo "Start processing | ";

$headers = apache_request_headers();
/* 
//--- Api Request Header
foreach ($headers as $header => $value) {
    echo "$header: $value <br />\n";
}
*/
//----x-www-form-urlencoded POST
$refno = $_POST['refno']; // magento order no.
$orderno = $_POST['orderno']; // payso order no.
$total = $_POST['total']; // payment total
$status = $_POST['status']; // payment status code
$statusname = $_POST['statusname']; // payment status name

//----Check Post value 
if($refno == null){
    echo "refno is null";
    die();
}elseif($status == null){
    echo "payment status is null";
    die();
}elseif($status != "CP"){
    echo "Payment is not complete";
    die();
}

//----Start forwarding param to magento Payso Postback
$url = 'https://magento-168858-0.cloudclusters.net//rest/V1/PaySolutions/Base/PostBack/';
$data = array(
            "refno" => $refno,
            "orderno" => $orderno,
            "total"=> $total,
            "status"=> $status,
            "statusname"=> $statusname,
            );

$postdata = json_encode($data);
//print_r($postdata); die();
$ch = curl_init($url); 
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
$result = curl_exec($ch);
curl_close($ch);
print_r ($result);

die();

?>