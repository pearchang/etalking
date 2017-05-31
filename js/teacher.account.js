$(function(){
	
	$('.submit').click(function(){
		
		var pass = $('input[name=passwd]').val();
		var pass2 = $('input[name=passwd2]').val();
		
		
		if( pass != "" || pass2!="" ){
			
			if( pass != pass2 ){
				dialog_teacher("Password are not the same!");
				return false;
			}
			
			if( !pass.match(/.{6,20}/) || !pass.match(/[0-9]/) || !pass.match(/[A-z]/) ){
				dialog_teacher("Password doesn't comply with the rules!");
				return false;
			}
		}		
		
    // file size
    var imgs = $('input[name*="avatar"]');
    for (var k = 0; k < imgs.length; k++)
    {
      var i = imgs.get(k);
      if(i.files && i.files.length == 1)
      {
        if (i.files[0].size > 524288)
        {
          dialog_teacher(i.files[0].name + ": Image file size no more than 500KB.");
          return false;
        }
        if (i.files[0].type != 'image/jpg' && i.files[0].type != 'image/jpeg' && i.files[0].type != 'image/png')
        {
          dialog_teacher(i.files[0].name + ": Image file supports PNG, JPG only.");
          return false;
        }
      }
    }
		
		$('.fill').submit();
	});
	
	$('.upload-close').click(function(){
		var img = parseInt( $(this).data('img') );
		var tag = $(this).data('tag');
		
		if(img==0) return false;
		
		get( 'ajax.teacher.del.avatar.php?tag=' + tag , function(data){
			if(data.code==1){
				$('#avatar_img' + tag ).remove();
				return false;
			}else{
				dialog_teacher( data.msg );
				return false;
			}
		});
		
		
	});
	
	
});