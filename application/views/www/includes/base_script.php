<script type="text/javascript">
  var CI_URL = "<?php echo site_url();?>";
  var ASSETS_URL = "<?php echo ASSETS_URL?>";
</script>

<!-- blockUI -->
<script type="text/javascript" src="<?php echo ASSETS_URL ?>/admin/js/jquery.blockUI.js"></script>
<!-- 驗證套建 -->
<script type="text/javascript" src="<?php echo ASSETS_URL ?>/www/js/jquery.validate.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.js"></script>

<script type="text/javascript">
  function openwindow(url) {
    var url;     //網頁位置;
    var name;    //網頁名稱;
    var iWidth = window.screen.availWidth / 2;  //視窗的寬度;
    var iHeight = window.screen.availHeight / 2; //視窗的高度;
    var iTop = (window.screen.availHeight - 30 - iHeight) / 2;  //視窗的垂直位置;
    var iLeft = (window.screen.availWidth - 10 - iWidth) / 2;   //視窗的水平位置;
    window.open(url, 'show', 'height=' + iHeight + ',,innerHeight=' + iHeight + ',width=' + iWidth + ',innerWidth=' + iWidth + ',top=' + iTop + ',left=' + iLeft + ',status=no,location=no,status=no,menubar=no,toolbar=no,resizable=no,scrollbars=no');
  }

</script>

<?php if (isset( $isLogin ) && !$isLogin): ?>
  <!-- 刷新驗證馬用 -->
  <script type="text/javascript">
    function changeChkImg(s_name, obj) {
      s_name = s_name || "";
      obj = obj || "chkImg";
      $.ajax({
        type: "POST",
        url: "<?php echo site_url("Manger/refresh_token")?>",
        cache: false,
        async: false,
        dataType: "json"
      }).done(function (msg) {
        var now = new Date();
        $('#' + obj).attr("src", "<?php echo  site_url("Vcode2");?>?token=" + msg.token + "&now=" + now.getTime() + "&s_name=" + s_name);
      });
    }
  </script>
  <script type="text/javascript" src="<?php echo ASSETS_URL ?>/www/js/login.js"></script>
<?php endif ?>

<?php if (isset( $isLogin ) && $isLogin): ?>
  <!--驗證登入 -->
  <script type="text/javascript">
    $(function () {
      CheckLogin();
      var int = setInterval("CheckLogin()", 60000);  //設定一分鐘
    });
    function CheckLogin() {
      $.ajax({
        type: "POST",
        url: CI_URL + 'Index/check_login.aspx',
        cache: false,
        dataType: "json"
      }).done(function (data) {
        if (data.RntCode == 'N') {
          //alert(data.Msg);
          location.href = CI_URL;
        }
      });
    }
  </script>
<?php endif; ?>

<!--全域設定JS -->
<?php if (isset( $alertMsg )): ?>
  <!--彈跳訊息視窗用-->
  <script type="text/javascript">
    $(function () {
      alertMsg('<?php echo $alertMsg?>');
    });
  </script>
<?php endif; ?>
<?php if (isset( $joinMsg )): ?>
  <!--加入會員成功彈跳 -->
  <script type="text/javascript">
    $(function () {
      $('#ths').modal('show');
    });
  </script>
<?php endif; ?>

<!-- <script>
  (scupioconv = window.scupioconv || []).push({bwp: 1219, bwpid: 'action'});
</script>
<script async src="//img.scupio.com/js/conv.js"></script>
 -->