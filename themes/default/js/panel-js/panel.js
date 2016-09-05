var ProgressBar = React.createClass({
	render: function() {
		return (
			<div className="progress-bar"><span className="progress-bar__percentage">0%</span></div>
		);
	}
});

var Environment = React.createClass({

	componentDidMount: function() {
		var self = this;
		this.socket.on('connect', function () {
			//socket.send('hi');
			this.emit('environment', self.props.data);
			this.on('message', function (msg) {
				console.log(msg);
			});
		}).on('transfer_progress', function(data){
			console.log(data);
		}).on('disconnect', function(data) {
			console.log('connection lost');
		});
	},

	doBackup: function(e) {
		e.preventDefault();
		trace('doing backup...');
	},

	doInit: function(e) {
		e.preventDefault();
		trace('init environment...');
	},

	doDeployment: function(e) {
		e.preventDefault();
		trace('deploying...');
	},

	render: function() {
		this.socket = io('//'+location.hostname+':10086', {reconnection: false});
		
		return (
			<li className="environment-item">
				<h3 className="environment-name">{this.props.children}</h3>
				<div className="environment-details">
					{this.props.data.path}
				</div>
				<div className="actions clearfix">
					<button onClick={this.doInit} href="#">Setup</button>
					<button onClick={this.doBackup} href="#">Backup</button>
					<button onClick={this.doDeployment} href="#">Deploy</button>
				</div>
				<ProgressBar />
			</li>
		);
	}
});

var Site = React.createClass({
	ClickHandler: function(environments) {
		var lcEnvironments = environments.map(function(environment) {
			return (
				<Environment key={environment.id} data={environment}>
					{environment.name}
				</Environment>
			);
		});
		ReactDOM.render(
			<ul className="Environments-list">
				{lcEnvironments}
			</ul>,
			document.getElementById('main')
		);
	},
	render: function() {
		return (
			<li onClick={this.ClickHandler.bind(this, this.props.data.environments)}>
				{this.props.children}
			</li>
		);
	}
});

var SiteList = React.createClass({
	render: function() {
		var listNodes = this.props.data.map(function(node) {
			return (
				<Site className="Site-item" key={node.id} data={node}>
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
	document.getElementById('header')
);
