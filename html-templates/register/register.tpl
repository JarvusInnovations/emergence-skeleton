{extends "designs/site.tpl"}

{block "title"}Register &mdash; {$dwoo.parent}{/block}

{block "user-tools"}{/block} {* redundant *}

{block "js-top"}
    {$dwoo.parent}

    {if RemoteSystems\ReCaptcha::$siteKey}
        <script src='https://www.google.com/recaptcha/api.js'></script>
    {/if}
{/block}

{block "content"}
    {$User = $data}
    {$errors = $User->validationErrors}

    <header class="page-header">
        <h1 class="header-title title-1">Register a New Account</h1>
    </header>

    <form method="POST" class="register-form">
        {if $errors}
            <div class="notify error">
                <strong>Please double-check the fields highlighted below.</strong>
            </div>
        {/if}

        <fieldset class="shrink">
            <div class="inline-fields">
                {field
                    inputName=FirstName
                    label='First Name'
                    error=$errors.FirstName
                    required=true
                    autofocus=true
                }

                {field
                    inputName=LastName
                    label='Last Name'
                    error=$errors.LastName
                    required=true
                }
            </div>

            {field
                inputName=Email
                label='Email Address'
                error=$errors.Email
                type=email
                required=true
            }

            {field
                inputName=Username
                label='Username'
                error=$errors.Username
                required=true
                attribs='autocapitalize="none" autocorrect="off"'
            }

            <div class="inline-fields">
                {field
                    inputName=Password
                    label='Password'
                    error=$errors.Password
                    type=password
                    required=true
                }

                {field
                    inputName=PasswordConfirm
                    label='(Confirm)'
                    error=$errors.PasswordConfirm
                    type=password
                    required=true
                }
            </div>

            {if RemoteSystems\ReCaptcha::$siteKey}
                <div class="field {tif $errors.ReCaptcha ? 'has-error'}">
                    <span class="field-label"></span>
                    <div class="field-control g-recaptcha" data-sitekey="{RemoteSystems\ReCaptcha::$siteKey|escape}"></div>
                    {if $errors.ReCaptcha}
                        <p class="error-text">{$errors.ReCaptcha|escape}</p>
                    {/if}
                </div>
            {/if}

            <div class="submit-area">
                <button class="submit" type="submit">Create Account</button>
                <span class="submit-text">or <a href="/login{tif $.request.return ? cat('?return=', escape($.request.return, url))}">Log In</a></span>
            </div>
        </fieldset>
    </form>
{/block}