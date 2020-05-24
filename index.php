<head>
</head>
<body>
<?php
require_once "components/subscriptions/subscriberHandler.php";
//$email = "flubberbub@gmail.com";
$email = "flemmishElephant@bazookas.com";
$email = "a@b.com";

//$result = getSubscriberData($email);
//
//if ($result == false)
//    echo "No, " . $email . " is not known";
//else
//    print_r($result);
/////////////////////////////////////////

$why = null;

$result = subscribePerson($email, $why);

if ($result)
    echo "<p>Success</p>";
else {
    echo "<p>Did not subscribe them</p>";
    echo "<p>Just for debugging porpoises:" . print_r($why, true) . "</p>";
}
?>
</body>
