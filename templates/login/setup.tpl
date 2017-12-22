<form action="{devblocks_url}c=login&ext=openid&a=setup{/devblocks_url}" method="POST" id="myAccountOpenId">
<input type="hidden" name="email" value="{$worker->getEmailString()}">

<div class="help-box">
	<h1>You need to finish setting up your account</h1>
	
	<p>
		Access to your account requires OpenID authentication.  You haven't set this up yet.
	</p>
		
	<p>
		To finish setting up your account, please follow the simple steps below.
	</p>
</div>

<fieldset>
	<legend>Step 1: Type the confirmation code that was sent to {$worker->getEmailString()}</legend>
	
	<input type="text" name="confirm_code" value="{$code}" size="10" maxlength="8" autocomplete="off">
</fieldset>

<fieldset>
<legend>Step 2: Choose an OpenID Identity</legend>	
<ul style="margin:5px 0px 0px 10px;padding:0px;list-style:none;">
	<li style="padding-bottom:10px;">
		<b>Add:</b>
		<input type="text" name="openid_url" size="45" style="background:url('{devblocks_url}c=resource&p=cerberusweb.openid&f=images/openid-inputicon.gif{/devblocks_url}') no-repeat scroll 5px 50% #ffffff;padding-left:25px;">
	</li>
</ul>
</fieldset>

<button type="submit" name="do_submit" value="1"><span class="glyphicons glyphicons-circle-ok" style="color:rgb(0,180,0);"></span> {'common.continue'|devblocks_translate|capitalize}</button>

</form>
