<?php

/**
 * 	MultiSafepay
 * 	Date: 17-1-2018
 * 	Version: 1.2.0
 * 	Author: Ruud Jonk
 * 	Email: ruud@multisafepay.com
 */
use Tygh\Http;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}
if (isset($_GET['type'])) {
    if ($_GET['type'] == "feed") {
        parseFeed();
    }
}


if (defined('PAYMENT_NOTIFICATION')) {
    if (isset($_REQUEST['type'])) {
        if ($_REQUEST['type'] == 'initial') {
            $url = 'payment_notification.return&payment=multisafepay_ideal&transactionid=' . $_REQUEST['transactionid'];
            $url = fn_url($url, AREA, 'current');

            echo '<a href="' . $url . '" >Keer terug naar de website.</a>';
            exit;
        }
    }


    if (($mode == 'notify' || $mode == 'return') && !empty($_REQUEST['transactionid'])) {
        if ($_REQUEST['transactionid']) {
            $pp_response = array();
            $order_info = fn_get_order_info($_REQUEST['transactionid']);

            if (empty($processor_data)) {
                $processor_data = fn_get_processor_data($order_info['payment_id']);
            }

            $pp_response = array();
            $order_id = $_REQUEST['transactionid'];

            require_once(dirname(__FILE__) . '/MultiSafepay.combined.php');

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
            $msp->transaction['id'] = $order_id;
            $status = $msp->getStatus();
            $details = $msp->details;
            $amount = $details['customer']['amount'];
            $order_id = $details['transaction']['id'];
            $pp_response['transaction_id'] = $order_id;

            $msp_statuses = $processor_data['processor_params']['statuses'];

            if ($order_info['status'] != 'P' && $order_info['status'] != 'C') {

                switch ($status) {
                    case "initialized":
                        $pp_response['order_status'] = $msp_statuses['initialized'];
                        $pp_response['reason_text'] = 'Transaction Initialized';

                        break;
                    case "completed":
                        $pp_response['order_status'] = $msp_statuses['completed'];
                        $pp_response['reason_text'] = 'Transaction completed';
                        break;
                    case "uncleared":
                        $pp_response['order_status'] = $msp_statuses['uncleared'];
                        $pp_response['reason_text'] = 'Transaction uncleared';
                        break;
                    case "reserved":
                        $pp_response['order_status'] = $msp_statuses['reserved'];
                        $pp_response['reason_text'] = 'Transaction reserved';
                        break;
                    case "void":
                        $pp_response['order_status'] = $msp_statuses['voided'];
                        $pp_response['reason_text'] = 'Transaction void';
                        break;
                    case "declined":
                        $pp_response['order_status'] = $msp_statuses['declined'];
                        $pp_response['reason_text'] = 'Transaction declined';
                        break;
                    case "reversed":
                        $pp_response['order_status'] = $msp_statuses['reversed'];
                        $pp_response['reason_text'] = 'Transaction reversed';
                        break;
                    case "refunded":
                        $pp_response['order_status'] = $msp_statuses['refunded'];
                        $pp_response['reason_text'] = 'Transaction refunded';
                        break;
                    case "expired":
                        $pp_response['order_status'] = $msp_statuses['expired'];
                        $pp_response['reason_text'] = 'Transaction expired';
                        break;
                    case "cancelled":
                        $pp_response['order_status'] = $msp_statuses['cancelled'];
                        $pp_response['reason_text'] = 'Transaction cancelled';
                        break;
                    default:
                        break;
                }


                if ($details['ewallet']['id'] != '' && $details['paymentdetails']['type'] != 'BANKTRANS') {
                    if ($status == 'initialized' || $status == 'expired') {
                        fn_change_order_status($_REQUEST['transactionid'], $pp_response['order_status'], '', false);
                        //fn_order_placement_routines($_REQUEST['transactionid'], false);		
                    } else {
                        fn_change_order_status($_REQUEST['transactionid'], $pp_response['order_status'], '', true);
                        fn_finish_payment($order_id, $pp_response, true);
                    }
                } elseif ($details['ewallet']['id'] != '' && $details['paymentdetails']['type'] == 'BANKTRANS' && $mode != 'return') {
                    if ($status == 'initialized') {
                        fn_change_order_status($_REQUEST['transactionid'], $msp_statuses['initialized'], '', false);
                    } else {
                        fn_change_order_status($_REQUEST['transactionid'], $pp_response['order_status'], '', true);
                        fn_finish_payment($order_id, $pp_response, true);
                    }
                }
            }
        }


        if ($mode == 'return') {

            if ($details['paymentdetails']['type'] == 'BANKTRANS') {
                $order_info = fn_get_order_info($_REQUEST['transactionid'], true);
                $order_id = $_REQUEST['transactionid'];
                $processor_data = fn_get_payment_method_data($order_info['payment_id']);

                $msp_statuses = $processor_data['processor_params']['statuses'];

                $order_info = fn_get_order_info($_REQUEST['transactionid'], true);
                //if ($order_info['status'] == 'N' || $order_info['status'] == $msp_statuses['initialized'] ) {
                fn_change_order_status($_REQUEST['transactionid'], 'O', '', false);
                //}


                fn_order_placement_routines('route', $_REQUEST['transactionid'], true);

                exit;
            } else {
                $order_info = fn_get_order_info($_REQUEST['transactionid'], true);
                $order_id = $_REQUEST['transactionid'];
                $processor_data = fn_get_payment_method_data($order_info['payment_id']);

                $msp_statuses = $processor_data['processor_params']['statuses'];

                if ($order_info['status'] == 'N') {
                    fn_change_order_status($_REQUEST['transactionid'], $msp_statuses['initialized'], '', false);
                }

                fn_order_placement_routines('route', $_REQUEST['transactionid'], true);
                exit;
            }
        }

        if (isset($_REQUEST['type'])) {
            $url = 'payment_notification.return&payment=multisafepay_ideal&transactionid=' . $order_id;
            $url = fn_url($url, AREA, 'current');
            echo '<a href="' . $url . '" >Keer terug naar de website.</a>';
        } else {
            echo "ok";
        }
        exit;
    } elseif ($mode == 'cancel') {
        $order_info = fn_get_order_info($_REQUEST['transactionid'], true);
        $order_id = $_REQUEST['transactionid'];
        $processor_data = fn_get_payment_method_data($order_info['payment_id']);


        $msp_statuses = $processor_data['processor_params']['statuses'];

        $pp_response['order_status'] = $msp_statuses['cancelled'];
        $pp_response["reason_text"] = fn_get_lang_var('text_transaction_cancelled');

        fn_finish_payment($_REQUEST['transactionid'], $pp_response, false);
        fn_order_placement_routines('route', $_REQUEST['transactionid']);
        exit;
    } elseif ($mode == 'process') {
        $pp_response = array();
    }
    exit;
} else {
    $itemlist = $order_info["products"];
    if (is_array($itemlist)) {
        $cart_items = "<ul>\n";
        foreach ($itemlist as $product) {
            $product_price = fn_format_price_by_currency($product['price'], CART_PRIMARY_CURRENCY, CART_SECONDARY_CURRENCY);
            $cart_items .= "<li>" . $product['amount'] . " x : " . $product['product'] . " : " . $product_price . "</li>\n";
        }
        $cart_items .= "</ul>\n";
    }

    
    if ($processor_data['processor_params']['mode'] == 'T') {
        $test = true;
    } else {
        $test = false;
    }

    //MSP SET DATA FOR TRANSACTION REQUEST

    $ip = fn_get_ip();
    require_once(dirname(__FILE__) . '/MultiSafepay.combined.php');
    $msp = new MultiSafepay();
    $msp->test = $test;
    $msp->merchant['account_id'] = $processor_data['processor_params']['account'];
    $msp->merchant['site_id'] = $processor_data['processor_params']['site_id'];
    $msp->merchant['site_code'] = $processor_data['processor_params']['securitycode'];
    //$msp->merchant['notification_url'] 	= 	Registry::get('config.current_location') . "/$index_script?dispatch=payment_notification.notify&payment=multisafepay_".strtolower($processor_data['processor_params']['gateway'])."&type=initial";
    //$msp->merchant['cancel_url']       	= 	Registry::get('config.current_location') . "/$index_script?dispatch=payment_notification.cancel&payment=multisafepay_".strtolower($processor_data['processor_params']['gateway'])."&transactionid=".$order_id;
    //$msp->merchant['redirect_url'] 	   	= 	Registry::get('config.current_location') . "/$index_script?dispatch=payment_notification.return&payment=multisafepay_".strtolower($processor_data['processor_params']['gateway']);	

    $gateway_url_postfix = strtolower($processor_data['processor_params']['gateway']);
    if ($gateway_url_postfix == "mistercash") { //hotfix for bancontact/mistercash url
        $gateway_url_postfix = "bancontact";
    }elseif ($gateway_url_postfix == "psafecard") { //hotfix for psafecard
        $gateway_url_postfix = "paysafecard";
    }

    $url = 'payment_notification.notify&payment=multisafepay_' . $gateway_url_postfix . '&type=initial';
    $url = fn_url($url, AREA, 'current');

    $msp->merchant['notification_url'] = $url;

    $url = 'payment_notification.cancel&payment=multisafepay_' . $gateway_url_postfix . '&transactionid=' . $order_id;
    $url = fn_url($url, AREA, 'current');
    $msp->merchant['cancel_url'] = $url;

    $url = 'payment_notification.return&payment=multisafepay_' . $gateway_url_postfix;
    $url = fn_url($url, AREA, 'current');
    $msp->merchant['redirect_url'] = $url;


    $msp->merchant['close_window'] = true;
    //$msp->customer['locale']           	= 	$processor_data['processor_params']['language'];
    $msp->customer['locale'] = isset($order_info['lang_code']) ? strtolower($order_info['lang_code']) : $processor_data['processor_params']['language'];
    $msp->customer['firstname'] = $order_info['b_firstname'];
    $msp->customer['lastname'] = $order_info['b_lastname'];

    $msp->customer['zipcode'] = $order_info['b_zipcode'];
    $msp->customer['city'] = $order_info['b_city'];
    $msp->customer['state'] = $order_info['b_state'];
    $msp->customer['email'] = $order_info['email'];
    $msp->customer['phone'] = $order_info['phone'];
    $msp->customer['country'] = $order_info['b_country'];
    $msp->customer['ipaddress'] = $ip['host'];
    $msp->customer['forwardedip'] = $ip['proxy'];
    $msp->parseCustomerAddress($order_info['b_address']);

    $msp->transaction['id'] = $order_id;
    $msp->transaction['currency'] = ($order_info['secondary_currency'] ? $order_info['secondary_currency'] : $processor_data['processor_params']['currency']);
    $msp->cart->currency = $msp->transaction['currency'];
    $msp->transaction['amount'] = fn_format_price_by_currency($order_info['total'], CART_PRIMARY_CURRENCY, CART_SECONDARY_CURRENCY) * 100;
    $msp->transaction['description'] = 'Order #' . $msp->transaction['id'];
    $msp->transaction['items'] = $cart_items;
    $msp->transaction['gateway'] = getGateway($processor_data['processor_params']['gateway']);
    $msp->plugin_name = 'CS-Cart 4.x';
    $msp->version = '1.2.0';

    $msp->plugin['shop'] = 'CS-Cart';
    $msp->plugin['shop_version'] = '4';
    $msp->plugin['plugin_version'] = '1.2.0';
    $msp->plugin['partner'] = '';
    $msp->plugin['shop_root_url'] = Registry::get('config.current_location');

    $taxes = array();

    $items = $order_info['products'];

        //Add the products
        foreach ($items as $item) {
            $product_data = fn_get_product_data($item['product_id'], $_SESSION['auth'], $order_info['lang_code'], '', true, true, true, true, false, true, true);
            
			$taxid ='BTW0';
            foreach ($product_data['tax_ids'] as $key => $value) {
                $taxid = $value;
                $taxed = $order_info['taxes'][$product_data[$value]]['price_includes_tax'];
            }

            if ($taxed == 'N') {
                $product_price = $item['price'];
            } else {
                $btw = $item['price'] / (100 + $order_info['taxes'][$taxid]['rate_value']) * $order_info['taxes'][$taxid]['rate_value'];
                $product_price = $item['price'] - $btw;
            }

            $cart_item_msp = new MspItem($item['product'], '', $item['amount'], fn_format_price_by_currency($product_price, CART_PRIMARY_CURRENCY, CART_SECONDARY_CURRENCY), 'KG', 0);
            $cart_item_msp->SetMerchantItemId($item['product_code']);
            //$cart_item_msp->SetTaxTableSelector('P_'.$item['item_id']);
            $cart_item_msp->SetTaxTableSelector($taxid);
            $msp->cart->AddItem($cart_item_msp);
        }
        
      


        //add shipping line item

        foreach ($order_info['shipping'] as $key => $shipper) {
            if ($shipper['shipping_id'] == $_SESSION['cart']['chosen_shipping'][0]) {
                if ($shipper['rate'] != 0) {
                    foreach ($order_info['taxes'] as $key => $value) {
                        if ($value['applies']['S'] != '0')
                            $shiptaxselector = $key;
                    }

                    $taxed = $order_info['taxes'][$shiptaxselector]['price_includes_tax'];

                    if ($taxed == 'N') {
                        $shiping_price = $shipper['rate'];
                    } elseif ($shiptaxselector) {
                        $btw = $shipper['rate'] / (100 + $order_info['taxes'][$shiptaxselector]['rate_value']) * $order_info['taxes'][$shiptaxselector]['rate_value'];
                        $shiping_price = $shipper['rate'] - $btw;
                    } else {
                        $shiping_price = $shipper['rate'];
                    }

                    $c_item = new MspItem($shipper['shipping'], 'Verzending', 1, fn_format_price_by_currency($shiping_price, CART_PRIMARY_CURRENCY, CART_SECONDARY_CURRENCY), 'KG', 0);
                    $c_item->SetMerchantItemId('msp-shipping');
                    //$c_item->SetTaxTableSelector('S_'.$key.'_0');

                    if ($shiptaxselector) {
                        $c_item->SetTaxTableSelector($shiptaxselector);
                    } else {
                        $c_item->SetTaxTableSelector('BTW0');
                    }

                    $msp->cart->AddItem($c_item);
                }
            }
        }

        //Add payment surcharge
        $taxes_payment_method = $order_info['payment_method']['tax_ids'];
        if (empty($taxes_payment_method)) {
            $surcharge_price = $order_info['payment_method']['a_surcharge'] + $order_info['payment_method']['p_surcharge'] * $order_info['subtotal'] / 100;
        } else {
            $total_surcharge = $order_info['payment_method']['a_surcharge'] + $order_info['payment_method']['p_surcharge'] * $order_info['subtotal'] / 100;
            $btw = $total_surcharge / (100 + $order_info['taxes'][$order_info['payment_method']['tax_ids'][0]]['rate_value']) * $order_info['taxes'][$order_info['payment_method']['tax_ids'][0]]['rate_value'];
            $surcharge_price = $total_surcharge - $btw;
        }

        if ($surcharge_price > 0) {
            $c_item = new MspItem($order_info['payment_method']['payment'], 'Payment Fee', 1, fn_format_price_by_currency($surcharge_price, CART_PRIMARY_CURRENCY, CART_SECONDARY_CURRENCY), 'KG', 0);
            $c_item->SetMerchantItemId('payment-fee');

            $ptax = $order_info['payment_method']['tax_ids'];
            foreach ($ptax as $key => $value) {
                $taxselector = $value;
            }

            if ($taxselector) {
                $c_item->SetTaxTableSelector($taxselector);
            } else {
                $c_item->SetTaxTableSelector('BTW0');
            }
            $msp->cart->AddItem($c_item);

            //add available tax rates
            $taxes = array();
            foreach ($order_info['taxes'] as $key => $tax) {

                if (!in_array($key, $taxes)) {
                    $taxes[] = $key;
                    $percentage = $tax['rate_value'] / 100;
                    $taxname = $key;
                    $taxtable = new MspAlternateTaxTable($taxname, 'true');
                    $taxrule = new MspAlternateTaxRule($percentage);
                    $taxtable->AddAlternateTaxRules($taxrule);
                    $msp->cart->AddAlternateTaxTables($taxtable);
                }
            }
        }

        //If there are coupons applied add coupon as a product with negative price
        if (isset($order_info['promotions'])) {
            foreach ($order_info['promotions'] as $key => $value) {
                if ($order_info['subtotal_discount'] != '0.00') {
                    $discount_price = $order_info['subtotal_discount'];
                    $coupon = new MspItem($value['name'], 'Discount Price', 1, ('-' . fn_format_price_by_currency($discount_price, CART_PRIMARY_CURRENCY, CART_SECONDARY_CURRENCY)));
                    $coupon->SetTaxTableSelector('BTW0');
                    $msp->cart->AddItem($coupon);
                }
            }
        }

        $percentage = '0.00';
        $taxname = 'BTW0';
        $taxtable = new MspAlternateTaxTable($taxname, 'true');
        $taxrule = new MspAlternateTaxRule($percentage);
        $taxtable->AddAlternateTaxRules($taxrule);
        $msp->cart->AddAlternateTaxTables($taxtable);
        
    if ($processor_data['processor_params']['gateway'] == 'IDEAL' && isset($order_info['payment_info']['issuer'])) {
        $msp->extravars = $order_info['payment_info']['issuer'];
        $url = $msp->startDirectXMLTransaction();
    }else{
	    $url = $msp->startCheckout();
    }
    if (isset($processor_data['processor_params']['debug'])) {
        if ($processor_data['processor_params']['debug'] == 'YES') {
            echo '<b style="color:red">MultiSafepay data:</b>';
            echo '<pre>';
            print_r($msp);
            echo '</pre>';
            echo '<br /><b style="color:red">CS-Cart order data:</b>';
            echo '<pre>';
            print_r($order_info);
            echo '</pre>';
            exit;
        }
    }

    if (!isset($msp->error)) {
        fn_redirect($url, true, true);
        exit;
    } else {
        fn_set_notification('E', "There was an error while processing your transaction: (Code: $msp->error)", "");

        $url = fn_url("checkout.cart", AREA, 'current');

        fn_redirect($url);
    }
    exit;
}

function parseFeed()
{
    echo 'parce feed';
    exit;
}

function getGateway($gateway_code)
{
    return ($gateway_code == "WALLET") ? "" : $gateway_code;
}

?>