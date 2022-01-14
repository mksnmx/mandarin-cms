<?php
header('Content-type: image/jpeg');
echo file_get_contents('https://'.'secure.mandarinpay.com/favicon.ico');
?>