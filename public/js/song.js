$(document).ready(function() {

	$("#playlist").click(function() {

		let playlists = '<div>TODO</div>';

		$(playlists).dialog({
		  title: 'Do some damage',
		  close: function() { 
		  	$(this).remove()
		  },
		  modal: false,
		  width: 500,
		});

	});

});