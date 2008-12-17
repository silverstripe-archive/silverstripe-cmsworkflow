Behaviour.register({
	'.TableListField .externallink' : {
		onclick: function(e) {
			window.open(e.target.href);
			Event.stop(e);
			return false;
		}
	}
});