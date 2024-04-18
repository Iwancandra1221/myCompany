
 
	<style type="text/css">
		.container{
			padding-bottom: 10px !important;
			padding-top: 10px !important;
		}
		.chat-container{
			height: 380px;
			overflow-y: scroll;
		}
		.chat-right-parent{
			position:relative;
			max-width: 80%;
			background-color: #838dff; 
			color: black;
			float: right;
			padding: 6px 10px;
			margin: 6px;
			border-radius: 10px;
			text-align: right;
			clear: both;
		}
		.chat-left-parent{
			position:relative;
			max-width: 80%;
			background-color:#f7a94f; 
			color: black; 
			float: left; 
			padding: 6px 10px; 
			margin: 6px;
			border-radius: 10px;
			clear: both;
		}
		.chat-body{
			display:block;
			white-space: pre-line;
	 	}
	 	.chat-left-body .image{
	 		width: 50%;
	 	}
	 	.chat-left-body .sticker{
	 		width: 75px;
	 		margin: 6px;
	 	}
	 	.chat-caption{
	 		display:block;
			text-align: left;
			padding-top: 5px;
			font-size: 13px;
	 	}
		.chat-time{
			display:block;
			font-size: 8px;
			text-align: right;
			padding-top: 5px;
		}
		.chat-name{
			display:block;
			font-size: 12px;
			text-align: left;
			padding-top: 5px;
			padding-bottom: 10px;
		}
		.chat-form{
			padding-top: 8px;
		}
		.chat-form .form-control{
			width: 80%;
			display: inline-block;
		}
		.chat-form .btn{
			float: right;
			margin-top: 10px;
		}
	</style>
	<div class="container">
		<div><a href="<?=base_url()?>WAMonitoring">BACK</a></div>
		<br>
		<div class="chat-container">
			<?php

			
			foreach($messages as $key => $value){
				$overideParentStyle = ($value['type']=='sticker' ? 'style="clear:none";':'');
				if($value['fromMe']==1){
					echo '<div class="chat-right-parent" '.$overideParentStyle.' >';
					echo '	<span class="chat-name">'.$value['senderName'].'</span>';
					echo '  <span class="chat-body">';
					if($value['type']=='image' || $value['type']=='sticker'){
						echo '<img src="'.$value['body'].'">';
						echo '<span class="chat-caption">'.$value['caption'].'</span>';
					}
					else{
						echo '   '.str_replace('\n',"<br>",$value['body']);
					}
					
					echo '  </span>';
					echo '  <span class="chat-time">';
					echo '    '.date('d M Y H:i:s',$value['time']);
					echo '  </span>';
					echo '</div>';
				}
				else{
					echo '<div class="chat-left-parent" '.$overideParentStyle.' >';
					echo '	<span class="chat-name">'.$value['senderName'].'</span>';
					echo '  <span class="chat-left-body">';
					if($value['type']=='image' || $value['type']=='sticker'){
						echo '<img class="'.$value['type'].'" src="'.$value['body'].'">';
						echo '<span class="chat-caption">'.$value['caption'].'</span>';
					}
					else{
						echo '   '.str_replace('\n',"<br>",$value['body']);
					}
					echo '  </span>';
					echo '  <span class="chat-time">';
					echo '    '.date('d M Y H:i:s',$value['time']);
					echo '  </span>';
					echo '</div>';
				}
				
			}
			?>
		</div>
		<div class="chat-form">
			<form id="f-chat" action="<?=$formAction?>">
				<textarea class="form-control" type="text" name="body"></textarea>
				<input class="btn" type="button" value="send">
			</form>
		</div>
	</div> <!-- /container -->
	 <script>
		$(document).ready(function(){
			$("#f-chat .btn").click(function(){
				var actionUrl = $("#f-chat")[0].action;
				var formData = new FormData($("#f-chat")[0]);
				$.ajax({
						type: "POST",
						url: actionUrl,
						data: formData, // serializes the form's elements.
						processData: false,
						contentType: false,
						success: function(data)
						{
							alert(data.msg); // show response from the php script.
							if(data.code==1){
								window.location.href="<?=base_url()?>WAMonitoring/message/<?=$chatId?>";
							}
						}
				});
			});
		});
	</script>