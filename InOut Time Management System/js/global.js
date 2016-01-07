$(function() {
	var host = location.protocol + '//' + location.host;

	$('#getReport').click(function(e) {
		e.preventDefault();
		var yw = $('#report').val();
		if (yw == 'current') {
			window.location.href = $(this).attr('url');
		} else {
			var yw = yw.split('-');
			var year = yw[0];
			var week = yw[1];
			window.location.href = $(this).attr('url') + '/' + year + '/' + week;
		}
	});

	$('#addTime_Date').datepicker({ dateFormat: "yy-mm-dd" });
	$('#addTime_InTime').timepicker({timeFormat: 'HH:mm:ss'});
	$('#addTime_OutTime').timepicker({timeFormat: 'HH:mm:ss'});

	$('.toggle').toggles({
		on: $('.toggle').hasClass('on'),
		text: {
			on: 'IN',
			off: 'OUT',
		},
		width: 140,
		height: 50
	});

	$('.editLogTime').each(function() {
		$(this).click(function(e) {
			e.preventDefault();
			var inTimeInstance = $(this).parent().parent().children('.inTime');
			var outTimeInstance = $(this).parent().parent().children('.outTime');
			var logId = $(this).attr('logId');
			if ($(this).attr('isOnConfirm') == 0) {
				var inTime = inTimeInstance.text();
				var outTime = outTimeInstance.text()
				inTimeInstance.html("<input class='text-center' type='text' value='" + inTime + "'>");
				outTimeInstance.html("<input class='text-center' type='text' value='" + outTime + "'>");
				inTimeInstance.children().timepicker({timeFormat: 'HH:mm:ss'});
				outTimeInstance.children().timepicker({timeFormat: 'HH:mm:ss'});
				$(this).html("<span class='glyphicon glyphicon-ok'>");
				$(this).attr('isOnConfirm', 1);
				//outTimeInstance.text('<input type="text"');
			} else {
				var inTime = inTimeInstance.children().val();
				var outTime = outTimeInstance.children().val();
				inTimeInstance.html(inTime);
				outTimeInstance.html(outTime);
				$(this).html("<span class='glyphicon glyphicon-edit'>");
				$(this).attr('isOnConfirm', 0);
				$.post('logs/update', { log_id: logId, in_time: inTime, out_time: outTime});
			}
		});
	});

	$('.roleChange').each(function() {
		$(this).change(function() {
			var dept_id = $(this).attr('did');
			var user_id = $(this).attr('uid');
			var newRoleId = $(this).val();
			$.post('change-user-role', { dept_id: dept_id, user_id: user_id, newRoleId: newRoleId });			
		});
	});

	$('#add_time').click(function() {
		var date = $('#addTime_Date').val();
		var in_time = $('#addTime_InTime').val();
		var out_time = $('#addTime_OutTime').val();
		var posting = $.post('dashboard/add-time', { date: date, in_time: in_time, out_time: out_time });
		posting.done(function(data) {
			if (data == 'success') {
				$('#addTime_Date').val('')
				$('#addTime_InTime').val('')
				$('#addTime_OutTime').val('')
				alert('Time had been added');
			} else {
				alert('Invalid date/time');
			}
		});
	});

	$('.toggle').on('toggle', function () {
		var posting = $.post('dashboard/change-status');
		posting.done(function(data) {
			$('#clicktime').text(data);
		});
		//console.log($(this).hasClass('on'));
	});

	//$('.changeStatus').each(function() {
	$('#clockBox').on('click', '.changeStatus', function(e) {
		//$(this).click(function(e) {
			e.preventDefault();
			var uid = $(this).attr('uid');
			var type = $(this).attr('type');
			var posting = $.post('public-clock/change-status/' + uid + '/' + type);
			var instance = $(this);
			posting.done(function(data) {
				instance.find('img').fadeOut('slow', function() {
					$(this).remove();
					instance.append(data.img);
					if (data.intime == '') {
						instance.parent().siblings('.outtime').text(data.outtime);
					} else {
						instance.parent().siblings('.intime').text(data.intime);
						instance.parent().siblings('.outtime').text('');
					}
				});
			});
		//});
	});

	$('#contentUsers').load('dashboard/user-data');
	$('#contentLogs').load('dashboard/log-data');

	$('#pcUsers').load('pc-users');

	setInterval(function() {
		$('#contentUsers').load('dashboard/user-data');
		$('#contentLogs').load('dashboard/log-data');

		$('#pcUsers').load('pc-users');
	},60000);

	$('#addUserBtn').click(function() {
		url = 'add-user';
		var email = $('#addUser_Email').val();
		var first_name = $('#addUser_FirstName').val();
		var last_name = $('#addUser_LastName').val();
		var role_id = $('#addUser_Role').val();
		var posting = $.post(url, { email: email, first_name: first_name, last_name: last_name, role_id: role_id });

		posting.done(function(data) {
			if (data == 'invalid') {
				$('#addUser_Error').text('Invalid Input, please check your entry');
				$('#addUser_Error').show();
			} else {
				var rowNumber = parseInt($('#userlist tr').last().attr('rid')) + 1;
				var tableRow = '<tr rid="' + rowNumber +'"> \
								<td>' + rowNumber + '</td> \
								<td>' + email + '</td> \
								<td>'+ first_name + ' ' + last_name + '</td> \
								<td>' + data.role + '</td> \
								<td>Active</td> \
								<td><a href="#" class="deleteUser" uid="' + data.user_id + '"><span class="glyphicon glyphicon-remove-circle"></span></a></td> \
								</tr>';
				$('#addUserModal').modal('hide');
				$('#userlist').append(tableRow);
			}
		});
	});

/*	$('#addUserModal').on('hidden.bs.modal', function() {
		$(this).removeData('bs.modal');
	});
*/

	$('.modal').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
    });

	$('.deleteUser').each(function() {
		$(this).click(function(e) {
			e.preventDefault();
			var url = 'delete-user';
			var uid = $(this).attr('uid');
			var posting = $.post(url, { user_id: uid });
			var instance = $(this);
			posting.done(function(data) {
				if (data == 1)
					$(instance).closest('tr').remove();
				else
					console.log('Don\'t do that');
			});
		});
	});

/*	$('.publicStatus').each(function() {
		$(this).click(function(e) {
			e.preventDefault();
			var url = 'public-clock/change-status/';
			var uid = $(this).attr('uid');
			var posting = $.post(url + uid);
			posting.done(function() {
				instance.find('img').fadeOut('slow', function() {
					$(this).remove();
					instance.append(data);
				});
			});
		});
	});*/
	
	$('.alert').alert();
	$('.changeSuccess').hide();
	$('.changeFail').hide();
	$('.addFail').hide();

	$('.changeInfo :input').on('change keyup paste', function() {
		$('.saveChanges').prop('disabled', false);
	});
	
	$('.saveChanges').click(function() {
		$('.changeSuccess').hide();
		$('.changeFail').hide();
		var formData = $('.changeInfo').serialize();
		var uid = $(this).attr('uid');
		var posting = $.ajax({
								type: 'PATCH',
								url: uid,
								data: formData
							});
		posting.done(function(data) {
			if (data == 1) {
				$('.changeSuccess').show();
				window.setTimeout(function() { $('.changeSuccess').alert('close'); }, 3000);
			} else {
				$('.changeFail').show();
			}
		});
		$(this).prop('disabled', true);
	});

	$('.addResource').click(function() {
		var formData = $('.addResourceForm').serialize();
		var rtype = $(this).attr('rtype');
		var rtypeSource = $(this).attr('rtypeSource');
		if (rtypeSource == 'self') {
			var posting = $.post(rtype, formData);
		} else {
			var posting = $.post('../' + rtype, formData);
		}
		posting.done(function(data) {
			$('.addFail').hide();
			if (data.code == 1) {
				if (rtypeSource == 'self') {
					window.location.replace(rtype+'/'+data.id);
				} else {
					if (rtype == 'department') {
						var div = $('#departmentBlock div:last').clone();
						div.find('a').html(data.text);
						div.css('display', 'block');
						$('#departmentBlock').append(div);
					} else if (rtype == 'user') {
						location.reload();
					}
				}
				$('.modal').modal('hide');
			} else {
				$('.addFail').show();
			}
		});
	});

	$('.ttlinks').tooltip();
	$('.ttlinks').click(function(e) {
		e.preventDefault();
	});

	$('.removeUserFromDepartment').click(function(e) {
		e.preventDefault();
		var uid = $(this).attr('uid');
		var did = $(this).attr('did');
		var posting = $.post('remove-user', { user_id: uid, department_id: did });
		posting.done(function(data) {
			if (data.code == 1) {
				location.reload()
			} else {
				
			}
		});
	});

	$('.toggleBoolColumn').each(function() {
		var scope = $(this);
		$(this).click(function(e) {
			e.preventDefault();
			var uid = $(this).closest('tr').attr('uid');
			var ctype = $(this).attr('ctype');
			var posting = $.post('user/toggle-column', { user_id: uid, column: ctype });
			posting.done(function(data) {
				if (data.code == 1) {
					var span = scope.children('span').eq(0);
					if (span.hasClass('glyphicon-ok')) {
						span.removeClass('glyphicon-ok');
						span.addClass('glyphicon-remove');
					} else {
						span.removeClass('glyphicon-remove');
						span.addClass('glyphicon-ok');
					}
				}
			});
		});
	});

	$('#inputCollegeX').on('change', function() {
		var college_id = $(this).val();
		var posting = $.post(host+'/manage/college/get-departments', { college_id: college_id });
		posting.done(function(data) {
			if (data.code == 1) {
				var select = $('#inputDepartmentX');
				select.empty();
				$.each(data.departments, function(key, department) {
					select.append('<option value='+department.id+'>'+department.long_name+'</option>');
				});
			}
		});
	});

	$('.searchBox').keypress(function (e) {
		if (e.which == 13) {
			var qs = {};
			qs['search'] = $(this).val();
			var search = jQuery.param(qs);
			var url = [location.protocol, '//', location.host, location.pathname].join('');
			window.location.href = url + '?' + search;
		}
	});

	$('.deptsInUser').each(function() {

		$(this).on('closed.bs.alert', function() {
			var user_id = $('#saveDepartmentChanges').attr('uid');
			var department_id = $(this).attr('did');
			$.post('../department/remove-user', { user_id: user_id, department_id: department_id });
		});
	});

});

function popitup(url) {
	newwindow = window.open(url, 'name', 'height=600,width=800,scrollbars=yes');
	if (window.focus) { newwindow.focus() }
	return false;
}