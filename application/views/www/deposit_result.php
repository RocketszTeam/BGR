<!DOCTYPE HTML>
<html>
	<head>
        <?php $this -> load -> view("www/includes/head.php")?>
			<script type="text/javascript">
				function printScreen(printlist) 
					{
					  var value = printlist.innerHTML;
					  var printPage = window.open("", "Printing...", "");
					  printPage.document.open();
					  printPage.document.write("<HTML><head></head><BODY onload='window.print();window.close()'>");
					  printPage.document.write("<PRE>");
					  printPage.document.write(value);
					  printPage.document.write("</PRE>");
					  printPage.document.close("</BODY></HTML>");
					}
					
					

			</script>
	</head>
	<body>
	<div class="site-wrap">
	<?php $this -> load -> view("www/includes/header.php")?>
    <section class="page_container">
        <div class="page_content">
            <img src="images/left_img1.png" alt="" class="left_img">
            <div class="page_mtitle">Member<b>會員中心</b></div>
            <div class="inner_content">
              <?php $this -> load -> view("www/includes/member_nav.php")?>
                <div class="member_content">
                    <div class="member_txt">儲值繳費代碼</div>
                        <table class="table table-striped history member_table member_record">
                          <tbody>
                              <tr>
                                  <td>
                                      <?php
                                      if($orderInfo["payment"]=='ATM'){
                                          echo '銀行代碼';
                                      }else{
                                          echo '超商繳費';
                                      }
                                      ?>
                                  </td>
                                  <td><?php if ($orderInfo["payment"] == 'ATM'){
                                          echo $payInfo['type'];
                                      }else{
                                          switch($orderInfo["payment"]){
                                              case 'IBON':
                                                  echo '7-11代碼';
                                                  break;
                                              case 'FAMI':
                                                  echo '全家代碼';
                                                  break;
                                              default:
                                                  echo '四大超商皆可繳費';
                                          }
                                      }
                                      ?>
                                  </td>
                              </tr>
                              <tr>
                                  <td>
                                      <?php
                                      if ($orderInfo["payment"] == 'ATM'){
                                          echo '銀行帳號';
                                      }else{
                                          echo '繳費代碼';
                                      }
                                      ?>
                                  </td>
                                  <td><?php echo $payInfo['code']?></td>
                              </tr>
								<tr>
                                 <td>儲值金額</td>
                                 <td>NT<?php echo number_format($orderInfo['amount'],0)?></td>                              
                                </tr>
                          </tbody>
						</table>
                       
                        <div class="text-right"><button type="button" class="btn btn-md btn-info" value="取得代碼" onclick="location.href='<?php echo site_url("Manger/deposit")?>'" style="margin-top:1em;">變更儲值方式</button></div>
                                                 
                        
                                <!----會員資料---->                 

                                        
                	</div>                              
					</div> 
                                          

                    
                </div>
              </section>
			<div class="frame_bg frame_bg2"></div>
			</div>

		<!-- Footer -->
		<?php $this -> load -> view("www/includes/footer.php")?>
		</div>

	</body>
</html>