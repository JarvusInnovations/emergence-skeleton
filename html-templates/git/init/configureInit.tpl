<h1>Initialize repository {$layer->getId()}</h1>
<pre>{$layer->getConfig()|var_export:true|escape}</pre>
<form method="POST">
	{if !$layer->isRemoteHttp()}
		<label>
			Deploy private key (optional):<br>
			<textarea name="privateKey" rows="30" cols="65" placeholder="-----BEGIN RSA PRIVATE KEY-----
	-----END RSA PRIVATE KEY-----"></textarea>
		</label>
	{/if}
	<p><input type="submit" value="Create repo"></p>
</form>