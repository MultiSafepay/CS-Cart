<?php
if (!defined('BOOTSTRAP')) { die('Access denied'); }

require_once(__DIR__ . '/../../payments/MultiSafepay.combined.php');

/**
* Called after new shipment creation.
*
* @param array $shipment_data Array of shipment data.
* @param array $order_info Shipment order info
* @param int $group_key Group number
* @param bool $all_products
* @param int $shipment_id Created shipment identifier
*/
function fn_multisafepay_create_shipment_post($shipment_data, $order_info, $group_key, $all_products, $shipment_id)
{
    // Only continue if in the Admin and order is processed by MultiSafepay
    if (AREA !== 'A' || empty($shipment_data) || !fn_is_multisafepay_order($order_info)) {
        return;
    }

    $processor_params = $order_info['payment_method']['processor_params'];

    $msp = new MultiSafepay();
    $msp->test = $processor_params['mode'] === 'T';
    $msp->merchant['account_id'] = $processor_params['account'];
    $msp->merchant['site_id'] = $processor_params['site_id'];
    $msp->merchant['site_code'] = $processor_params['securitycode'];
    $msp->transaction['id'] = $shipment_data['order_id'];
    $msp->transaction['status'] = 'shipped';
    $msp->transaction['tracktracecode'] = $shipment_data['tracking_number'];
    $msp->transaction['reason'] =  $shipment_data['comments'];
    $msp->transaction['carrier'] =  $shipment_data['carrier'];
    $msp->transaction['shipdate'] =  date("Y-m-d", $shipment_data['timestamp']);

    $msp->updateTransaction();
}

function fn_is_multisafepay_order($order_info)
{
    if (empty($order_info)) {
        return false;
    }
    return strncmp($order_info['payment_method']['processor'], 'MultiSafepay', 12) === 0;
}

/**
 * Normalizes wallet payment label in order info for all templates/views.
 *
 * @param array $order           Order data
 * @param array $additional_data Additional raw order data entries
 *
 * @return void
 */
function fn_multisafepay_get_order_info(&$order, $additional_data)
{
    if (empty($order['payment_info']['payment_method']) || empty($order['payment_method'])) {
        return;
    }

    // Keep backend order details unchanged: admin already has dedicated
    // payment info fields (Payment method / Payment type).
    if (defined('AREA') && AREA === 'A') {
        return;
    }

    if (!fn_is_multisafepay_order($order)) {
        return;
    }

    $gateway_code = strtoupper((string) ($order['payment_method']['processor_params']['gateway'] ?? ''));
    $processor_script = strtolower((string) ($order['payment_method']['processor_script'] ?? ''));
    $payment_method_display = (string) ($order['payment_info']['payment_method'] ?? '');

    $is_wallet_gateway = in_array($gateway_code, ['APPLEPAY', 'GOOGLEPAY'], true);
    $is_wallet_script = in_array($processor_script, ['multisafepay_applepay.php', 'multisafepay_googlepay.php'], true);
    $is_wallet_display = stripos($payment_method_display, 'Apple Pay') !== false
        || stripos($payment_method_display, 'Google Pay') !== false;

    if (!$is_wallet_gateway && !$is_wallet_script && !$is_wallet_display) {
        return;
    }

    $order['payment_method']['payment'] = $payment_method_display;

    // Keep rendering clean when templates append description next to payment label.
    $order['payment_method']['description'] = '';
}
