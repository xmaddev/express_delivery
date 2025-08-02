{assign var="data_id" value=$data_id|default:"store_locator_list_"|cat:rand()}

{if $item_ids && !$item_ids|is_array}
    {$item_ids = ","|explode:$item_ids}
{/if}

{script src="js/tygh/picker.js"}

<div class="clearfix">
    {if !$multiple}
        <div class="choose-input">
    {/if}

    {capture name="add_buttons"}
    
        {if $multiple}
            {$display = "checkbox"}
        {else}
            {$display = "radio"}
        {/if}

        <div class="{if !$multiple}choose-icon input-append{else}buttons-container{/if}">
            {include file="buttons/button.tpl"
                but_id="opener_picker_`$data_id`"
                but_href="express_delivery.store_locator_picker?display={$display}&select_mode={if $multiple}multiple{else}single{/if}&checkbox_name={$input_name}&data_id={$data_id}"|fn_url
                but_text=__("add_store_locations")
                but_role="add"
                but_icon="icon-plus"
                but_target_id="content_{$data_id}"
                but_meta="cm-dialog-opener add-on btn"
                method="GET"
            }
        </div>

        <div class="hidden"
             id="content_{$data_id}"
             title="{__("add_store_locations")}"
        ></div>
    {/capture}

    {$smarty.capture.add_buttons nofilter}

    <input type="hidden"
           class="cm-picker-value"
           id="a{$data_id}_ids"
           name="{$input_name}"
           value="{if $item_ids}{","|implode:$item_ids}{/if}" />

    <div class="table-wrapper">
        <table width="100%" class="table table-middle table--relative">
            <thead>
                <tr>
                    <th>{__("name")}</th>
                    <th width="5%"></th>
                </tr>
            </thead>
            <tbody id="{$data_id}"
                {if !$item_ids}class="hidden"{/if}>
                {foreach from=$item_ids item=store_location_id}
                    {include file="pickers/store_locator/js.tpl"
                        store_location_id=$store_location_id
                        holder=$data_id
                        input_name=$input_name
                        hide_input=true
                        clone=true
                    }
                {/foreach}
                
                {include file="pickers/store_locator/js.tpl"
                    store_location_id="{$ldelim}store_location_id{$rdelim}"
                    holder=$data_id
                    input_name=$input_name
                    hide_input=true
                    clone=true
                }
            </tbody>

        </table>
    </div>

    {if !$multiple}
        </div>
    {/if}
</div>
