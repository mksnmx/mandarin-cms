<?php
$config = $_SERVER['DOCUMENT_ROOT'].'/phpshop/inc/config.ini';

$ini_array = parse_ini_file($config, true);
$host = $ini_array['connect']['host'];
$user_db = $ini_array['connect']['user_db'];
$pass_db = $ini_array['connect']['pass_db'];
$dbase = $ini_array['connect']['dbase'];

$pdo = new PDO(
    'mysql:host='.$host.';dbname='.$dbase,
    $user_db,
    $pass_db
);
$pdo->exec("SET NAMES cp1251");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$config_mandarin = $_SERVER['DOCUMENT_ROOT'].'/phpshop/modules/mandarin/inc/config.ini';
$ini_mandarin_array = parse_ini_file($config_mandarin, true);
$table = $ini_mandarin_array['base']['mandarin_system'];

$stmt = $pdo->prepare('SELECT * FROM `'.$table.'` WHERE `id` = 1 LIMIT 1');
$stmt->execute();
$data = $stmt->fetchAll();

$desc = $data[0]['title_end'];
$secret = $data[0]['merchant_sig'];
$mid = $data[0]['merchant_id'];
$pdo = null;

if(empty($GLOBALS['SysValue'])) exit(header("Location: /"));

// регистрационная информация
$mrh_login = $SysValue['roboxchange']['mrh_login'];    //логин
$mrh_pass1 = $SysValue['roboxchange']['mrh_pass1'];    // пароль1

//параметры магазина
$mrh_ouid = explode("-", $_POST['ouid']);

//описание покупки
$inv_desc  = "PHPShopPaymentServiceorder".$mrh_ouid[0];
$out_summ  = $GLOBALS['SysValue']['other']['total']*$SysValue['roboxchange']['mrh_kurs']; //сумма покупки

function calc_sign($secret, $fields)
{
        ksort($fields);
        $secret_t = '';
        foreach($fields as $key => $val)
        {
                $secret_t = $secret_t . '-' . $val;
        }
        $secret_t = substr($secret_t, 1) . '-' . $secret;
        return hash("sha256", $secret_t);
}

function generate_form($secret, $fields)
{
        $sign = calc_sign($secret, $fields);
        $form = "";
        foreach($fields as $key => $val)
        {
                $form = $form . '<input type="hidden" name="'.$key.'" value="' . htmlspecialchars($val) . '"/>'."\n";
        }
        $form = $form . '<input type="hidden" name="sign" value="'.$sign.'"/>';
        return $form;
}

$f = generate_form($secret, $values = array(
   "callbackUrl" => "http://".$_SERVER['HTTP_HOST']."/payment-mandarin.php",
   "customer_email" => $_POST['mail'],
   "merchantId" => $mid,
   "orderId" => $mrh_ouid[0],
   "price" => $out_summ,
   "returnUrl" => "http://".$_SERVER['HTTP_HOST']."/page/result.html"
));

// вывод HTML страницы с кнопкой для оплаты
$disp= '
<div align="center">
<img src="https://static.tildacdn.com/tild3464-3864-4261-b738-616561623662/Logo02_1_noring.svg" style="border:0">
<h4>
'.$desc.'
</h4>

<form action="https://secure.mandarinpay.com/Pay" method="POST"> 
'.$f.'
	  <table>
<tr>
	<td>
<input type="submit" value="Оплатить" /> 
        </td>
</tr>
</table>
      </form>
</div>';

?>