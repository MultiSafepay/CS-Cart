{assign var="msp_gateway" value=$order_info.payment_method.processor_params.gateway|default:""}
{if $msp_gateway == "APPLEPAY" || $msp_gateway == "GOOGLEPAY" || $order_info.payment_method.processor_script == "multisafepay_applepay.php" || $order_info.payment_method.processor_script == "multisafepay_googlepay.php"}
	{$order_info.payment_info.payment_method|default:$order_info.payment_method.payment|escape}
	{if !$order_info.payment_info.payment_method && $order_info.payment_info.payment_type} ({$order_info.payment_info.payment_type|escape}){elseif !$order_info.payment_info.payment_method && $order_info.payment_method.description} ({$order_info.payment_method.description|escape}){/if}
{else}
	{$order_info.payment_method.payment|escape} {if $order_info.payment_method.description}({$order_info.payment_method.description|escape}){/if}
{/if}
