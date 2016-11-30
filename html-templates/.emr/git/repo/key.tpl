{extends "design.tpl"}


{block "content"}
    <ol class="breadcrumb">
        <li><a href="/.emr/git">Git</a></li>
        <li><a href="/.emr/git/{$Repo->ID}">{$Repo->ID}</a></li>
        <li class="active">Deploy Key</li>
    </ol>

    <div class="navbar-form navbar-left">
        <h2>
            {$Repo->ID}
        </h2>
    </div>
    <div class="clearfix"></div>
    
    {assign $Repo->getPublicFingerprint() Fingerprint}
    
    <div class="container">
        
        <h3><i class="glyphicon glyphicon-lock"></i> Deploy Key Configuration</h3>
    
        {if !$Repo->privateKeyExists()}
            <div class="alert alert-info" role="alert">You currently have no key configured. All Git remote functions will proceed without providing authentication credentials.</div>
        {else}
            <div class="container">
                <div class="container">
                    <p>A key has already been configured for this repository.</p>
                    <p>Use this public key to identify this deployment to your server.</p>
                    <p><a href="https://developer.github.com/guides/managing-deploy-keys/#deploy-keys">See this guide for adding a deploy key to a GitHub repository</a></p>
                    <pre>{file_get_contents($Repo->getPublicKeyPath())}</pre>
                    <div class="input-group">
                        <label class="input-group-addon" for="fingerprint">Fingerprint</label>
                        <input id="fingerprint" class="form-control" type="text" disabled value="{$Fingerprint.1}">
                    </div>
                </div>
            </div>
        {/if}
        
        <div class="container">
            <form method="post">
            
                {if $Repo->privateKeyExists()}
                <h3><i class="glyphicon glyphicon-lock"></i> New Key</h3>
                {/if}
                
                <div class="container">
                
                    <p>Add the below generated public key to your git server before continuing, or paste your own key public/private key pair.</p>
                
                    <div class="form-group">
                        <div class="col-sm-7 col-sm-offset-2">
                            <label>Private Key</label>
                            <textarea name="privateKey" class="form-control" style="height: 300px;">{$PrivateKey}</textarea>
                        </div>
                        <div class="col-sm-7 col-sm-offset-2">
                            <label>Public Key</label>
                            <textarea name="publicKey" class="form-control" style="height: 300px;">{$PublicKey}</textarea>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="form-group text-right col-sm-7 col-sm-offset-2">
                        <div class="btn-group">
                            <button class="btn btn-default" id="js-commit"><i class="glyphicon glyphicon-save"></i> Set Key</button>
                        </div>
                    </div>
    
                </div>
            </form>
        </div>
        
    </div>
    

            
{/block}

{block "js-bottom"}
    {$dwoo.parent}
    {*jsmin ".emr/git/key.js"*}
{/block}