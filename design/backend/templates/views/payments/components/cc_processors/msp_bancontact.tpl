{* $Id: cc_multisafepay.tpl,v 1.0 2008/04/20 letun Exp $ *}
{assign var="r_url" value="`$config.http_location`/`$config.customer_index`?dispatch=payment_notification.notify&payment=multisafepay_bancontact"}
{assign var="e_url" value="`$config.http_location`/`$config.customer_index`?dispatch=payment_notification&payment_notification.result=multisafepay_bancontact"}
<h3>MultiSafepay</h3>
<p />

{* Test/Live mode *}
<div class="form-field">
  <label for="mode">Type account:</label>
  <select name="payment_data[processor_params][mode]" id="mode">
    <option value="P" {if $processor_params.mode == "P"}selected="selected"{/if}>Live account</option>
    <option value="T" {if $processor_params.mode == "T"}selected="selected"{/if}>Test account</option>
  </select>
</div>

<input type="hidden" name="payment_data[processor_params][gateway]" maxlength="20" id="store_id" value="MISTERCASH" class="input-text" />

{* account id *}
<div class="form-field">
  <label for="store_id">Account ID:</label>
  <input type="text" name="payment_data[processor_params][account]" maxlength="20" id="store_id" value="{$processor_params.account|escape}" class="input-text" />
</div>

{* site id *}
<div class="form-field">
  <label for="store_id">Site ID:</label>
  <input type="text" name="payment_data[processor_params][site_id]" maxlength="16" id="store_id" value="{$processor_params.site_id|escape}" class="input-text" />
</div>

{* Security Code *}

<div class="form-field">
  <label for="securitycode">Site Code:</label>
  <input type="text" name="payment_data[processor_params][securitycode]" maxlength="16" id="securitycode" value="{$processor_params.securitycode|escape}" class="input-text" />
</div>

{* Notificatie URL *}
<div class="form-field">
  <label for="notificationurl">Notificatie URL:</label>
  {$r_url}
  <input type="hidden" name="payment_data[processor_params][notificationurl]" id="securitycode" value="{$r_url|escape}"/>
</div>


{* Currency *}
<div class="form-field">
  <label for="currency">{__("currency")}:</label>
  <select name="payment_data[processor_params][currency]" id="currency">
    <option value="EUR" {if $processor_params.currency == "EUR"}selected="selected"{/if}>Euro (Europe)</option>
  </select>
</div>

{assign var="statuses" value=$smarty.const.STATUSES_ORDER|fn_get_simple_statuses}
<div class="form-field">
  <label for="minamount">Minimal amount:</label>
  <input type="text" name="payment_data[processor_params][minamount]" maxlength="20" id="minamount" value="{$processor_params.minamount|escape}" class="input-text" />
</div>

<div class="form-field">
  <label for="maxamount">Maximal amount:</label>
  <input type="text" name="payment_data[processor_params][maxamount]" maxlength="20" id="maxamount" value="{$processor_params.maxamount|escape}" class="input-text" />
</div>
<div class="form-field">
  <label for="elm_multisafepay_initialized">Initialized status:</label>
  <select name="payment_data[processor_params][statuses][initialized]" id="elm_multisafepay_initialized">
    {foreach from=$statuses item="s" key="k"}
      <option value="{$k}" {if (isset($processor_params.statuses.initialized) && $processor_params.statuses.initialized == $k) || (!isset($processor_params.statuses.initialized) && $k == 'I')}selected="selected"{/if}>{$s}</option>
    {/foreach}
  </select>
</div>



<div class="form-field">
  <label for="elm_multisafepay_refunded">Refunded status:</label>
  <select name="payment_data[processor_params][statuses][refunded]" id="elm_multisafepay_refunded">
    {foreach from=$statuses item="s" key="k"}
      <option value="{$k}" {if (isset($processor_params.statuses.refunded) && $processor_params.statuses.refunded == $k) || (!isset($processor_params.statuses.refunded) && $k == 'I')}selected="selected"{/if}>{$s}</option>
    {/foreach}
  </select>
</div>

<div class="form-field">
  <label for="elm_multisafepay_completed">Complete status:</label>
  <select name="payment_data[processor_params][statuses][completed]" id="elm_multisafepay_completed">
    {foreach from=$statuses item="s" key="k"}
      <option value="{$k}" {if (isset($processor_params.statuses.completed) && $processor_params.statuses.completed == $k) || (!isset($processor_params.statuses.completed) && $k == 'P')}selected="selected"{/if}>{$s}</option>
    {/foreach}
  </select>
</div>

<div class="form-field">
  <label for="elm_multisafepay_pending">Pending status:</label>
  <select name="payment_data[processor_params][statuses][pending]" id="elm_multisafepay_pending">
    {foreach from=$statuses item="s" key="k"}
      <option value="{$k}" {if (isset($processor_params.statuses.pending) && $processor_params.statuses.pending == $k) || (!isset($processor_params.statuses.pending) && $k == 'O')}selected="selected"{/if}>{$s}</option>
    {/foreach}
  </select>
</div>

<div class="form-field">
  <label for="elm_multisafepay_expired">Expired status:</label>
  <select name="payment_data[processor_params][statuses][expired]" id="elm_multisafepay_expired">
    {foreach from=$statuses item="s" key="k"}
      <option value="{$k}" {if (isset($processor_params.statuses.expired) && $processor_params.statuses.expired == $k) || (!isset($processor_params.statuses.expired) && $k == 'F')}selected="selected"{/if}>{$s}</option>
    {/foreach}
  </select>
</div>


<div class="form-field">
  <label for="elm_multisafepay_voided">Voided status:</label>
  <select name="payment_data[processor_params][statuses][voided]" id="elm_multisafepay_voided">
    {foreach from=$statuses item="s" key="k"}
      <option value="{$k}" {if (isset($processor_params.statuses.voided) && $processor_params.statuses.voided == $k) || (!isset($processor_params.statuses.voided) && $k == 'O')}selected="selected"{/if}>{$s}</option>
    {/foreach}
  </select>
</div>

<div class="form-field">
  <label for="elm_multisafepay_uncleared">Uncleared status:</label>
  <select name="payment_data[processor_params][statuses][uncleared]" id="elm_multisafepay_uncleared">
    {foreach from=$statuses item="s" key="k"}
      <option value="{$k}" {if (isset($processor_params.statuses.uncleared) && $processor_params.statuses.uncleared == $k) || (!isset($processor_params.statuses.uncleared) && $k == 'O')}selected="selected"{/if}>{$s}</option>
    {/foreach}
  </select>
</div>


<div class="form-field">
  <label for="elm_multisafepay_declined">Declined status:</label>
  <select name="payment_data[processor_params][statuses][declined]" id="elm_multisafepay_declined">
    {foreach from=$statuses item="s" key="k"}
      <option value="{$k}" {if (isset($processor_params.statuses.declined) && $processor_params.statuses.declined == $k) || (!isset($processor_params.statuses.declined) && $k == 'O')}selected="selected"{/if}>{$s}</option>
    {/foreach}
  </select>
</div>

<div class="form-field">
  <label for="elm_multisafepay_cancelled">Cancelled status:</label>
  <select name="payment_data[processor_params][statuses][cancelled]" id="elm_multisafepay_cancelled">
    {foreach from=$statuses item="s" key="k"}
      <option value="{$k}" {if (isset($processor_params.statuses.cancelled) && $processor_params.statuses.cancelled == $k) || (!isset($processor_params.statuses.cancelled) && $k == 'O')}selected="selected"{/if}>{$s}</option>
    {/foreach}
  </select>
</div>