{if not $isLogged}
<div id="fb-login-button">
    <a href='#'><img alt="" class="" height="21" width="169" src="/modules/facebookpack/fb_connect.gif" /></a>
</div>
{elseif $fbUser}
<div id="fb-picture">
    <img src="https://graph.facebook.com/{$fb_user_id}/picture" height="50" width="50" />
</div>
{/if}