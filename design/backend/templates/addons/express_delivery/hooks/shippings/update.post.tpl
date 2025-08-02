
<div class="control-group">
    <label class="control-label" for="elm_is_express_delivery"
    >{__("is_express_delivery")}:</label>
    <div class="controls">
        <input type="hidden"
               name="shipping_data[is_express_delivery]"
               value="N"
        />
        <input type="checkbox"
               name="shipping_data[is_express_delivery]"
               id="is_express_delivery"
               {if $shipping.is_express_delivery|default:"Y" == "Y"}checked="checked"{/if}
               value="Y"
        />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_express_hide_icon"
    >{__("express_hide_icon")}:</label>
    <div class="controls">
        <input type="hidden"
               name="shipping_data[express_hide_icon]"
               value="N"
        />
        <input type="checkbox"
               name="shipping_data[express_hide_icon]"
               id="express_hide_icon"
               {if $shipping.express_hide_icon|default:"Y" == "Y"}checked="checked"{/if}
               value="Y"
        />
    </div>
</div>

{if $shipping.is_express_delivery === 'Y'}
    <div class="control-group">
        <label class="control-label" for="express_store_locations">{__("express_store_locations")}:</label>
        <div class="controls">
            {include file="pickers/store_locator/picker.tpl"
                input_name="shipping_data[express_store_locations]"
                item_ids=$shipping.express_store_locations|default:[]
                multiple=true
                default_name=__("all_stores")
            }
            <p class="muted description">{__("express_store_locations_tooltip")}</p>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="express_categories">{__("express_delivery_categories")}:</label>
        <div class="controls">
            {include file="pickers/categories/picker.tpl"
                input_name="shipping_data[express_categories]"
                item_ids=$shipping.express_categories|default:[]
                multiple=true
                show_active_path=true
                default_name=__("all_categories")
            }
            <p class="muted description">{__("express_delivery_categories_tooltip")}</p>
        </div>
    </div>
{/if}
