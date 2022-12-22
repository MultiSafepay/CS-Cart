<?php

/**
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the MultiSafepay plugin
 * to newer versions in the future. If you wish to customize the plugin for your
 * needs please document your changes and make backups before you update.
 *
 * @category MultiSafepay
 * @package Connect
 * @author TechSupport <techsupport@multisafepay.com>
 * @copyright Copyright (c) 2018 MultiSafepay, Inc. (https://www.multisafepay.com)
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 * PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN
 * ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
function smarty_block_msp_issuers($params, $content, &$smarty, &$repeat)
{
    $repeat = false;
    $processor_data = fn_get_processor_data($_SESSION['cart']['payment_id']);

    if(empty($processor_data) && isset($_REQUEST['order_id'])) {
        $order_info = fn_get_order_info($_REQUEST['order_id']);
        $processor_data = fn_get_processor_data($order_info['payment_id']);
    }

    require_once (DIR_ROOT . '/app/payments/MultiSafepay.combined.php');

    if ($processor_data['processor_params']['mode'] == 'T') {
        $test = true;
    } else {
        $test = false;
    }

    $msp = new MultiSafepay();
    $msp->test = $test;
    $msp->merchant['account_id'] = $processor_data['processor_params']['account'];
    $msp->merchant['site_id'] = $processor_data['processor_params']['site_id'];
    $msp->merchant['site_code'] = $processor_data['processor_params']['securitycode'];

    $iDealIssuers = $msp->getIdealIssuers();

    $idealselect = '<div class="litecheckout__field cm-field-container litecheckout__field--small litecheckout__field--state">';
    $idealselect .= '<select name="payment_info[issuer]" class="issuerselect litecheckout__input litecheckout__input--selectable litecheckout__input--selectable--select" id="issuerselect">';

    if (!empty($iDealIssuers['issuers']['issuer'])) {
        $idealselect .= '<option value="">Kies uw bank</option>';

        foreach ($iDealIssuers['issuers']['issuer'] as $issuer) {
            if (!empty($issuer['code']['VALUE']) && !empty($issuer['description']['VALUE'])) {
                $idealselect .= '<option value="' . $issuer['code']['VALUE'] . '">' . $issuer['description']['VALUE'] . '</option>';
            }
        }
    }
    else {
        // There are no banks available
        $idealselect .= '<option value="">Er zijn geen banken beschikbaar</option>';
    }
    $idealselect .= '</select></div>';

    return $idealselect;
}

?>
