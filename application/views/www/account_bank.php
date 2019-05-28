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
			   
            <div class="member_content">
                  <div class="tab_container">
                     <div id="tab1" class="tab_content">
                        <table class="table table-striped history member_table member_record">
                           <thead>
                              <tr>
                                 <th>訂單編號</th>
                                 <th>加值方式</th>
                                 <th>金額</th>
                                 <th>日期</th>
                                 <th>狀態</th>
                              </tr>
                           </thead>
                           <tbody>
                              <?php if (isset( $result )): ?>
                              <?php foreach ($result as $row): ?>
                              <tr>
                                 <td data-th="訂單編號"><?php echo $row[ "order_no" ] ?></td>
                                 <td data-th="加值方式">
                                    <p class="w1">銀行代碼： <?php echo $row[ "bank_name" ] ?></p>
                                    <p class="w1">銀行帳號： <?php echo $row[ "bank_account" ] ?></p>
                                    <p class="w1">銀行戶名： <?php echo $row[ "account_name" ] ?></p>
                                 </td>
                                 <td data-th="金額"><?php echo number_format($row[ "amount" ], 0) ?></td>
                                 <td data-th="日期"><?php echo $row[ "buildtime" ] ?></td>
                                 <td data-th="狀態" class="<?php echo( $row[ "keyin2" ] == 0 ? 'red' : 'text-success' ) ?>">
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
                        <!--page btn end-->
                     </div>
                  </div>
                  <!-- .tab_container -->
               </div>
            </div>


            </div>
            <!--inner_content end-->
         </div>
      </section>
      <?php $this -> load -> view("www/includes/footer.php")?>
   </body>
</html>