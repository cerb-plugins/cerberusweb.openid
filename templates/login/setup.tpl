<form action="{devblocks_url}c=login&ext=openid&a=setup{/devblocks_url}" method="POST" id="myAccountOpenId">
<input type="hidden" name="email" value="{$worker->email}">

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
	<legend>Step 1: Type the confirmation code that was sent to {$worker->email}</legend>
	
	<input type="text" name="confirm_code" value="{$code}" size="10" maxlength="8" autocomplete="off">
</fieldset>

<fieldset>
<legend>Step 2: Choose an OpenID Identity</legend>	
<ul style="margin:5px 0px 0px 10px;padding:0px;list-style:none;">
	<li style="padding-bottom:10px;">
		<b>Add:</b>
		<input type="text" name="openid_url" size="45" style="background:url('{devblocks_url}c=resource&p=cerberusweb.openid&f=images/openid-inputicon.gif{/devblocks_url}') no-repeat scroll 5px 50% #ffffff;padding-left:25px;">
		<div>
			<a href="javascript:;" style="float:left;margin-right:5px;border:1px solid rgb(230,230,230);width:100px;height:50px;background:url('{devblocks_url}c=resource&p=cerberusweb.openid&f=images/providers/google.gif{/devblocks_url}') no-repeat scroll center center;" onclick="$('#myAccountOpenId input:text[name=openid_url]').val('https://www.google.com/accounts/o8/id')"></a>
			<a href="javascript:;" style="float:left;margin-right:5px;border:1px solid rgb(230,230,230);width:100px;height:50px;background:url('{devblocks_url}c=resource&p=cerberusweb.openid&f=images/providers/yahoo.gif{/devblocks_url}') no-repeat scroll center center;" onclick="$('#myAccountOpenId input:text[name=openid_url]').val('https://me.yahoo.com');"></a>
			<a href="javascript:;" style="float:left;margin-right:5px;border:1px solid rgb(230,230,230);width:100px;height:50px;background:url('{devblocks_url}c=resource&p=cerberusweb.openid&f=images/providers/verisign_pip.gif{/devblocks_url}') no-repeat scroll center center;" onclick="$('#myAccountOpenId input:text[name=openid_url]').val('http://pip.verisignlabs.com');"></a>
			<a href="javascript:;" style="float:left;margin-right:5px;border:1px solid rgb(230,230,230);width:100px;height:50px;background:url('{devblocks_url}c=resource&p=cerberusweb.openid&f=images/providers/myopenid.gif{/devblocks_url}') no-repeat scroll center center;" onclick="$('#myAccountOpenId input:text[name=openid_url]').val('http://myopenid.com');"></a>
		</div>
	</li>
</ul>
</fieldset>

<button type="submit" name="do_submit" value="1"><span class="cerb-sprite2 sprite-tick-circle"></span> {'common.continue'|devblocks_translate|capitalize}</button>

</form>
