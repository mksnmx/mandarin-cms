<style>
    p.payment_module a.mandarinpay {
        background: url("{$base_dir_ssl}modules/mandarinpay/64x64.png") 15px 15px no-repeat #fbfbfb;
    }
</style>
<div class="row">
    <div class="col-xs-12">
        <p class="payment_module">
            <a class="bankwire mandarinpay" href="{$link->getModuleLink('mandarinpay', 'payment', [], true)|escape:'html'}" title="{l s='Pay with MandarinPay' mod='mandarinpay'}">
                
                {l s='Pay online with credit card' mod='mandarinpay'}
            </a>
        </p>
    </div>
</div>