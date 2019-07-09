
$(document).on('change', '#id_province', function(){
	var pid = $(this).val();
	$.ajax({
		url: '/jobs/jobapi.php?method=arealist&id='+pid,
		type: 'get',
		dataType : 'json',
		success: function(data){
			options = '';
			for (let v in data) {
				options += '<option value="'+data[v].id+'">'+data[v].cname+'</option>';
			}
			$('#id_city').html(options);
		}
	});
});
;