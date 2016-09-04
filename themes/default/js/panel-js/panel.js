// // tutorial4.js
// var Comment = React.createClass({
//   render: function() {
//     return (
//       <div className="comment">
//         <h2 className="commentAuthor">
//           {this.props.author}
//         </h2>
//         {this.props.children}
//       </div>
//     );
//   }
// });


// // tutorial10.js
// var CommentList = React.createClass({
//   render: function() {
//     var commentNodes = this.props.data.map(function(comment) {
//       return (
//         <Comment author={comment.author} key={comment.id}>
//           {comment.text}
//         </Comment>
//       );
//     });
//     return (
//       <div className="commentList">
//         {commentNodes}
//       </div>
//     );
//   }
// });


var Environment = React.createClass({
	render: function() {
		return (
			<li>
				{this.props.sql_path}
				{this.props.children}
			</li>
		);
	}
});

var EnvironmentList = React.createClass({
	render: function() {
		var listNodes = this.props.data.map(function(node) {
			return (
				<Environment key={node.id} data={node}>
					{node.name}
				</Environment>
			);
		});
		return (
			<ul className="environments-list">
				{listNodes}
			</ul>
		);
	}
});

ReactDOM.render(
	<EnvironmentList data={environment_data} />,
	document.getElementById('main')
);
