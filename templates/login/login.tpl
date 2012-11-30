{if !empty($error)}
<div class="error-box">
	<h1>Error</h1>
	<p>{$error}</p>
</div>
{/if}

<form action="{devblocks_url}c=login&ext=openid&a=discover{/devblocks_url}" method="post" id="loginOpenID">
<input type="hidden" name="email" value="{$worker->email}">

<fieldset>
	<legend>Sign on using OpenID</legend>
	
	<b>Email:</b><br>
	{$worker->email}
	(<a href="{devblocks_url}c=login&a=reset{/devblocks_url}" tabindex="-1">change</a>)
	<br>
	<br>
	
	<b>Log in with one of these providers:</b><br>
	<div>
		<a href="javascript:;" style="float:left;margin-right:5px;border:1px solid rgb(230,230,230);width:100px;height:50px;background:url('{devblocks_url}c=resource&p=cerberusweb.openid&f=images/providers/google.gif{/devblocks_url}') no-repeat scroll center center;" onclick="$('#loginOpenID input:text[name=openid_url]').val('https://www.google.com/accounts/o8/id').closest('form').submit();"></a>
		<a href="javascript:;" style="float:left;margin-right:5px;border:1px solid rgb(230,230,230);width:100px;height:50px;background:url('{devblocks_url}c=resource&p=cerberusweb.openid&f=images/providers/yahoo.gif{/devblocks_url}') no-repeat scroll center center;" onclick="$('#loginOpenID input:text[name=openid_url]').val('https://me.yahoo.com').closest('form').submit();"></a>
		<a href="javascript:;" style="float:left;margin-right:5px;border:1px solid rgb(230,230,230);width:100px;height:50px;background:url('{devblocks_url}c=resource&p=cerberusweb.openid&f=images/providers/verisign_pip.gif{/devblocks_url}') no-repeat scroll center center;" onclick="$('#loginOpenID input:text[name=openid_url]').val('http://pip.verisignlabs.com').closest('form').submit();"></a>
		<a href="javascript:;" style="float:left;margin-right:5px;border:1px solid rgb(230,230,230);width:100px;height:50px;background:url('{devblocks_url}c=resource&p=cerberusweb.openid&f=images/providers/myopenid.gif{/devblocks_url}') no-repeat scroll center center;" onclick="$('#loginOpenID input:text[name=openid_url]').val('http://myopenid.com').closest('form').submit();"></a>
	</div>
	
	<div style="clear:both;">
		<b>Or enter your own OpenID:</b><br>
		<input type="text" name="openid_url" size="45" class="input_openid">
		<button type="submit">{$translate->_('header.signon')|capitalize}</button>
		
		 &nbsp; 
		
		<a href="{devblocks_url}c=login&a=recover{/devblocks_url}?email={$worker->email}" tabindex="-1">can't log in?</a>
	</div>
</fieldset>
</form>

<script type="text/javascript">
	$(function() {
		$('#loginOpenID input:text').first().focus().select();
	});
</script>