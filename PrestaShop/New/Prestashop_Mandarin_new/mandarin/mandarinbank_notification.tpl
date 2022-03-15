
{if $status == 'success'}
    <p>{l s='Ваш заказ ' mod='mandarinbank'} <span class="bold">{$shop_name}</span> {l s='успешно оплачен.' mod='mandarinbank'}
        <br /><br /><span class="bold">{l s='Ваш заказ будет доставлен так быстро, как это возможно.' mod='mandarinbank'}</span>
    </p>
    {else}
    <p class="failed">
        {l s='Ваш заказ не был оплачен. Если вам кажется что это была ошибка, свяжитесь с нами.' mod='mandarinbank'}
        <a href="{$link->getPageLink('contact', true)}">{l s='Поддержка клиентов' mod='mandarinbank'}</a>.
    </p>
{/if}
<br /><br />{l s='В случае любых вопросов свяжитесь с нами' mod='mandarinbank'} <a href="{$link->getPageLink('contact', true)}">{l
    s='Поддержка клиентов' mod='mandarinbank'}</a>.
<br /><br />{l s='Вы можете просмотреть свою ' mod='mandarinbank'} <a href="{$link->getPageLink('history', true)}">{l
    s='Историю заказов' mod='mandarinbank'}</a>.