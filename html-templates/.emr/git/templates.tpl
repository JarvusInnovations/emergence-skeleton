{template fileBlock Files}
    <div class="emr-git-files">
        <form method="POST">
            <fieldset class="form-group">
                <ul>
                    {foreach from=$Files item=File}
                    <div class="checkbox">
                        <label class="file{if $File.Staged} file-staged{/if}">
                            <input type="checkbox" name="Files[]" value="{$File.File|escape}">
                            <span class="code">{if ord($File.Code.0) != 32}{$File.Code.0}{else}&nbsp;{/if}{$File.Code.1}</span>
                            <span class="path">{if $File.OriginalFile}{$File.OriginalFile} ->{/if}{$File.File}</span>    
                            <div class="btn-group text-right" data-file="{$File.File}">
                                {*<button type="button" class="btn btn-xs navbar-btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" alt="Diff a File">
                                    Diff
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu diff-dropdown">
                                    <li><a href="#" class="js-diff-remote-head">Remote HEAD</a></li>
                                    <li><a href="#" class="js-diff-local-head">Local HEAD</a></li>
                                    <li role="separator" class="divider"></li>
                                    {for i 1 5}
                                    <li><a href="#" class="js-diff-behind-head" data-behind="{$i}">{$i} commit{if $i>1}s{/if} behind HEAD</a></li>
                                    {/for}
                                </ul>*}
                                <button type="button" class="btn btn-xs navbar-btn js-reset-file">Reset</button>
                                {if !$File.Staged}<button type="button" class="btn btn-xs navbar-btn js-stage-file">Stage</button>{/if}
                            </div>
                        </label>
                    </div>
                    {/foreach}
                </ul>
            </fieldset>
        </form>
    </div>
{/template}