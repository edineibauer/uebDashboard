<{($action == "link")? "a href='{$file}' target='_blank'" : "div"}
class='menu-li menu-dashboard-lista theme-hover bar-item button z-depth-0 padding-small'
data-action="{$action}"
{if $action == "table"}
    data-entity='{$entity}'
{elseif $action == "form"}
    data-atributo='{$file}' data-lib="{$lib}"
{elseif $action == "page"}
    data-atributo='{$file}'
{/if}
>
<i class='material-icons left'>{$icon}</i>
<span class='left padding-tiny padding-left'>{$title}</span>
</{($action == "link")? "a" : "div"}>
