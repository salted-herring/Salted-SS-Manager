<nav class="grid_12" id="sub_navigation">
	<div class="grid_12" id="sub_nav_inner">
		<ul>
			<% loop Menu(2) %>
				<li>
					<a href="$Link" class="<% if LinkOrCurrent = current %>current<% end_if %>">$MenuTitle.XML</a>
				</li>
			<% end_loop %>
		</ul>
		
	</div>
</nav>