<?php
/**
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the MultiSafepay plugin
 * to newer versions in the future. If you wish to customize the plugin for your
 * needs please document your changes and make backups before you update.
 *
 * @category    MultiSafepay
 * @package     Connect
 * @author      MultiSafepay <integration@multisafepay.com>
 * @copyright   Copyright (c) MultiSafepay, Inc. (https://www.multisafepay.com)
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 * PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN
 * ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

define('BOOTSTRAP', '');
if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set('Europe/Amsterdam');
}

// Load user configuration
define('AREA', true);
define('DIR_ROOT', dirname(__FILE__));

require_once(dirname(__FILE__) . '/config.php');

/**
 * If this is an update of the plugin,
 * we first have to rename some deprecated payment names.
 */

const PAYMENTS_TO_RENAME = [
    'Multisafepay Wallet' => 'MultiSafepay',
    'Betaalplan' => 'Santander Consumer Finance Pay per Month',
];

foreach (PAYMENTS_TO_RENAME as $oldName => $newName) {
    renamePaymentNames($oldName, $newName, $config);
}

$payments = array(
    "BANKTRANS" => "Bank transfer",
    "DIRDEB" => "Direct Debit",
    "DIRECTBANK" => "SOFORT Banking",
    "GIROPAY" => "Giropay",
    "IDEAL" => "iDEAL",
    "MAESTRO" => "Maestro",
    "MASTERCARD" => "Mastercard",
    "BANCONTACT" => "Bancontact",
    "WALLET" => "MultiSafepay",
    "VISA" => "Visa",
    "PAYPAL" => "PayPal",
    "FERBUY" => "Ferbuy",
    "DOTPAY" => "Dotpay",
    "PAYSAFECARD" => "Paysafecard",
    "PAYAFTER" => "Pay After Delivery",
    "EINVOICE" => "E-Invoicing",
    "KLARNA" => "Klarna",
    "AMEX" => "American Express",
    "ING" => "ING Home''Pay",
    "KBC" => "KBC",
    "BELFIUS" => "Belfius",
    "SANTANDER" => "Santander Consumer Finance Pay per Month",
    "ALIPAY" => "Alipay",
    "TRUSTLY" => "Trustly",
    "TRUSTPAY" => "TrustPay",
    "EPS" => "EPS",
    "IDEALQR" => "iDEAL QR",
    "AFTERPAY" => "AfterPay",
    "APPLEPAY" => "Apple Pay",
    "DBRTP" => "Request to Pay",
    "CBC" => "CBC",
);


foreach ($payments as $paymentcode => $naam) {
    upd($naam, "`" . $config['table_prefix'] . "payment_processors` SET `processor` = 'MultiSafepay " . $naam . "', `processor_script` = 'multisafepay_" . strtolower($paymentcode) . ".php', `admin_template` = 'msp_" . strtolower($paymentcode) . ".tpl', `processor_template` = 'views/orders/components/payments/msp_" . strtolower($paymentcode) . ".tpl', `callback` = 'Y', `type` = 'P'", $config);
}


$html = '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="nl-nl" lang="nl-nl">';
$html .= '<head>';
$html .= '<meta charset="utf-8">';
$html .= '<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">';
$html .= '<meta name="copyright" content="2020 MultiSafepay" />';
$html .= '<meta name="title" content="CS-Cart Gateway Installation | MultiSafepay" />';
$html .= '</head>';
$html .= '<body style="width:560px;height:100%;margin:0px auto;margin-top:40px;background:#252525">';
$html .= '<div class="wrapper" style="min-height:560;border:1px solid #00adee;padding:20px;position:relative;background:white;">';
$html .= '<img src="images/msp/multisafepay-logo.png" width="331" height="62" border="0" /><br /><br />';
$html .= '<h1 class="msp-error-h1" style="color:#252525;font-size:20px;">CS-Cart MultiSafepay Gateway Installation</h1>';
$html .= '<br />';
$html .= '<div class="msp-error-body">';
$html .= '<p style="color: red;">Please remove this file after installation!</p><br />';
foreach ($payments as $paymentcode => $naam) {
    $html .= '<b>Gateway: ' . $naam . ' added</b><br />';
}

$html .= '<div style="font-size:12px;text-align:center;padding-top:25px;">Copyright &#169; ' . date("Y") . ' MultiSafepay. Alle rechten voorbehouden.</div>';
$html .= '</div>';
$html .= '</body>';
$html .= '</html>';
echo $html;

function upd($naam, $query, $config)
{
    $mysqli = new mysqli($config['db_host'], $config['db_user'], $config['db_password'], $config['db_name']);

    if ($mysqli->connect_errno) {
        printf("Connect failed: %s\n", $mysqli->connect_error);
        exit();
    }
    $q = $mysqli->query("SELECT * FROM `" . $config['table_prefix'] . "payment_processors` WHERE `processor` = 'MultiSafepay " . $naam . "'");

    if (!$q || ($n = mysqli_num_rows($q)) == 0) {
        $ex = $mysqli->query("INSERT INTO " . $query);
        echo 'insert ' . $ex . '<br/>';
    } else {
        $r = mysqli_fetch_assoc($q);
        $ex = $mysqli->query("UPDATE " . $query . " WHERE `processor_id` = '" . $r['processor_id'] . "'");
        echo 'update ' . $ex . '<br/>';
    }
}


function renamePaymentNames($oldName, $newName, $config)
{
    $mysqli = new mysqli($config['db_host'], $config['db_user'], $config['db_password'], $config['db_name']);

    if ($mysqli->connect_errno) {
        printf("Connect failed: %s\n", $mysqli->connect_error);
        exit();
    }

    $query = 'UPDATE ' . $config['table_prefix'] . 'payment_processors' .
        " SET processor = 'MultiSafepay " . $newName . "' " .
        " WHERE processor = 'MultiSafepay " . $oldName . "'";

    $mysqli->query($query);
}
