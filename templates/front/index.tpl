{config_load file=$LANGUAGE_FILE}
<!Doctype html>

<html lang=it>
<!-- Head del sito -->
{include file=$head_placeholder}
<!-- HEAD -->

<!-- BODY -->
<body>
	<!--HEADER-->
	<header>
		{include file=$header_placeholder}
	</header>
	<!--MAIN-->
	<main>
        {if isset($error_placeholder)}
		    {include file=$error_placeholder}
        {/if}
		{include file=$home_body_placeholder}
	</main>
	<!--FOOTER-->
		{include file=$footer_placeholder}
	</footer>
</body>
<!-- BODY -->
</html>