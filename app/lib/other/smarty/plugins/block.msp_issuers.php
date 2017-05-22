<?php

function smarty_block_msp_issuers($params, $content, &$smarty, &$repeat)
{
    $repeat = false;
    $processor_data = fn_get_processor_data($_SESSION['cart']['payment_id']);

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


    $idealselect = '<br /><select name="payment_info[issuer]" id="issuerselect" style="min-width:250px;">';
    if ($processor_data['processor_params']['mode'] == 'T') {
        foreach ($iDealIssuers['issuers'] as $issuer) {
            $idealselect .= '<option value="' . $issuer['code']['VALUE'] . '">' . $issuer['description']['VALUE'] . '</option>';
        }
    } else {
        foreach ($iDealIssuers['issuers']['issuer'] as $issuer) {
            $idealselect .= '<option value="' . $issuer['code']['VALUE'] . '">' . $issuer['description']['VALUE'] . '</option>';
        }
    }
    $idealselect .= '</select>';



    return $idealselect;
}

?>