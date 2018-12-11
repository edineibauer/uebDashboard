<div class="col relative">
    <header class="container">
        <h5>
            <b><i class="material-icons left padding-right">dashboard</i> <span class="left">Meu Painel</span></b>
        </h5>
    </header>

    {if $dashboard != ""}
        {$dashboard}
    {else}
        <div class="row padding-32 align-center">
            <h2>Seja Bem-Vindo(a) <?=$_SESSION['userlogin']['nome']?></h2>
        </div>
    {/if}

    <div class="col padding-large">
        {if !empty($note)}
            {foreach item=n key=i from=$note}
                <div class="col s12 m4 padding-small">
                    <article class="card color-white">
                        <header class="col padding-12 {($n.status == '1') ? "theme-l3" : (($n.status == '2') ? "theme-l1" : "theme-d1")}">
                            <span class="col right pointer close-dashboard-note" id="{$n.id}" style="width: 30px">
                                <i class="material-icons">close</i>
                            </span>
                            <h1 class="font-medium padding-left upper font-light padding-4 rest">{$n.titulo}</h1>
                        </header>
                        <div class="col">
                            <div class="col padding-large padding-4 font-small font-light">
                                {$n.data}
                            </div>
                            <p class="col padding-large padding-4">
                                {$n.descricao}
                            </p>
                        </div>
                    </article>
                </div>
            {/foreach}
        {/if}
    </div>


    <!--
    <div class="row-padding margin-bottom">
        <div class="quarter">
            <div class="container color-red padding-16">
                <div class="left"><i class="fa fa-comment font-xxxlarge"></i></div>
                <div class="right">
                    <h3>52</h3>
                </div>
                <div class="clear"></div>
                <h4>Messages</h4>
            </div>
        </div>
        <div class="quarter">
            <div class="container color-blue padding-16">
                <div class="left"><i class="fa fa-eye font-xxxlarge"></i></div>
                <div class="right">
                    <h3>99</h3>
                </div>
                <div class="clear"></div>
                <h4>Views</h4>
            </div>
        </div>
        <div class="quarter">
            <div class="container color-teal padding-16">
                <div class="left"><i class="fa fa-share-alt font-xxxlarge"></i></div>
                <div class="right">
                    <h3>23</h3>
                </div>
                <div class="clear"></div>
                <h4>Shares</h4>
            </div>
        </div>
        <div class="quarter">
            <div class="container color-orange color-text-white padding-16">
                <div class="left"><i class="fa fa-users font-xxxlarge"></i></div>
                <div class="right">
                    <h3>50</h3>
                </div>
                <div class="clear"></div>
                <h4>Users</h4>
            </div>
        </div>
    </div>

    <hr>
    <div class="container">
        <h5>General Stats</h5>
        <p>New Visitors</p>
        <div class="grey">
            <div class="container center padding color-green" style="width:25%">+25%</div>
        </div>

        <p>New Users</p>
        <div class="grey">
            <div class="container center padding color-orange" style="width:50%">50%</div>
        </div>

        <p>Bounce Rate</p>
        <div class="grey">
            <div class="container center padding color-red" style="width:75%">75%</div>
        </div>
    </div>
    <hr>
    <div class="container">
        <h5>Recent Users</h5>
        <ul class="ul card-4 color-white">
            <li class="padding-16">
                <img src="/w3images/avatar2.png" class="left radius-circle margin-right" style="width:35px">
                <span class="font-xlarge">Mike</span><br>
            </li>
            <li class="padding-16">
                <img src="/w3images/avatar5.png" class="left radius-circle margin-right" style="width:35px">
                <span class="font-xlarge">Jill</span><br>
            </li>
            <li class="padding-16">
                <img src="/w3images/avatar6.png" class="left radius-circle margin-right" style="width:35px">
                <span class="font-xlarge">Jane</span><br>
            </li>
        </ul>
    </div>
    <hr>

    <div class="container color-grey-dark padding-32">
        <div class="row">
            <div class="container third">
                <h5 class="bottombar color-border-green">Demographic</h5>
                <p>Language</p>
                <p>Country</p>
                <p>City</p>
            </div>
            <div class="container third">
                <h5 class="bottombar color-border-red">System</h5>
                <p>Browser</p>
                <p>OS</p>
                <p>More</p>
            </div>
            <div class="container third">
                <h5 class="bottombar color-border-orange">Target</h5>
                <p>Users</p>
                <p>Active</p>
                <p>Geo</p>
                <p>Interests</p>
            </div>
        </div>
    </div>
    -->

    <div class="dash-version space-header">v.{$version}</div>
</div>