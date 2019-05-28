<!DOCTYPE HTML>
<html>
   <head>
      <?php $this -> load -> view("www/includes/head.php")?>
      <link href="<?php echo ASSETS_URL ?>/www/css/page.css" rel="stylesheet" type="text/css">
   </head>
   <body>
      <?php $this -> load -> view("www/includes/header.php")?>
      <section class="page_container esport_container">
         <div class="page_content">
            <div class="page_mtitle">Member<b>會員中心</b></div>
            <div class="inner_content">

            <?php $this -> load -> view("www/includes/member_nav.php")?>

            <div class="member_content">
                           <table class="table table-striped history member_table member_record">
                           <thead>
                              <tr>
                                 <th>時     間</th>
                                 <th>類     型</th>
                                 <th>金     額</th>
                              </tr>
                           </thead>
                           <tbody>
                              <?php if (isset( $result )): ?>
                              <?php foreach ($result as $row): ?>
                              <tr>
                                 <td data-th="儲值時間"><?php echo $row["buildtime"]?></td>
                                 <td data-th="儲值方式">
                                    <p class="Stored-value">儲值方式： <?php echo inNumberString($paymentType,$row["payment"])?></p>
                                    <?php if($row["payment"]!='Credit'):?>
                                      <?php if($row['pay_mode']==3):?>
                                        <?php if($row["payment"]=='ATM'):?>
                                          <p class="w1">銀行代碼： 012</p>
                                          <p class="w1">銀行帳號： <?php echo $row["payInfo"]["ACID"]?></p>
                                        <?php else:?>
                                          <p class="w1">繳費代碼：<?php echo $row["payInfo"]["StoreCode"]?></p>
                                        <?php endif;?>
                                      <?php elseif($row['pay_mode']==1): //綠界?>
                                        <?php if($row["payment"]=='ATM'):?>
                                          <p class="w1">銀行代碼： <?php echo $row["payInfo"]["BankCode"]?></p>
                                          <p class="w1">銀行帳號： <?php echo $row["payInfo"]["vAccount"]?></p>
                                        <?php else:?>
                                          <p class="w1">繳費代碼：<?php echo $row["payInfo"]["PaymentNo"]?></p>
                                        <?php endif;?>
                                        <p class="w1">繳費期限：<?php echo $row["payInfo"]["ExpireDate"]?></p>
                                      <?php endif;?>
                                    <?php endif;?>
                                 </td>
                                 <td data-th="儲值金額"><?php echo number_format($row["amount"],0)?></td>
                                 <td data-th="處理結果" class="<?php echo( $row[ "keyin2" ] == 0 ? 'red' : 'text-success' ) ?>">
                                    <p><?php echo $orderKeyin2[$row[ "keyin2" ]] ?></p>
                                 </td>
                              </tr>
                              <?php endforeach; ?>
                              <?php endif; ?>
                           </tbody>
                        </table>
                        <!--page btn start-->
                        <div class="text-center">
                           <?php echo @$pagination ?>
                        </div>
            
                     </div>


            </div>


            <!--inner_content end-->
         </div>
      </section>
      <?php $this -> load -> view("www/includes/footer.php")?>
      <script type="text/javascript" src="<?php echo ASSETS_URL?>/www/js/transfer.js"></script>
   </body>
</html>