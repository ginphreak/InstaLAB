<?php
/**
 * InstaLab 0.1 - powered by iLab Solutions - www.ilabsolutions.it
 * info_AT_ilabsolutions_DOT_it
 * 
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * Requirement:
 * 		-  Instagram Client ID	(http://instagram.com/developer/)
 * 		-  Fancybox >= 2.1.5	(http://fancybox.net/) //included via CDN
 * 		-  jQuery >= 1.10.2		(http://jquery.com/) //included via CDN
 */

/* Configuration! ---------------------------------------------------------------------*/
define('CLIENT_ID',  "26148f04d10c418a832d10dd30e6f5f9");	//Your Client ID
define('TYPESEARCH', "tags");								//Search type: tags / users
define('TARGET',     "italia");								//Target hashtag or user
/*-------------------------------------------------------------------------------------*/


function ig_getUserID($username){
	$username = strtolower($username);
	$url = "https://api.instagram.com/v1/users/search?q=".$username."&client_id=".CLIENT_ID;
	$get = file_get_contents($url);
	$json = json_decode($get);
	foreach($json->data as $user)if($user->username == $username)return $user->id;
	return false;
}
function ig_get($n=""){
	if(TYPESEARCH=='users'){
		$uid = ig_getUserID(TARGET);
		if($uid){
			$handle = fopen("https://api.instagram.com/v1/".TYPESEARCH."/".$uid."/media/recent?client_id=".CLIENT_ID."&max_id=$n", "rb");
		}else{
			echo "User not found!";
			return false;
		}
	}else{
		$handle = fopen("https://api.instagram.com/v1/".TYPESEARCH."/".TARGET."/media/recent?client_id=".CLIENT_ID."&max_id=$n", "rb");
	}
	$contents = '';
	while (!feof($handle))$contents .=fread($handle, 8192);
	fclose($handle);
	$arr = json_decode($contents,true);
	foreach ($arr['data'] as $f) echo "<a href=\"".$f['images']['standard_resolution']['url']."\" data-fancybox-group=\"roadtrip\" title=\"".substr(str_replace('"',"''",$f['caption']['text']),0,200)."\" target=\"_blank\" class=\"image\"><img src=\"".$f['images']['thumbnail']['url']."\"></a>\n";
	if(!empty($arr['pagination']['next_max_id']))return $arr['pagination']['next_max_id'];else return false;
}
if(empty($_POST['next'])){
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>InstaLAB 0.1 - powered by iLab Solutions http://www.ilabsolutions.it</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<script src="//code.jquery.com/jquery-1.10.2.min.js"></script>
		<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.pack.js"></script>
		<link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.css" media="screen" />
		<style>
			body{margin:0;background:#f5f5f5;}
			#tst{max-width:750px;margin:0 auto;border:8px solid #e0e0e0;box-shadow: 0px 0px 15px #888888;}
			#tst img{float:left;}
			#loader{text-align:center;display:none;height:150px;padding-top:2em;}
			#mrt{font-family:"Verdana"; padding:0.5em;margin-bottom:2em;text-align:center;font-size:2em;background:#666;color:#f5f5f5;}
			#cont{background:#666;border:0;font-family:"Verdana", sans-serif;color:#f5f5f5;padding:0.5em;}
		</style>
	</head>
	<body>
	<div id="mrt">InstaLAB</div>
	<script type="text/javascript">
	function goNext(){
		$('#loader').css("display","block");
		$.ajax({
			url: "<?php echo basename(__FILE__); ?>",
			type: "POST",
			data: { next : $('#next').val()},
			dataType: "html"	
		}).done(function(data){
			$('#tst').append(data+'<div style="clear:both;"></div>');
			$('#loader').css("display","none");
		});
	}
	$(document).ready(function(){
		$('.image').fancybox({padding: 0,	openEffect : 'elastic',	openSpeed  : 150, closeEffect : 'elastic', closeSpeed  : 150, closeClick : true, helpers : {overlay : {css : {'background' : 'rgba(255, 255, 255, 1)'}}}});
		//$(window).scroll(function() {if($(window).scrollTop() + $(window).height() == $(document).height()) {goNext();}});
	});
	</script>
	<div id="tst">
	<?php
	if($nid = ig_get()){
		echo '<script type="text/javascript">$(document).ready(function(){$("#next").val("'.$nid.'")});</script>';
	}else{
		echo '<script type="text/javascript">$(document).ready(function(){$("#cont").css("display","none")});</script>';
	}
	?>
	<div style="clear:both;"></div>
	</div>
	<div id="loader">Loading...</div>
	<input type="hidden" id="next" value="">
	<div style="text-align:center;margin-top:2em;"><input type="button" id="cont" value="Continue..." onclick="goNext()"></div>
	</body>
</html>
<?php }else{
	if($nid = ig_get($_POST['next'])){
		echo '<script type="text/javascript">$(document).ready(function(){$("#next").val("'.$nid.'")});</script>';
	}else{
		echo '<script type="text/javascript">$(document).ready(function(){$("#cont").css("display","none")});</script>';
	}
}?>
