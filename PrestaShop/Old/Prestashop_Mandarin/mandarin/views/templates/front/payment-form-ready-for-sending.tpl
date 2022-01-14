{capture name=path}{l s='Your shopping cart'}{/capture}


<h3>{l s='Order summary' mod='mandarinpay'}</h3>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<div class="box cheque-box">
<p>
	<img src="{$store_url}modules/mandarinpay/logo-real.png" alt="{l s='MandarinPay' mod='mandarinpay'}" style="margin-bottom: 5px;" /><br/><br/>
    {l s='You have chosen to pay with MandarinPay.' mod='mandarinpay'}</p>

<p>{l s='The total amount of your order is' mod='mandarinpay'} <b>{$total_amount} {$currency}</b></p>

<p><b>{l s='Please confirm your order by clicking \'I confirm my order\'' mod='mandarinpay'}.</b></p>
</div>



<form action="{$mandarinpay_url}" method="post" id="mandarinpay_form" class="">
    
	<input type="hidden" name="email" value     ="{$email}" />
    <input type="hidden" name="merchantId" value="{$merchantId}" />
	<input type="hidden" name="orderId" value   ="{$orderId}" />
	<input type="hidden" name="price" value     ="{$price}" />
	<input type="hidden" name="sign" value      ="{$sign}" />
	

    <p class="cart_navigation clearfix" id="cart_navigation">
        <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" class="button-exclusive btn btn-default">
            <i class="icon-chevron-left"></i>{l s='Other payment methods' mod='mandarinpay'}
        </a>
        <button type="submit" class="button btn btn-default button-medium">
            <span>{l s='I confirm my order' mod='mandarinpay'}<i class="icon-chevron-right right"></i></span>
        </button>
    </p>
	
</form>
<!--|CONFIRMATION.TPL|-->
