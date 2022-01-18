
<div class="other-pay clearfix">
    <ul class="paym-methods">
        {foreach from=$payments item="payment"}
            {assign var="payment_data" value=$payment.payment_id|fn_get_payment_method_data}


            {if $payment_id == $payment.payment_id}
                {$instructions = $payment.instructions}
            {/if}

            {if $payment_data.processor_params.minamount && $payment_data.processor_params.maxamount}
                {if $payment_data.processor_params.ipvalidator}
                    {if $smarty.session.settings.secondary_currencyC.value == 'EUR'}
                        {if $payment_data.processor_params.ipvalidator == 'Enabled'}
                            {if $payment_data.processor_params.ipaddresses == $smarty.server.REMOTE_ADDR && $smarty.session.cart.user_data.b_country == "NL"}
                                {if $smarty.session.cart.total >= $payment_data.processor_params.minamount && $smarty.session.cart.total <= $payment_data.processor_params.maxamount}
                                    <li>
                                        <input id="payment_{$payment.payment_id}" class="radio valign cm-select-payment" type="radio" name="payment_id" value="{$payment.payment_id}" {if $payment_id == $payment.payment_id}checked="checked"{/if} />
                                        <div class="radio1">
                                            <h5>
                                                <label for="payment_{$payment.payment_id}">
                                                    {if $payment.image}
                                                        <div>
                                                            {include file="common/image.tpl" obj_id=$payment.payment_id images=$payment.image image_width=$settings.Thumbnails.product_cart_thumbnail_width image_height=$settings.Thumbnails.product_cart_thumbnail_height}
                                                        </div>
                                                    {/if}

                                                    {$payment.payment}
                                                </label>
                                            </h5>{$payment.description}
                                        </div>
                                    </li>

                                    {if $payment_id == $payment.payment_id}
                                        {if $payment.template && $payment.template != "cc_outside.tpl"}
                                            <div>
                                                {include file=$payment.template}
                                            </div>
                                        {/if}
                                    {/if}
                                {/if}
                            {/if}
                        {else}
                            {if $smarty.session.cart.total >= $payment_data.processor_params.minamount && $smarty.session.cart.total <= $payment_data.processor_params.maxamount && $payment_data.processor_params.gateway != 'PAYAFTER'}
                                <li>
                                    <input id="payment_{$payment.payment_id}" class="radio valign cm-select-payment" type="radio" name="payment_id" value="{$payment.payment_id}" {if $payment_id == $payment.payment_id}checked="checked"{/if} />
                                    <div class="radio1">
                                        <h5>
                                            <label for="payment_{$payment.payment_id}">
                                                {if $payment.image}
                                                    <div>
                                                        {include file="common/image.tpl" obj_id=$payment.payment_id images=$payment.image image_width=$settings.Thumbnails.product_cart_thumbnail_width image_height=$settings.Thumbnails.product_cart_thumbnail_height}
                                                    </div>
                                                {/if}

                                                {$payment.payment}
                                            </label>
                                        </h5>{$payment.description}
                                    </div>
                                </li>

                                {if $payment_id == $payment.payment_id}
                                    {if $payment.template && $payment.template != "cc_outside.tpl"}
                                        <div>
                                            {include file=$payment.template}
                                        </div>
                                    {/if}
                                {/if}
                            {elseif $smarty.session.cart.total >= $payment_data.params.minamount && $smarty.session.cart.total <= $payment_data.params.maxamount && $smarty.session.cart.user_data.b_country == "NL"}
                                }								<li>
                                    <input id="payment_{$payment.payment_id}" class="radio valign cm-select-payment" type="radio" name="payment_id" value="{$payment.payment_id}" {if $payment_id == $payment.payment_id}checked="checked"{/if} />
                                    <div class="radio1">
                                        <h5>
                                            <label for="payment_{$payment.payment_id}">
                                                {if $payment.image}
                                                    <div>
                                                        {include file="common/image.tpl" obj_id=$payment.payment_id images=$payment.image image_width=$settings.Thumbnails.product_cart_thumbnail_width image_height=$settings.Thumbnails.product_cart_thumbnail_height}
                                                    </div>
                                                {/if}

                                                {$payment.payment}
                                            </label>
                                        </h5>{$payment.description}
                                    </div>
                                </li>

                                {if $payment_id == $payment.payment_id}
                                    {if $payment.template && $payment.template != "cc_outside.tpl"}
                                        <div>
                                            {include file=$payment.template}
                                        </div>
                                    {/if}
                                {/if}
                            {/if}
                        {/if}
                    {/if}
                {else}
                    {if $payment_data.processor_params.gateway != 'VISA' && $payment_data.processor_params.gateway != 'MASTERCARD' && $smarty.session.settings.secondary_currencyC.value == 'EUR' && $payment_data.processor_params.gateway != 'PAYAFTER'}
                        {if $smarty.session.cart.total >= $payment_data.processor_params.minamount && $smarty.session.cart.total <= $payment_data.processor_params.maxamount}
                            <li>
                                <input id="payment_{$payment.payment_id}" class="radio valign cm-select-payment" type="radio" name="payment_id" value="{$payment.payment_id}" {if $payment_id == $payment.payment_id}checked="checked"{/if} />
                                <div class="radio1">
                                    <h5>
                                        <label for="payment_{$payment.payment_id}">
                                            {if $payment.image}
                                                <div>
                                                    {include file="common/image.tpl" obj_id=$payment.payment_id images=$payment.image image_width=$settings.Thumbnails.product_cart_thumbnail_width image_height=$settings.Thumbnails.product_cart_thumbnail_height}
                                                </div>
                                            {/if}

                                            {$payment.payment}
                                        </label>
                                    </h5>{$payment.description}
                                </div>
                            </li>

                            {if $payment_id == $payment.payment_id}
                                {if $payment.template && $payment.template != "cc_outside.tpl"}
                                    <div>
                                        {include file=$payment.template}
                                    </div>
                                {/if}
                            {/if}
                        {/if}
                    {elseif $payment_data.params.gateway == 'PAYAFTER' && $smarty.session.cart.user_data.b_country == "NL"}
                        {if $smarty.session.cart.total >= $payment_data.processor_params.minamount && $smarty.session.cart.total <= $payment_data.processor_params.maxamount}
                            <li>
                                <input id="payment_{$payment.payment_id}" class="radio valign cm-select-payment" type="radio" name="payment_id" value="{$payment.payment_id}" {if $payment_id == $payment.payment_id}checked="checked"{/if} />
                                <div class="radio1">
                                    <h5>
                                        <label for="payment_{$payment.payment_id}">
                                            {if $payment.image}
                                                <div>
                                                    {include file="common/image.tpl" obj_id=$payment.payment_id images=$payment.image image_width=$settings.Thumbnails.product_cart_thumbnail_width image_height=$settings.Thumbnails.product_cart_thumbnail_height}
                                                </div>
                                            {/if}

                                            {$payment.payment}
                                        </label>
                                    </h5>{$payment.description}
                                </div>
                            </li>

                            {if $payment_id == $payment.payment_id}
                                {if $payment.template && $payment.template != "cc_outside.tpl"}
                                    <div>
                                        {include file=$payment.template}
                                    </div>
                                {/if}
                            {/if}
                        {/if}

                    {elseif $payment_data.processor_params.gateway == 'VISA' || $payment_data.processor_params.gateway == 'MASTERCARD'}
                        {if $smarty.session.cart.total >= $payment_data.processor_params.minamount && $smarty.session.cart.total <= $payment_data.processor_params.maxamount}
                            <li>
                                <input id="payment_{$payment.payment_id}" class="radio valign cm-select-payment" type="radio" name="payment_id" value="{$payment.payment_id}" {if $payment_id == $payment.payment_id}checked="checked"{/if} />
                                <div class="radio1">
                                    <h5>
                                        <label for="payment_{$payment.payment_id}">
                                            {if $payment.image}
                                                <div>
                                                    {include file="common/image.tpl" obj_id=$payment.payment_id images=$payment.image image_width=$settings.Thumbnails.product_cart_thumbnail_width image_height=$settings.Thumbnails.product_cart_thumbnail_height}
                                                </div>
                                            {/if}

                                            {$payment.payment}
                                        </label>
                                    </h5>{$payment.description}
                                </div>
                            </li>

                            {if $payment_id == $payment.payment_id}
                                {if $payment.template && $payment.template != "cc_outside.tpl"}
                                    <div>
                                        {include file=$payment.template}
                                    </div>
                                {/if}
                            {/if}						{/if}
                        {/if}
                    {/if}

                {else}
                    {if $payment_data.processor_params.gateway != 'VISA' && $payment_data.processor_params.gateway != 'MASTERCARD' && $smarty.session.settings.secondary_currencyC.value == 'EUR' && $payment_data.processor_params.gateway != 'PAYAFTER'}
                        <li>
                            <input id="payment_{$payment.payment_id}" class="radio valign cm-select-payment" type="radio" name="payment_id" value="{$payment.payment_id}" {if $payment_id == $payment.payment_id}checked="checked"{/if} />
                            <div class="radio1">
                                <h5>
                                    <label for="payment_{$payment.payment_id}">
                                        {if $payment.image}
                                            <div>
                                                {include file="common/image.tpl" obj_id=$payment.payment_id images=$payment.image image_width=$settings.Thumbnails.product_cart_thumbnail_width image_height=$settings.Thumbnails.product_cart_thumbnail_height}
                                            </div>
                                        {/if}

                                        {$payment.payment}
                                    </label>
                                </h5>{$payment.description}
                            </div>
                        </li>

                        {if $payment_id == $payment.payment_id}
                            {if $payment.template && $payment.template != "cc_outside.tpl"}
                                <div>
                                    {include file=$payment.template}
                                </div>
                            {/if}
                        {/if}

                    {elseif $payment_data.params.gateway == 'PAYAFTER' && $smarty.session.cart.user_data.b_country == "NL"}
                        {if $smarty.session.cart.total >= $payment_data.processor_params.minamount && $smarty.session.cart.total <= $payment_data.processor_params.maxamount}
                            <li>
                                <input id="payment_{$payment.payment_id}" class="radio valign cm-select-payment" type="radio" name="payment_id" value="{$payment.payment_id}" {if $payment_id == $payment.payment_id}checked="checked"{/if} />
                                <div class="radio1">
                                    <h5>
                                        <label for="payment_{$payment.payment_id}">
                                            {if $payment.image}
                                                <div>
                                                    {include file="common/image.tpl" obj_id=$payment.payment_id images=$payment.image image_width=$settings.Thumbnails.product_cart_thumbnail_width image_height=$settings.Thumbnails.product_cart_thumbnail_height}
                                                </div>
                                            {/if}

                                            {$payment.payment}
                                        </label>
                                    </h5>{$payment.description}
                                </div>
                            </li>

                            {if $payment_id == $payment.payment_id}
                                {if $payment.template && $payment.template != "cc_outside.tpl"}
                                    <div>
                                        {include file=$payment.template}
                                    </div>
                                {/if}
                            {/if}
                        {/if}

                    {elseif $payment_data.processor_params.gateway == 'VISA' || $payment_data.processor_params.gateway == 'MASTERCARD'}
                        <li>
                            <input id="payment_{$payment.payment_id}" class="radio valign cm-select-payment" type="radio" name="payment_id" value="{$payment.payment_id}" {if $payment_id == $payment.payment_id}checked="checked"{/if} />
                            <div class="radio1">
                                <h5>
                                    <label for="payment_{$payment.payment_id}">
                                        {if $payment.image}
                                            <div>
                                                {include file="common/image.tpl" obj_id=$payment.payment_id images=$payment.image image_width=$settings.Thumbnails.product_cart_thumbnail_width image_height=$settings.Thumbnails.product_cart_thumbnail_height}
                                            </div>
                                        {/if}

                                        {$payment.payment}
                                    </label>
                                </h5>{$payment.description}
                            </div>
                        </li>

                        {if $payment_id == $payment.payment_id}
                            {if $payment.template && $payment.template != "cc_outside.tpl"}
                                <div>
                                    {include file=$payment.template}
                                </div>
                            {/if}
                        {/if}				{/if}
                    {/if}
                {/foreach}
            </ul>
            <div class="other-text">
                {$instructions|unescape}
            </div>
        </div>