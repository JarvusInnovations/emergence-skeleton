{extends "designs/site.tpl"}

{block "title"}Edit Profile &mdash; {$dwoo.parent}{/block}

{block "css"}
    <link rel="stylesheet" type="text/css" href="/css/dog/photos.css" />
    <link rel="stylesheet" type="text/css" href="/css/forms.css">
    {$dwoo.parent}
{/block}

{block "app-menu"}
    <a href="/{MICS::getApp()}" class="page open">Edit My Profile</a>
    <a href="/{MICS::getApp()}/view" class="page">View Profile</a>
{/block}

{block "content"}

    {$User = $data}

    <h1>Manage {tif $User->ID == $.User->ID ? 'Your' : $User->FullNamePossessive} Profile</h1>
    <hr class="clear" />

    {if $.get.status == 'photoUploaded'}
        <p class="status highlight">Photo uploaded.</p>
    {elseif $.get.status == 'photoPrimaried'}
        <p class="status highlight">Default photo selected.</p>
    {elseif $.get.status == 'photoDeleted'}
        <p class="status highlight">Photo deleted.</p>
    {elseif $.get.status == 'passwordChanged'}
        <p class="status highlight">Password changed.</p>
    {elseif $.get.status == 'saved'}
        <p class="status highlight">Profile saved.</p>
    {/if}


    <form id="uploadPhotoForm" class="generic" action="/profile/uploadPhoto?{refill_query}" method="POST" enctype="multipart/form-data">

        <fieldset class="section">
            <legend>Photos</legend>
            {strip}
            <div class="photosGallery clearfix">
                {foreach item=Photo from=$User->Photos}
                    <div class="photo {if $Photo->ID == $User->PrimaryPhotoID}highlight{/if}">
                        <div class="photothumb"><img src="{$Photo->getThumbnailRequest(100,100)}"></div>
                        {*<input type="text" name="Caption[{$Photo->ID}]" class="caption" value="{$Photo->Caption|escape}">*}
                        <div class="buttons">
                            <span>{if $Photo->ID != $.Session->Person->PrimaryPhotoID}
                                <a href="/profile/primaryPhoto?{refill_query MediaID=$Photo->ID}" alt="Make Default" title="Make Default"><img src="/img/icons/fugue/user-silhouette.png" alt="Make Default" /></a>
                            {else}
                                <img src="/img/icons/fugue/user-silhouette.png" alt="Default Photo" class="nofade" />Default
                            {/if}</span>
                            <a href="/profile/deletePhoto?{refill_query MediaID=$Photo->ID}" alt="Delete Photo" title="Delete Photo" onclick="return confirm('Are you sure you want to delete this photo from your profile?');"><img src="/img/icons/fugue/slash.png" alt="Delete Photo" /></a>
                        </div>
                    </div>
                {/foreach}
            </div>
            {/strip}

            <div class="field upload">
                <input type="file" name="photoFile" id="photoFile">
            </div>
            <div class="submit">
                <input type="submit" class="submit inline" value="Upload New Photo">
            </div>
        </fieldset>
    </form>

    <form method="POST" id="profileForm" class="generic">
        {if ProfileRequestHandler::$accountLevelEditOthers && $.User->hasAccountLevel(ProfileRequestHandler::$accountLevelEditOthers)}
            <h2 class="legend">Account Settings ({ProfileRequestHandler::$accountLevelEditOthers} only)</h2>
            <fieldset class="section">
                <div class="field">
                    <label>
                        Username
                        <input type="text" class="text" name="Username" value="{refill field=Username default=$User->Username}">
                    </label>
                </div>

                <div class="field">
                    <label>
                        Account Level
                        <select name="AccountLevel">
                            {foreach item=level from=User::getFieldOptions(AccountLevel, values)}
                                <option {refill field=AccountLevel default=$User->AccountLevel selected=$level}>{$level}</option>
                            {/foreach}
                        </select>
                    </label>
                </div>

                <div class="field">
                    <label>
                        Person subclass
                        <select name="Class">
                            {foreach item=class from=User::getFieldOptions(Class, values)}
                                <option {refill field=Class default=$User->Class selected=$class}>{$class}</option>
                            {/foreach}
                        </select>
                    </label>
                </div>

                <div class="submit">
                    <input type="submit" class="submit" value="Save account">
                </div>
            </fieldset>
        {/if}

        <h2 class="legend">Profile Details</h2>
        <fieldset class="section">
            <div class="field">
                <label for="Location">Location</label>
                <input type="text" class="text" id="Location" name="Location" value="{refill field=Location default=$User->Location}">
            </div>

            <div class="field expand">
                <label for="about">About</label>
                <textarea id="about" name="About">{refill field=About default=$User->About}</textarea>
                <p class="hint">Check out the <a href="/pages/Formatting_Guide">Formatting Guide</a> to give your text some style</p>
            </div>

            <div class="submit">
                <input type="submit" class="submit" value="Save profile">
            </div>

        </fieldset>


        <h2 class="legend">Contact Information</h2>
        <fieldset class="section">
            <div class="field">
                <label for="Email">Email</label>
                <input type="email" class="text" id="Email" name="Email" value="{refill field=Email default=$User->Email}">
            </div>

            <div class="field">
                <label for="Phone">Phone</label>
                <input type="tel" class="text" id="Phone" name="Phone" value="{refill field=Phone default=$User->Phone modifier=phone}">
            </div>

            <div class="submit">
                <input type="submit" class="submit" value="Save profile">
            </div>

        </fieldset>
    </form>



    <form action="/profile/password{refill_query}" method="POST" id="passwordForm" class="generic">
        <h2 class="legend">Change Password</h2>
        <fieldset class="section">
            <div class="field">
                <label for="oldpassword">Old Password</label>
                <input type="password" class="text" id="oldpassword" name="OldPassword">
            </div>
            <div class="field">
                <label for="password">New Password</label>
                <input type="password" class="text" id="password" name="Password">
                <input type="password" class="text" id="password2" name="PasswordConfirm">
                <p class="hint">Please type your new password in both boxes above to make sure it is correct.</p>
            </div>

            <div class="submit">
                <input type="submit" class="submit" value="Save new password">
            </div>
        </fieldset>
    </form>

{/block}