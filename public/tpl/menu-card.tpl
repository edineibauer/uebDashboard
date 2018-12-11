<{($action == "link")? "a href='{$file}' target='_blank'" : "div"} class="col padding-medium">
    <div class="col align-center border padding-medium hover-text-theme color-grey-light opacity radius pointer hover-opacity-off menu-li"
         style="margin-bottom: 5px"
         data-action="{$action}"
         {if $action == "table"}
             data-entity='{$entity}'
         {elseif $action == "form"}
             data-atributo='{$file}' data-lib="{$lib}"
         {elseif $action == "page"}
             data-atributo='{$file}'
         {/if}
    >
        <i class="font-xxxlarge material-icons">{$icon}</i>
        <span class="font-large col">{$title}</span>
    </div>
</{($action == "link")? "a" : "div"}>