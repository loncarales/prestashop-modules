<div id="fb-root"></div>
{if $fbPack_app_id}
{literal}
<script type="text/javascript">
window.fbAsyncInit = function() {
	FB.init({ 
    	appId: '{/literal}{$fbPack_app_id}{literal}',
        status : true, // check login status
        cookie : true, // enable cookies to allow the server to access the session
        xfbml : true, // parse XFBML
        oauth : true //enables OAuth 2.0
	});
    
    {/literal}{if $fbPack_comments}{literal}
    FB.Event.subscribe('comment.create', function(response) {
        FB.XFBML.parse($('#fb_count').get(0));
    });
    
    FB.Event.subscribe('comment.remove', function(response) {
        FB.XFBML.parse($('#fb_count').get(0));
    });
    {/literal}{/if}{literal}
    
    {/literal}{if $fbPack_login_button}{literal}
    
        // Am i Logged
        if ({/literal}{$isLogged}{literal}) {
            // bind logout
            $('#header_user a').click(function() {
                window.location.href = "http://{/literal}{$smarty.server.HTTP_HOST}{literal}/modules/facebookpack/fb-logout.php";
            });
        } else {
            // bind login
            $('#fb-login-button a').click(function(){
                FB.login(function(response) {
                    if (response.authResponse) {
                        window.location.href = "http://{/literal}{$smarty.server.HTTP_HOST}{literal}/modules/facebookpack/fb-login.php";
                    }
                }, {scope: 'email,user_birthday'});
            });
        }
    {/literal}{/if}{literal}
    
};
(function() {
    var e = document.createElement('script');
    e.src = document.location.protocol + '//connect.facebook.net/{/literal}{$fbPack_app_locale}{literal}/all.js';
    e.async = true;
    document.getElementById('fb-root').appendChild(e);
}());
</script>
{/literal}
{else}
{literal}
<script type="text/javascript">
(function(d, s, id) {
	  var js, fjs = d.getElementsByTagName(s)[0];
	  if (d.getElementById(id)) {return;}
	  js = d.createElement(s); js.id = id;
	  js.src = "//connect.facebook.net/{/literal}{$fbPack_app_locale}{literal}/all.js#xfbml=1";
	  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
</script>
{/literal}
{/if}