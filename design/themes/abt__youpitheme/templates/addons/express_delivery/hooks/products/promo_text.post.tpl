{if $product.express_delivery_available && !empty($product.express_notification)}
    <div class="alert alert-success alert-dismissable" style="border-radius:12px;padding:8px 28px;">
        {$product.express_notification nofilter}
    </div>
{/if}
