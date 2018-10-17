{if !empty($error)}
<div class="error-box">
	<h1>{'common.error'|devblocks_translate|capitalize}</h1>
	<p>{$error}</p>
</div>
{/if}

<form action="{devblocks_url}c=login&ext=openid&a=discover{/devblocks_url}" method="post" id="loginOpenID">
<input type="hidden" name="email" value="{$worker->getEmailString()}">

<fieldset>
	<legend>Sign on using OpenID</legend>
	
	<b>Email:</b><br>
	{$worker->getEmailString()}
	(<a href="{devblocks_url}c=login&a=reset{/devblocks_url}" tabindex="-1">change</a>)
	<br>
	<br>
	
	<div style="clear:both;">
		<b>Enter your OpenID:</b><br>
		<input type="text" name="openid_url" size="45" class="input_openid">
		<button type="submit">{'header.signon'|devblocks_translate|capitalize}</button>
		
		&nbsp; 
		
		<a href="{devblocks_url}c=login&a=recover{/devblocks_url}?email={$worker->getEmailString()}" tabindex="-1">can't log in?</a>
	</div>
</fieldset>
</form>

<script type="text/javascript">
	$(function() {
		$('#loginOpenID input:text').first().focus().select();
	});
</script>