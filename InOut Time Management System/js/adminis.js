$(function() {
	$('#addButton').click(function(e) {
		var type = $(this).attr('atype');
		var url = 'add-' + type;
		var fields = {};
		$('#addTypeModal *').filter(':input').each(function() {
			var fieldname = $(this).attr('name');
			if (fieldname) fields[fieldname] = $(this).val();
		});
		var posting = $.post(url, fields);
		posting.done(function(data) {
			if (data == 'invalid') {
				$('#addType_Error').text('Invalid Input, please check your entry');
				$('#addType_Error').show();
			} else {
				var rowNumber = parseInt($('#typelist tr').last().attr('tid')) + 1;
				var tableRow = '<tr tid="' + rowNumber +'"><td>' + rowNumber + '</td>';
				if (type=='college') tableRow += '<td>' + data.name + '</td><td>'+ data.long_name + '</td><td>0</td>';
				if (type=='department') tableRow += '<td>' + data.name + '</td><td>'+ data.long_name + '</td><td>' + data.college + '</td>'; 
				tableRow += '</tr>';
				$('#addTypeModal').modal('hide');
				$('#typelist').append(tableRow);
			}
		});
	});
	$('.deleteType').each(function() {
		$(this).click(function(e) {
			e.preventDefault();
			var type = $(this).attr('dtype');
			var url = 'delete-' + type;
			var tid = $(this).attr('tid');
			var instance = $(this);
			var posting = $.post(url, { department_id: tid });
			posting.done(function() {
				$(instance).closest('tr').remove();
			});
		});
	});
});