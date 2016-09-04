<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
	<head>
		<% base_tag %>
		$MetaTags(true)
		<% include OG %>
		<meta name="viewport" content="width=device-width">

		$getCSS
		
		<script src="$ThemeDir/js/lib/modernizr.min.js"></script>
		
		<% include GA %>
	</head>
	<body class="page-$URLSegment<% if $isMobile %> mobile<% end_if %> page-type-$BodyClass.LowerCase">
		
		
		<main id="main" class="container">
			$Layout
		</main>
		
		<script type="text/javascript">$Environments</script>
		<script type="text/babel" src="/themes/default/js/panel-js/panel.js"></script>
	</body>
</html>