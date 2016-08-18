<% if getGACode %>
	<script>
		var _gaq=[['_setAccount','$getGACode'],['_trackPageview']];
		_gaq.push(['_setAllowLinker', true]);
		(function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];g.async=1;
		g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
		s.parentNode.insertBefore(g,s)}(document,'script'));
	</script>
<% end_if %>