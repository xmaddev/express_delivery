{if $product.express_delivery_available}
    {* Получаем URL изображения из настроек модуля *}
    {if $product.express_delivery_icon}
        {assign var="sticker_url" value=$product.express_delivery_icon}
    {/if}

    <div class="ty-product-labels__item ty-product-labels__item-image cm-tooltip" 
         title="{$product.express_delivery_shipping_title|default:__("express_delivery_available")}"
         style="background-image: url('{$sticker_url}');">
    </div>
{/if}

