function serverFileDialog(target)
{
	if (target == undefined || $(target).size() == 0) {
		throw "Unable to find target";
	}
	$('#submitServerFileDialog').attr('target', target);
	$('#serverFileDialog').dialog('open');
	serverFileDialogChange();
}

function serverFileDialogChange(path)
{
	var params = {};
	if (path != undefined) {
		params.path = path;
	}
	$('#serverFileDialog #files').children().remove();
	$.getJSON(PUBLIC_PATH+'/ajax/fetchdirectory', params, function(data) {
		$('#serverFileDialog #path').html(data.path);
		$.each(data.files, function(index, value) {
			if (value.directory) {
				$('#serverFileDialog #files').append('<div class="file"><span class="link" onclick="serverFileDialogChange(\''+data.path+'/'+value.file+'\')">'+value.file+'</span></div>');
			} else {
				$('#serverFileDialog #files').append('<div class="file">'+value.file+'</div>');
			}
		});
	});	
}

function submitServerFileDialog(target)
{
	$(target).val($('#serverFileDialog #path').html());
	$('#serverFileDialog').dialog('close');
}

$(document).ready(function() {
	$('#serverFileDialog').dialog({
		autoOpen: false,
		modal: true,
		title: "Select a directory",
		show: "slide",
		hide: "slide"
	});
});
