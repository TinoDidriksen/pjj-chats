<?php
	$_REQUEST['chatid'] = intval($_REQUEST['chatid']);
	if (empty($_REQUEST['chatid'])) {
		die();
	}

	ob_start();
?><!DOCTYPE html>
<html>
<head>
	<script type="text/javascript" src="https://pjj.cc/common/xmlsocket/XMLSocket.js"></script>
	<script type="text/javascript">
	function gx(i) {
		return document.getElementById(i);
	}

	var host = 'pjj.cc';
	var port = 4120;
	var chat = <?=$_REQUEST['chatid'];?>;
	var socket = new XMLSocket();

	function connect() {
		socket.connect(host, port);
	}

	// Connection attempt finished
	socket.onConnect = function(success) {
		if (success) {
			gx('log').value += "\n" + 'Connected';
			if (socket.connectid) {
				clearInterval(socket.connectid);
			}
			socket.send("REGISTER "+chat+"\n");
			socket.keepaliveid = setInterval(socket.keepalive, 300000);
		} else {
			//gx('log').value += "\n" + 'Connection failed for unknown reason';
		}
	}

	socket.onData = function(text) {
		//gx('log').value += "\n" + 'Received: '+text;
		var sub;
		if (sub = text.match(/^REFRESH (\d+)$/)) {
			if (sub[1] == chat && window.parent.frames['TextWindow'] && window.parent.frames['TextWindow'].RefreshPageDelayed) {
				window.parent.frames['TextWindow'].RefreshPageDelayed();
			}
		}
	}

	socket.onClose = function() {
		gx('log').value += "\n" + 'Connection dropped';
		//socket.connectid = setInterval(connect, 2000);
	}

	socket.keepalive = function() {
		try {
			socket.send("KEEPALIVE "+Date()+"\n");
		} catch(e) {
			clearInterval(socket.keepaliveid);
		}
	}

	window.onload = function() {
		socket.init('socket');
		setTimeout(connect, 1500);
	}
	</script>
</head>
<body>
	<div id="socket"></div>
	<br/>
	<textarea id="log" name="log" cols="70" rows="16"></textarea>
</body>
</html>
