
<script>

  function number_format(number, decimals, decPoint, thousandsSep){
      decimals = decimals || 0;
      number = parseFloat(number);

      if(!decPoint || !thousandsSep){
          decPoint = '.';
          thousandsSep = ',';
      }

      var roundedNumber = Math.round( Math.abs( number ) * ('1e' + decimals) ) + '';
      // add zeros to decimalString if number of decimals indicates it
      roundedNumber = (1 > number && -1 < number && roundedNumber.length <= decimals)
              ? Array(decimals - roundedNumber.length + 1).join("0") + roundedNumber
              : roundedNumber;
      var numbersString = decimals ? roundedNumber.slice(0, decimals * -1) : roundedNumber.slice(0);
      var checknull = parseInt(numbersString) || 0;
  
      // check if the value is less than one to prepend a 0
      numbersString = (checknull == 0) ? "0": numbersString;
      var decimalsString = decimals ? roundedNumber.slice(decimals * -1) : '';
      
      var formattedNumber = "";
      while(numbersString.length > 3){
          formattedNumber = thousandsSep + numbersString.slice(-3) + formattedNumber;
          numbersString = numbersString.slice(0,-3);
      }

      return (number < 0 ? '-' : '') + numbersString + formattedNumber + (decimalsString ? (decPoint + decimalsString) : '');
  }     

  $(document).ready(function(){
    
  });
</script>   
<?php
  //echo(json_encode($messages));
  //echo("<br><br>");
    //     {
    //       "messageNumber": 106291,
    //       "id": "ABGHYoElIkQQTwIQ9-QfJvcHwjfsaNoaz-l26A",
    //       "body": "https://s3.eu-central-1.wasabisys.com/incoming-chat-api/2022/7/29/210962/0bc3912c-add3-4345-96ef-9d5e75a4a048.jpeg",
    //       "fromMe": 0,
    //       "self": 0,
    //       "isForwarded": 0,
    //       "author": "6281252244104@c.us",
    //       "time": 1659063205,
    //       "chatId": "6281252244104@c.us",
    //       "type": "image",
    //       "senderName": "Andreas Prabudi",
    //       "chatName": "6281252244104",
    //       "caption": null,
    //       "quotedMsgId": null,
    //       "meta": null
    //     },
  ?>

  <div class="container">
      <div>
        <table id="tblMonitoring" class="table table-striped table-bordered" cellspacing="0" width="100%">
          <thead>
            <tr>
              <td>MsgId</td>
              <td>IN/OUT</td>
              <td>Author</td>
              <td>Time</td>
              <td>ChatId</td>
              <td>Type</td>
              <td>Sender</td>
              <td>Body</td>
              <td>Caption</td>
            </tr>
          </thead>
          <tbody>
          <?php 
            foreach($messages as $value){
              echo "  <tr>";
              echo "    <td>".$value["messageNumber"]."</td>";
              // echo "    <td>".$value["messageNumber"]."<br>".$value["id"]."</td>";
              echo "    <td>".(($value["fromMe"]==0)? "IN":"OUT")."</td>";
              echo "    <td>".$value["author"]."</td>";
              echo "    <td>".date('d M Y H:i:s',$value['time'])."</td>";
              echo "    <td><a href='".base_url()."WAMonitoring/message/".urlencode($value['chatId'])."'>".$value["chatId"]."</a></td>";
              echo "    <td>".$value["type"]."</td>";
              echo "    <td>".$value["senderName"]."<br>".$value["chatName"]."</td>";
              echo "    <td>".$value["body"]."</td>";
              echo "    <td>".$value["caption"]."</td>";
            }
            // for($i=0; $i<=count($messages);$i++) {
             
            // }
          ?>
          </tbody>
        </table>
      </div>
  </div> <!-- /container -->