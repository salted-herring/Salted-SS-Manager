<nav id="main_nav">
	<ul>
		<% loop Menu(1) %>
			<li>
				<a href="$Link" class="<% if LinkOrCurrent = current || $LinkOrSection = section %>current<% end_if %>">$MenuTitle.XML</a>
				<% if Children %>
				<ul class="children">
					<% loop Children %>
						<li><a href="$Link" class="<% if LinkOrCurrent = current %>current<% end_if %>">$MenuTitle.XML</a></li>
					<% end_loop %>
				</ul>
				<% end_if %>
			</li>
		<% end_loop %>
	</ul>
</nav>
