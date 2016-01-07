$(function() {

	$("#phone").mask("(999) 999-9999");
	
	$('.disabled').click(function(e) {
		e.preventDefault();
	});

	$('.selectpicker').selectpicker();

	$('.changeStatus').each(function() {
		$(this).click(function(e) {
			e.preventDefault();
			var uid = $(this).attr('uid');
			var posting = $.post('update-status/' + uid);
			var instance = $(this);
			posting.done(function(data) {
				instance.find('img').fadeOut('slow', function() {
					$(this).remove();
					instance.append(data);
				});
				
			});
		});
	});

	$('.changeRole').each(function() {
		$(this).change(function() {
			var newRoleId = $(this).val();
			var uid = $(this).attr('uid');
			var posting = $.post('update-role/' + uid, { roleId: newRoleId });
		});
	});

	$('.activateUser').each(function() {
		$(this).click(function(e) {
			e.preventDefault();
			var uid = $(this).attr('uid');
			var posting = $.post('activate-user/' + uid);
			var instance = $(this);
			posting.done(function() {
				instance.parents('.userRow').fadeOut('slow', function() {
					$(this).remove();
				});
			});
		});
	});

	$('.approveClient').each(function() {
		$(this).click(function(e) {
			e.preventDefault();
			var cid = $(this).attr('cid');
			var posting = $.post('approve-client', { cid: cid });
			posting.done(function() {
				location.reload();
			});
		});
	});

	$('#myProfile input').each(function() {

	});

	$('#types').children('.btn').each(function() {
		$(this).click(function(e) {
			$('#type').val($(this).val());
		});
	});

	$('.addUser').click(function(e) {
		e.preventDefault();
		var type = $(this).attr('type');
		var url = 'add-' + type + '-modal';
		$.get(url, function(data) {
			$(data).modal('show');
		});
	});

	$('.deleteUser').click(function(e) {
		e.preventDefault();
		var type = $(this).attr('type');
		var url = 'delete-' + type + '-modal';
		var ids = [];
		var uids = [];
		$('input[type=checkbox]:checked').each(function() {
			ids.push($(this).val());
			uids.push($(this).attr('uid'));
		});
		if (ids.length > 0) {
			var posting = $.get(url, { ids: ids, uids: uids });
			posting.done(function(data) {
				$(data).modal('show');
			});
		}
	});

	$('body').on('shown', '#addUserModal', function() {
		var instance = $(this);
		$('.selectpicker').selectpicker();
		$($(this)).on('click', '.addUserButton', function(e) {
			e.preventDefault();
			var type = $(this).attr('type');
			var url = 'add-' + type;
			var email = $('#email').val();
			var first_name = $('#first_name').val();
			var last_name = $('#last_name').val();
			if (type == 'admin') {
				var posting = $.post(url, { email: email, first_name: first_name, last_name: last_name });
			} else if (type == 'trainer') {
				var semester = $('#semester').val();
				var section = $('#section').val();
				var posting = $.post(url, { email: email, first_name: first_name, last_name: last_name, semester: semester, section: section });
			}
			posting.done(function(data) {
				if (data == 'invalid') {
					$('#errors').text('Invalid input, please check your entry.');
				} else {
					location.reload();
				}
			});
		});
	});

	$('body').on('shown', '#deleteUserModal', function() {
		var instance = $(this);
		$($(this)).on('click', '.deleteUserButton', function(e) {
			e.preventDefault();
			var type = $(this).attr('type');
			if (type == 'trainer' || type == 'admin') {
				var ids = $('#delIds').val();
				console.log(ids);
				var url = 'delete-' + type;
				var posting = $.post(url, { ids: ids });
				posting.done(function(data) {
					location.reload();
				});
			}
		});
	});

	$('body').on('hidden', '.forceRemove', function() {
		$(this).remove();
	});

	$('#clientList').on('click', '#changeTrainer', function(e) {
		e.preventDefault();
		var parent = $(this).parent();
		var name = $(this).text().trim();
		$(this).remove();
		if (name == 'Add') {
			parent.append('<input class="span3" id="trainerName" type="text" value="">');
		} else {
			parent.append('<input class="span3" id="trainerName" type="text" value="' + name +' ">');
		}
		$('#trainerName').typeahead({
			source: function (query, process) {
				$.getJSON('trainers-json', function(data) {
					emails = [];
					$.each(data, function (i, email) {
						emails.push(email.email);
					});

					return process(emails);
				});
			},
		}).focus();
	});

	$('#clientList').on('keypress', '#trainerName', function(e) {
		if (e.which == 13) {
			e.preventDefault();
			var parent = $(this).parent();
			var name= $(this).val();
			$(this).remove();
			if (name == '') {
				parent.append('<a id="changeTrainer" href="#">Add</a>');
				var posting = $.post('update-trainer', { id: parent.attr('clientid'), trainer: name });
			} else {
				parent.append('<a id="changeTrainer" href="#">'+name+'</a>');
				var posting = $.post('update-trainer', { id: parent.attr('clientid'), trainer: name });
			}
		}
	});

	$('.viewPreQuestionnaire').each(function() {
		$(this).click(function(e) {
			e.preventDefault();
			var cid = $(this).attr('cid');
			var url = 'view-pre-questionnaire/' + cid;
			$.get(url, function(data) {
				$(data).modal('show');
			});
		});
	});

	$('#editFields').on('click', '.editField', function(e) {
			e.preventDefault();
			var type = $(this).attr('type');
			var parent = $(this).parent();
			var sid = parent.parent().attr('sid');
			var value = $(this).text();
			var tabletype = $('#editFields').attr('tabletype');
			var myInput = $(document.createElement("input"));
			myInput.attr('type', 'text').attr('id', type).attr('oldval', value)
				   .attr('value', value).attr('sid', sid).attr('tabletype', tabletype);
			$(this).remove();
			parent.append(myInput);
			if (type == 'start_date' || type == 'end_date') {
				myInput.datepicker({ format: 'yyyy-mm-dd' }).focus();
			} else {
				myInput.focus();
			}
	});

	$('#editFields').on('keyup', 'input', function(e) {
		if (e.keyCode == 13) {
			var value = $(this).val();
			var parent = $(this).parent();
			var sid = $(this).attr('sid');
			var type = $(this).attr('id');
			var oldval = $(this).attr('oldval');
			var tabletype = $(this).attr('tabletype');

			$(this).datepicker('hide').remove();
			
			if (value == '' || value == oldval) {
				parent.append('<a class="editField" type="'+type+'" href="#">'+oldval+'</a>');
			} else {
				parent.append('<a class="editField" type="'+type+'" href="#">'+value+'</a>');
				$.post('update-' + tabletype, { sid: sid, type: type, value: value });
			}
		} else if (e.keyCode == 27) {
			var parent = $(this).parent();
			var oldval = $(this).attr('oldval');
			$(this).datepicker('hide').remove();
			parent.append('<a class="editField" type="'+type+'" href="#">'+oldval+'</a>');
		}
	});

	$('.updateTrainerDd').each(function() {
		$(this).change(function() {
			var sid = $(this).val();
			var tid = $(this).attr('tid');
			var type = $(this).attr('tabletype');
			$.post('update-trainer-'+type, { sid: sid, tid: tid })
		});
	});

});