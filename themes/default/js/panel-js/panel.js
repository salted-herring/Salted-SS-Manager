var ProgressBar = React.createClass({
	render: function() {
		return (
			<div className="progress-bar"><div className="progress-bar__bar"></div><span className="progress-bar__percentage">{this.props.percentage}%</span></div>
		);
	}
});

var Environment = React.createClass({

	componentDidMount: function() {
		this.socket = io('//'+location.hostname+':10086', {reconnection: false});
		var self = this;
		this.socket.on('connect', function () {
			trace('connected');
			this.emit('environment', self.props.data);
			this.on('message', function (msg) {
				self.setState({
			    	message : msg
			    });
			}).on('repo_exist', function(b) {
				self.setState({
					repo_label: b ? 'Re-steup' : 'Setup'
				});
			});
		}).on('transfer_progress', function(data){
			self.setState({
		    	percentage : Math.ceil(data * 100)
		    });
		}).on('disconnect', function(data) {
			console.log('connection lost');
		});
	},

	doBackup: function(e) {
		e.preventDefault();
		trace('doing backup...');
		this.socket.emit('ssh', {
			environment_id: 'enviro-' + this.props.data.id, 
			cmd: 'backup'
		});
	},

	doInit: function(e) {
		e.preventDefault();
		trace('init environment...');
		var self = this;
		this.socket.emit('ssh', {
			environment_id: 'enviro-' + this.props.data.id, 
			cmd: 'setup'
		}, self.state.repo_label == 'Re-steup' ? 'destruct-repo' : null);
	},

	doDeployment: function(e) {
		e.preventDefault();
		trace('deploying...');
	},

	getInitialState : function() {
	    return {
	    	message: '',
	    	percentage : 0,
	    	repo_label: 'Setup'
	    };
	},

	render: function() {

		return (
			<li className="environment-item">
				<h3 className="environment-name">{this.props.children}</h3>
				<div className="environment-details">
					{this.props.data.path}
				</div>
				<div className="actions clearfix">
					<button onClick={this.doInit} href="#">{this.state.repo_label}</button>
					<button onClick={this.doBackup} href="#">Backup</button>
					<button onClick={this.doDeployment} href="#">Deploy</button>
				</div>
				<div className="message-channel">Server message: {this.state.message}</div>
				<ProgressBar percentage={this.state.percentage} />
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
