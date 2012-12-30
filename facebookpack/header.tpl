{if $fbPack_comments}
<meta property="fb:app_id" content="{$fbPack_app_id}"/>
<meta property="fb:admins" content="{$fbPack_comments_moderators}"/>
{/if}
{if $fbPack_login_button}
{literal}
<style type="text/css">
/* block facebook connect */
#fb-login-button, #fb-picture {
    float: right;
    margin-right: 0.8em;
}
</style>
{/literal}
{/if}