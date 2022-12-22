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
use Tygh\Http;
use Tygh\Registry;
use Tygh\Shippings\Shippings;

require_once(dirname(__FILE__) . '/../MultiSafepay.combined.php');

/*
 * Function will request a fastcheckout payment link
 *
 */

function fn_multisafepay_set_fastcheckout($payment_id, $order_id = 0, $order_info = array(), $cart = array())
{
    $auth['user_id'] = '0';
    $auth['tax_exempt'] = '0';
    $cart['payment_id'] = $payment_id;


    $result = fn_place_order($cart, $auth, $action = '', $issuer_id = null, $parent_order_id = 0);


    $processor_data = fn_get_payment_method_data($payment_id);
    $taxes = array();
    $order_info = $cart;
    $ip = fn_get_ip();

    $itemlist = $order_info["products"];
    if (is_array($itemlist)) {
        $cart_items = "<ul>\n";
        foreach ($itemlist as $product) {
            $cart_items .= "<li>" . $product['amount'] . " x : " . $product['product'] . " : " . $product['price'] . "</li>\n";
        }
        $cart_items .= "</ul>\n";
    }

    if ($processor_data['processor_params']['mode'] == 'T') {
        $test = true;
    } else {
        $test = false;
    }


    $orderid = $result[0];


    $msp = new MultiSafepay();
    $msp->test = $test;
    $msp->merchant['account_id'] = $processor_data['processor_params']['account'];
    $msp->merchant['site_id'] = $processor_data['processor_params']['site_id'];
    $msp->merchant['site_code'] = $processor_data['processor_params']['securitycode'];
    if ($order_id == 0) {
        $msp->transaction['id'] = $orderid;
    } else {
        $msp->transaction['id'] = $order_id;
    }
    $msp->merchant['notification_url'] = fn_payment_url('current', "multisafepay_fastcheckout.php?mode=fastcheckout_notify&payment_id=$payment_id");
    $msp->merchant['cancel_url'] = fn_url("checkout.cart", 'C', 'current');
    $msp->merchant['redirect_url'] = fn_payment_url('current', "multisafepay_fastcheckout.php?mode=fastcheckout_return&payment_id=$payment_id");
    $msp->merchant['close_window'] = true;
    $msp->customer['locale'] = CART_LANGUAGE . '_' . strtoupper(CART_LANGUAGE);
    $msp->customer['ipaddress'] = $ip['host'];
    $msp->customer['forwardedip'] = $ip['proxy'];

    $msp->transaction['currency'] = (isset($order_info['secondary_currency']) ? $order_info['secondary_currency'] : $processor_data['processor_params']['currency']);
    $msp->transaction['amount'] = $order_info['total'] * 100;
    $msp->transaction['description'] = 'Order #' . $msp->transaction['id'];
    $msp->transaction['items'] = $cart_items;
    $msp->plugin_name = 'CS-Cart 4.x';
    $msp->version = '1.6.1';
    $msp->plugin['shop'] = 'CS-Cart';
    $msp->plugin['shop_version'] = PRODUCT_VERSION;
    $msp->plugin['plugin_version'] = '1.6.1';
    $msp->plugin['partner'] = '';
    $msp->plugin['shop_root_url'] = Registry::get('config.current_location');



    $items = $order_info['products'];

    //Add the products
    foreach ($items as $item) {
        $product_data = fn_get_product_data($item['product_id'], $_SESSION['auth'], CART_LANGUAGE, '', true, true, true, true, false, true, true);



        foreach ($product_data['tax_ids'] as $key => $value) {
            $taxid = $value;
            if (isset($order_info['taxes'][$value]['price_includes_tax'])) {
                $taxed = $order_info['taxes'][$value]['price_includes_tax'];
            } else {
                $taxed = 'N';
            }
        }

        if ($taxed == 'N') {
            $product_price = $item['price'];
        } else {
            $btw = $item['price'] / (100 + $order_info['taxes'][$taxid]['rate_value']) * $order_info['taxes'][$taxid]['rate_value'];
            $product_price = $item['price'] - $btw;
        }

        $cart_item_msp = new MspItem($item['product'], '', $item['amount'], $product_price, 'KG', 0);
        $msp->cart->AddItem($cart_item_msp);
        $cart_item_msp->SetMerchantItemId($item['product_code']);
        //$cart_item_msp->SetTaxTableSelector('P_'.$item['item_id']);
        $cart_item_msp->SetTaxTableSelector($taxid);
    }


    //add shipping line item
    $result = fn_get_shippings(true);

    print_r(fn_get_destinations());
    exit;


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

                $c_item = new MspItem($shipper['shipping'], 'Verzending', 1, $shiping_price, 'KG', 0);
                $c_item->SetMerchantItemId($key);
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

    //If there are coupons applied add coupon as a product with negative price
    if (isset($order_info['promotions'])) {
        foreach ($order_info['promotions'] as $key => $value) {
            if ($order_info['subtotal_discount'] != '0.00') {
                $discount_price = $order_info['subtotal_discount'];
                $coupon = new MspItem($value['name'], 'Discount Price', 1, ('-' . $discount_price));
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


    foreach ($order_info['product_groups'] as $key => $group) {
        $shipping_methods = $group['shippings'];

        foreach ($shipping_methods as $method => $values) {
            $shipping = new MspFlatRateShipping($values['shipping'], $values['rate']);
            $msp->cart->AddShipping($shipping);
        }
    }


    $url = $msp->startCheckout();


    if ($msp->error) {
        echo "MultiSafepay Error " . $msp->error_code . ": " . $msp->error;
        exit();
    }

    if (isset($url)) {
        return $url;
    } else {
        return $msp;
    }
}
