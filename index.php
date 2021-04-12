<?php
header('Content-Type: application/json');
require './class.AddressHandler.php';



try {
    $ah = new AddressHandler(new mysqli('localhost', 'root', '', 'eleamapi'));    

    // $ah->addAddress('anyUid', '232108', 'IN', 'Utter Pradesh', 'Varanasi', 'Ramnagar', ['name'=> "Dinesh Gupta", 'mobile'=>'+919889871300', 'nearBy'=>'Kidzee Ramnagar']);

    $ah->removeAddress(2);
    $ah->updateAddress(3, 'postalCode', 'UK', 'Broklyn', 'London', 'London Street', ['name'=>'hero'], 45, 50);
    $addresses = $ah->getAddressesByUid('anyUid');
    print_r($addresses);

} catch (Exception $e) {
    die($e->getMessage() . ' - ' . $e->getCode());
}
