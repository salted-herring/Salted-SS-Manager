var Site = React.createClass({
	render: function() {
		return (
			<li>
				{this.props.sql_path}
				{this.props.children}
			</li>
		);
	}
});

var SiteList = React.createClass({
	render: function() {
		var listNodes = this.props.data.map(function(node) {
			return (
				<Site key={node.id} data={node}>
					{node.title}
				</Site>
			);
		});
		return (
			<ul className="Sites-list">
				{listNodes}
			</ul>
		);
	}
});

ReactDOM.render(
	<SiteList data={sites} />,
	document.getElementById('main')
);
