<{($action == "link")? "a href='{$file}' " : "div"} class="card left padding-0 hover-text-theme color-grey-light pointer hover-shadow" style="width: 200px; max-width: 48%; margin: 1%">
    <div class="col align-center padding-large menu-li color-white"
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