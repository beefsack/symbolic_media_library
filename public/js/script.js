function serverFileDialog(target, path)
{
	if (target == undefined || $(target).size() == 0) {
		throw "Unable to find target";
	}
	$('#serverFileDialogTarget').val(target);
	$('#serverFileDialog').dialog('open');
	serverFileDialogChange(path);
}

function serverFileDialogChange(path)
{
	var params = {};
	if (path != undefined) {
		params.path = path;
	}
	$.getJSON(PUBLIC_PATH+'/ajax/fetchdirectory', params, function(data) {
		$('#serverFileDialog #path').html(data.path);
		$('#serverFileDialog #files').children().remove();
		$.each(data.files, function(index, value) {
			if (value.directory) {
				$('#serverFileDialog #files').append('<div class="file"><span class="link" onclick="serverFileDialogChange(\''+escapeQuotes(data.path)+'/'+escapeQuotes(value.file)+'\')">'+value.file+'</span></div>');
			} else {
				$('#serverFileDialog #files').append('<div class="file">'+value.file+'</div>');
			}
		});
	});	
}

function submitServerFileDialog()
{
	$($('#serverFileDialogTarget').val()).val($('#serverFileDialog #path').html());
	$('#serverFileDialog').dialog('close');
}

function generateLibrary()
{
	var params = {};
	params.type = 'Model_LibraryType_Video';
	params.source = $('#source').val();
	params.destination = $('#destination').val();
	$('#generateStatusDialog').dialog('open');
	var reload = setInterval(function() {
		loadLog(params.destination);
	}, 2000);
	$.getJSON(PUBLIC_PATH+'/ajax/generatelibrary', params, function(data) {
		clearInterval(reload);
		loadLog(params.destination);
		if (data.result) {
			alert('success!');
		} else {
			var error = 'Unspecified error while generating library';
			if (data.error) {
				error = data.error;
			}
			$('#generateStatusDialog').dialog('close');
			alert(error);
		}
	});
}

function loadLog(path)
{
	$.getJSON(PUBLIC_PATH+'/ajax/loadlog', {path: path}, function(data) {
		if (data.result) {
			var element = $('#generateStatusDialogConsole');
			element.html(data.data);
			element.scrollTop(element[0].scrollHeight);
		}
	});
}

function escapeQuotes(str)
{
	return str.replace('\'', '\\&apos;').replace('"', '\\&quot;');
}

$(document).ready(function() {
	$('#serverFileDialog').dialog({
		autoOpen: false,
		buttons: {
			Cancel: function() {
				$('#serverFileDialog').dialog('close');
			},
			"Create Directory": function() {
				$('#createDirectoryDialog').dialog('open');
			},
			Select: function() {
				submitServerFileDialog();
			}
		},
		width: 600,
		height: 400,
		modal: true,
		resizable: false,
		title: "Select a directory",
		show: "drop",
		hide: "drop"
	});
	$('#createDirectoryDialog').dialog({
		autoOpen: false,
		buttons: {
			Cancel: function() {
				$('#createDirectoryDialog').dialog('close');
			},
			Create: function() {
				$.getJSON(PUBLIC_PATH+'/ajax/createdirectory', {
					path: $('#serverFileDialog #path').html(),
					name: $('#createDirectoryName').val()
				}, function(data) {
					if (data.result) {
						$('#createDirectoryDialog').dialog('close');
						serverFileDialogChange($('#serverFileDialog #path').html());
					} else {
						var error = "Unspecified error creating directory";
						if (data.error) {
							error = data.error;
						}
						$('#createDirectoryDialogError').html(error);
						$('#createDirectoryDialogError').fadeIn();
					}
				});
			}
		},
		modal: true,
		resizable: false,
		title: "Create directory",
		show: "drop",
		hide: "drop",
		open: function(event, ui) {
			$('#createDirectoryName').val('');
			$('#createDirectoryDialogError').hide();
		}
	});
	$('#generateStatusDialog').dialog({
		autoOpen: false,
//		buttons: {
//			Cancel: function() {
//				$('#serverFileDialog').dialog('close');
//			},
//			"Create Directory": function() {
//				$('#createDirectoryDialog').dialog('open');
//			},
//			Select: function() {
//				submitServerFileDialog();
//			}
//		},
		closeOnEscape: false,
		modal: true,
		resizable: false,
		title: "Generate library",
		width: 640,
		show: "drop",
		hide: "drop"
	});
});
