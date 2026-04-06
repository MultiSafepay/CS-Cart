<?php
/**
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the MultiSafepay plugin
 * to newer versions in the future. If you wish to customize the plugin for your
 * needs, please document your changes and make backups before you update.
 *
 * @category    MultiSafepay
 * @package     Connect
 * @author      MultiSafepay <integration@multisafepay.com>
 * @copyright   Copyright © MultiSafepay, Inc. (https://www.multisafepay.com)
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 * PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN
 * ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set('Europe/Amsterdam');
}

// Bootstrap CS-Cart runtime, so add-on APIs are available across versions.
if (!defined('AREA')) {
    define('AREA', 'A');
}
require_once(__DIR__ . '/init.php');

$runtime_config = Tygh\Registry::get('config');
$config = [
    'db_host' => (string) $runtime_config['db_host'],
    'db_user' => (string) $runtime_config['db_user'],
    'db_password' => (string) $runtime_config['db_password'],
    'db_name' => (string) $runtime_config['db_name'],
    'table_prefix' => (string) $runtime_config['table_prefix'],
];

// Determine whether script is executed from CLI.
$isCli = (PHP_SAPI === 'cli');

if ($isCli) {
    echo "Starting msp_installer.php...\n";
}

$errorCount = 0;

// Rename deprecated payment names
const PAYMENTS_TO_RENAME = [
    'Multisafepay Wallet' => 'MultiSafepay',
    'Betaalplan' => 'Santander Consumer Finance Pay per Month',
];

foreach (PAYMENTS_TO_RENAME as $oldName => $newName) {
    if ($isCli) {
        echo "Renaming payment: $oldName to $newName\n\n";
    }
    renamePaymentNames($oldName, $newName, $config, $isCli, $errorCount);
}

ensureMultisafepayAddonIsActive($isCli);

$payments = array(
    'AFTERPAY' => 'AfterPay',
    'ALIPAY' => 'Alipay',
    'AMEX' => 'American Express',
    'APPLEPAY' => 'Apple Pay',
    'GOOGLEPAY' => 'Google Pay',
    'BANCONTACT' => 'Bancontact',
    'BANKTRANS' => 'Bank transfer',
    'BELFIUS' => 'Belfius',
    'BILLINK' => 'Billink',
    'BIZUM' => 'Bizum',
    'BNPL_MF' => 'Pay After Delivery',
    'CBC' => 'CBC',
    'DBRTP' => 'Request to Pay',
    'DIRDEB' => 'Direct Debit',
    'DOTPAY' => 'Dotpay',
    'EINVOICE' => 'E-Invoicing',
    'EPS' => 'EPS',
    'FERBUY' => 'Ferbuy',
    'GIROPAY' => 'Giropay',
    'IDEAL' => 'iDEAL | Wero',
    'IDEALQR' => 'iDEAL QR',
    'IN3' => 'in3',
    'ING' => 'ING Home Pay',
    'KBC' => 'KBC',
    'KLARNA' => 'Klarna',
    'MAESTRO' => 'Maestro',
    'MASTERCARD' => 'Mastercard',
    'PAYPAL' => 'PayPal',
    'PAYSAFECARD' => 'Paysafecard',
    'SANTANDER' => 'Santander Consumer Finance Pay per Month',
    'TRUSTLY' => 'Trustly',
    'TRUSTPAY' => 'TrustPay',
    'VISA' => 'Visa',
    'WALLET' => 'MultiSafepay',
);

foreach ($payments as $paymentcode => $naam) {
    if ($isCli) {
        echo "Updating payment: $naam with code: $paymentcode\n";
    }
    upd($naam, '`' . $config['table_prefix'] . 'payment_processors` SET `processor` = \'MultiSafepay ' . $naam . '\', `processor_script` = \'multisafepay_' . strtolower($paymentcode) . '.php\', `admin_template` = \'msp_' . strtolower($paymentcode) . '.tpl\', `processor_template` = \'views/orders/components/payments/msp_' . strtolower($paymentcode) . '.tpl\', `callback` = \'Y\', `type` = \'P\'', $config, $isCli, $errorCount);
}

$html = '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="nl-nl" lang="nl-nl">';
$html .= '<head>';
$html .= '<title>CS-Cart Gateway Installation | MultiSafepay</title>';
$html .= '<meta charset="utf-8">';
$html .= '<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">';
$html .= '<meta name="copyright" content="2024 MultiSafepay" />';
$html .= '<meta name="title" content="CS-Cart Gateway Installation | MultiSafepay" />';
$html .= '</head>';
$html .= '<body style="width:560px;height:100%;margin: 40px auto 0;background:#252525">';
$html .= '<div class="wrapper" style="min-height:560px;border:1px solid #00adee;padding:20px;position:relative;background:white;">';
$html .= '<img src="images/msp/multisafepay-logo.png" width="331" height="62" alt="" title="" /><br /><br />';
$html .= '<h1 class="msp-error-h1" style="color:#252525;font-size:20px;">CS-Cart MultiSafepay Gateway Installation</h1>';
$html .= '<br />';
$html .= '<div class="msp-error-body">';
$html .= '<p style="color: red;">Please remove this file after installation!</p><br />';

foreach ($payments as $paymentcode => $naam) {
    $html .= '<b>Gateway: ' . $naam . ' added</b><br />';
}

$html .= '<div style="font-size:12px;text-align:center;padding-top:25px;">Copyright &#169; ' . date('Y') . ' MultiSafepay. Alle rechten voorbehouden.</div>';
$html .= '</div>';
$html .= '</body>';
$html .= '</html>';

if (!$isCli) {
    echo $html;
}

function upd($naam, $query, $config, $isCli, &$errorCount)
{
    if ($isCli) {
        echo "\nConnecting to database for updating payment: $naam\n";
    }
    $mysqli = new mysqli($config['db_host'], $config['db_user'], $config['db_password'], $config['db_name']);

    if ($mysqli->connect_errno) {
        printf('Connect failed: %s\n', $mysqli->connect_error);
        $errorCount++;
        return;
    }
    $q = $mysqli->query('SELECT * FROM `' . $config['table_prefix'] . 'payment_processors` WHERE `processor` = \'MultiSafepay ' . $naam . '\'');

    if (!$q || ((string)mysqli_num_rows($q) === '0')) {
        if ($isCli) {
            echo "Inserting new payment: $naam\n";
        }
        $ex = $mysqli->query('INSERT INTO ' . $query);
        if ($isCli) {
            echo 'Insert result: ' . ($ex ? 'Success' : 'Failed') . "\n";
        }
    } else {
        if ($isCli) {
            echo "Updating existing payment: $naam\n";
        }
        $r = mysqli_fetch_assoc($q);
        $ex = $mysqli->query('UPDATE ' . $query . ' WHERE `processor_id` = \'' . $r['processor_id'] . '\'');
        if ($isCli) {
            echo 'Update result: ' . ($ex ? 'Success' : 'Failed') . "\n";
        }
    }
    if (!$ex) {
        $errorCount++;
    }
}

function renamePaymentNames($oldName, $newName, $config, $isCli, &$errorCount)
{
    if ($isCli) {
        echo "Connecting to database for renaming payment: $oldName to $newName\n";
    }
    $mysqli = new mysqli($config['db_host'], $config['db_user'], $config['db_password'], $config['db_name']);

    if ($mysqli->connect_errno) {
        printf('Connect failed: %s\n', $mysqli->connect_error);
        $errorCount++;
        return;
    }

    $query = 'UPDATE ' . $config['table_prefix'] . 'payment_processors' .
        ' SET processor = \'MultiSafepay ' . $newName . '\' ' .
        ' WHERE processor = \'MultiSafepay ' . $oldName . '\'';

    if ($isCli) {
        echo "Executing query: $query\n";
    }
    $result = $mysqli->query($query);

    if ($result) {
        if ($isCli) {
            echo "Successfully renamed $oldName to $newName\n\n";
        }
    } else {
        if ($isCli) {
            echo "Error renaming $oldName to $newName: " . $mysqli->error . "\n\n";
        }
        $errorCount++;
    }
}

/**
 * Ensure the MultiSafepay addon is installed and active so wallet label hooks are available
 * across older and newer CS-Cart versions.
 *
 * @return void
 */
function ensureMultisafepayAddonIsActive($isCli)
{
    $addon_id = 'multisafepay';

    if ($isCli) {
        echo "Ensuring add-on '$addon_id' is installed and active...\n";
    }

    $addon_xml_path = __DIR__ . '/app/addons/' . $addon_id . '/addon.xml';
    if (!file_exists($addon_xml_path)) {
        if ($isCli) {
            echo "Add-on '$addon_id' files are not deployed yet. Skipping activation for now.\n";
        }
        return;
    }

    if (function_exists('fn_install_addon')) {
        fn_install_addon($addon_id, false, false, true);
    }

    $addon_installed = function_exists('fn_check_addon_exists') &&
        (bool) fn_check_addon_exists($addon_id);

    if (!$addon_installed) {
        if ($isCli) {
            echo "Add-on '$addon_id' is not installed in DB yet. Skipping activation for now.\n";
        }
        return;
    }

    if (function_exists('fn_update_addon_status')) {
        fn_update_addon_status($addon_id, 'A', false, true, true);
    }

    $status = function_exists('db_get_field')
        ? (string) db_get_field("SELECT status FROM ?:addons WHERE addon = ?s", $addon_id)
        : '';

    if ($isCli) {
        echo "Add-on '$addon_id' status: " . ($status !== '' ? $status : 'N/A') . "\n";
    }
}

if ($isCli) {
    echo "\n" . 'msp_installer.php completed.' . "\n";
    if ($errorCount > 0) {
        echo "Total errors: $errorCount\n\n";
    } else {
        echo 'No errors encountered.'  . "\n\n";
    }
}
