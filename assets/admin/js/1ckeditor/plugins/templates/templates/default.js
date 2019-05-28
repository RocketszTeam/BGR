/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.addTemplates('default', {
    imagesPath: CKEDITOR.getUrl(CKEDITOR.plugins.getPath('templates') + 'templates/images/'), templates: [
    {
        title: '表格樣版一',
        image: 'template1.gif',
        description: '條列式表格樣式，欄位間隔以不同色系區格。',
        html: '<table cellspacing="1" cellpadding="5" width="90%" align="center" summary="" border="0"><tbody><tr bgcolor="#eeeeee"><td valign="top" width="4%"><div align="center"><font class="b">1</font></div></td><td width="96%" bgcolor="#eeeeee"><font class="b">第一行文字</font></td></tr><tr bgcolor="#dddddd"><td valign="top" width="4%"><div align="center"><font class="b">2</font></div></td><td width="96%" bgcolor="#dddddd"><font class="b">第二行文字</font></td></tr><tr bgcolor="#eeeeee"><td valign="top" width="4%"><div align="center"><font class="b">3</font></div></td><td width="96%" bgcolor="#eeeeee"><font class="b">第三行文字</font></td></tr><tr bgcolor="#dddddd"><td valign="top" width="4%"><div align="center"><font class="b">4</font></div></td><td width="96%" bgcolor="#dddddd"><font class="b">第四行文字</font></td></tr><tr bgcolor="#eeeeee"><td valign="top" width="4%"><div align="center"><font class="b">5</font></div></td><td width="96%" bgcolor="#eeeeee"><font class="b">第五行文字</font></td></tr><tr bgcolor="#dddddd"><td valign="top" width="4%"><div align="center"><font class="b">6</font></div></td><td width="96%" bgcolor="#dddddd"><font class="b">第六行文字</font></td></tr><tr bgcolor="#eeeeee"><td valign="top" width="4%"><div align="center"><font class="b">7</font></div></td><td width="96%" bgcolor="#eeeeee"><font class="b">第七行文字</font></td></tr><tr bgcolor="#dddddd"><td valign="top" width="4%"><div align="center"><font class="b">8</font></div></td><td width="96%" bgcolor="#dddddd"><font class="b">第八行文字</font></td></tr></tbody></table>'
    },
    {
        title: '表格樣版二',
        image: 'template2.gif',
        description: '條列式表格樣式，欄位間隔以不同色系區格。表格樣式立體',
        html: '<table width="95%" border="1" align="center" cellpadding="2" cellspacing="0" bordercolorlight="#CCCCCC" bordercolordark="#FFFFFF" ><tr bgcolor="#eeeeee"><td width="20" align="center">1</td><td bgcolor="#eeeeee">&nbsp;</td></tr><tr bgcolor="#dddddd"><td align="center">2</td><td bgcolor="#dddddd">&nbsp;</td></tr><tr bgcolor="#eeeeee"><td align="center">3</td><td bgcolor="#eeeeee">&nbsp;</td></tr><tr bgcolor="#dddddd"><td align="center">4</td><td bgcolor="#dddddd">&nbsp;</td></tr><tr bgcolor="#eeeeee"><td align="center">5</td><td bgcolor="#eeeeee">&nbsp;</td></tr><tr bgcolor="#dddddd"><td align="center">6</td><td bgcolor="#dddddd">&nbsp;</td></tr><tr bgcolor="#eeeeee"><td align="center">7</td><td bgcolor="#eeeeee">&nbsp;</td></tr><tr bgcolor="#dddddd"><td align="center">8</td><td bgcolor="#dddddd">&nbsp;</td></tr></table>'
    },
    {
        title: '表格樣版三',
        image: 'template3.gif',
        description: '使用表格編排圖片及文字。圖片置左，文字有標題及內容【表格為透明，瀏覽時不會出現框線】',
        html: '<table width="95%" border="0" cellspacing="0" cellpadding="0" align="center"><tr><td><img style="MARGIN-RIGHT: 10px" alt="【按右鍵影像屬性置入圖片】" width="100" height="100" align="left" /><font size=3><b>標題文字</b></font><br>內容文字‧內容文字‧<br>內容文字‧內容文字‧</td></tr></table>'
    },
    {
        title: '表格樣版四',
        image: 'template4.gif',
        description: '使用表格編排圖片及文字。圖片置右，文字有標題及內容【表格為透明，瀏覽時不會出現框線】',
        html: '<table width="95%" border="0" cellspacing="0" cellpadding="0" align="center"><tr><td><font size=3><b>標題文字</b></font><br><img style="MARGIN-RIGHT: 10px" alt="【按右鍵影像屬性置入圖片】" width="100" height="100" align="right" />內容文字‧內容文字‧<br>內容文字‧內容文字‧</td></tr></table>'
    },
    {
        title: '表格樣版五',
        image: 'template5.gif',
        description: '段落式圖文編排，圖片為左右左右放置【表格為透明，瀏覽時不會出現框線】',
        html: '<table width="95%" border="0" cellspacing="0" cellpadding="0" align="center"><tr><td><img style="MARGIN-RIGHT: 10px" alt="【按右鍵影像屬性置入圖片】" width="100" height="100" align="left" />第一段文字內容‧文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文‧<br><br><img style="MARGIN-LEFT: 10px" alt="【按右鍵影像屬性置入圖片】" width="100" height="100" align="right" />第二段文字內容‧文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文‧<br><br><img style="MARGIN-RIGHT: 10px" alt="【按右鍵影像屬性置入圖片】" width="100" height="100" align="left" />第三段文字內容‧文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文‧<br><br><img style="MARGIN-LEFT: 10px" alt="【按右鍵影像屬性置入圖片】" width="100" height="100" align="right" />第四段文字內容‧文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文字文‧</td></tr></table>'
    },
    {
        title: '表格樣版六',
        image: 'template6.gif',
        description: '圖片介紹條列編排，圖片置於表格左方，右方為條列式表格，可列出特點及內容【表格透明，瀏覽時不會出現框線】<',
        html: '<table width="95%" border="0" cellspacing="0" cellpadding="0" align="center"><tr><td width="1" valign="top"><img style="MARGIN-RIGHT: 5px" alt="【按右鍵影像屬性置入圖片】" width="100" height="100" align="left" /></td><td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="2"><tr><td width="80" align="right" valign="top"><b>‧項目一：</b></td><td valign="top">內容</td></tr><tr><td align="right" valign="top"><b>‧項目二：</b></td><td valign="top">內容</td></tr><tr><td align="right" valign="top"><b>‧項目三：</b></td><td valign="top">內容</td></tr><tr><td align="right" valign="top"><b>‧項目四：</b></td><td valign="top">內容</td></tr></table></td></tr></table>'
    },
    {
        title: '表格樣版七',
        image: 'template7.gif',
        description: '中英文抬頭，圖片置於表格左方，右方條列式介紹，可列出特點及內容，表格樣式立體。',
        html: '<table width="95%" border="1" align="center" cellpadding="0" cellspacing="0" bordercolorlight="#CCCCCC" bordercolordark="#FFFFFF" bgcolor="#FFFFFF"><tr><td valign="top"><font color="#CC3300" size="3"><B>ENGLISH TITTLE</B></font></td></tr><tr><td valign="top" bgcolor="#f5f5f5"><b>中文標題</b></td></tr><tr><td valign="top"><table width="98%" border="0" align="center" cellpadding="0" cellspacing="0"><tr><td width="1" valign="top"><img style="MARGIN-RIGHT: 10px" alt="【按右鍵影像屬性置入圖片】" width="100" height="100" align="left" /></td><td><table width="100%" border="1" align="center" cellpadding="1" cellspacing="0" bordercolorlight="#CCCCCC" bordercolordark="#FFFFFF" bgcolor="#FFFFFF"><tr><td width="15" align="center" valign="top">‧</td><td valign="top">內容</td></tr><tr><td align="center" valign="top">‧</td><td valign="top">內容</td></tr><tr><td align="center" valign="top">‧</td><td valign="top">內容</td></tr><tr><td align="center" valign="top">‧</td><td valign="top">內容</td></tr></table></td></tr></table></td></tr></table>'
    },
    {
        title: '表格樣版八',
        image: 'template8.gif',
        description: '標題及條列式內容編排，每項標題下有條列式表格，並以虛線作為區格',
        html: '<table width="95%" border="0" cellpadding="0" cellspacing="0" ><tr><td><b>標題文字一</b></td></tr><tr><td><table width="98%" border="0" align="center" cellpadding="2" cellspacing="0"><tr><td width="15" align="center" valign="top">‧</td><td valign="top">內容</td></tr><tr><td align="center" valign="top" bgcolor="#f5f5f5">‧</td><td valign="top" bgcolor="#f5f5f5">內容</td></tr><tr><td align="center" valign="top">‧</td><td valign="top">內容</td></tr><tr><td align="center" valign="top" bgcolor="#f5f5f5">‧</td><td valign="top" bgcolor="#f5f5f5">內容</td></tr></table></td></tr><tr><td height="20" align="right" background="../admin/images/line20.gif"><a href="#"><img src="../admin/images/top.gif" border="0"></a></td></tr><tr><td><br><b>標題文字二</b></td></tr><tr><td><table width="98%" border="0" align="center" cellpadding="2" cellspacing="0"><tr><td width="15" align="center" valign="top">‧</td><td valign="top">內容</td></tr><tr><td align="center" valign="top" bgcolor="#f5f5f5">‧</td><td valign="top" bgcolor="#f5f5f5">內容</td></tr><tr><td align="center" valign="top">‧</td><td valign="top">內容</td></tr><tr><td align="center" valign="top" bgcolor="#f5f5f5">‧</td><td valign="top" bgcolor="#f5f5f5">內容</td></tr></table></td></tr><tr><td background="../admin/images/line20.gif">&nbsp;</td></tr><tr><td><br><b>標題文字三</b></td></tr><tr><td><table width="98%" border="0" align="center" cellpadding="2" cellspacing="0"><tr><td width="15" align="center" valign="top">‧</td><td valign="top">內容</td></tr><tr><td align="center" valign="top" bgcolor="#f5f5f5">‧</td><td valign="top" bgcolor="#f5f5f5">內容</td></tr><tr><td align="center" valign="top">‧</td><td valign="top">內容</td></tr><tr><td align="center" valign="top" bgcolor="#f5f5f5">‧</td><td valign="top" bgcolor="#f5f5f5">內容</td></tr></table></td></tr><tr><td background="../admin/images/line20.gif">&nbsp;</td></tr></table>'
    },
    {
        title: '表格樣版九',
        image: 'template9.gif',
        description: '一列3組圖文介紹，圖片位於上方，下方為條列式文字介紹',
        html: '<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" bordercolorlight="#CCCCCC" bordercolordark="#FFFFFF" bgcolor="#FFFFFF"><tr><td valign="top"><table width="100" border="0" align="center" cellpadding="2" cellspacing="0"><tr><td align="center" valign="top"><img alt="【按右鍵影像屬性置入圖片】" width="100" height="100"/></td></tr><tr><td valign="top"><table width="100%" border="1" align="center" cellpadding="1" cellspacing="0" bordercolorlight="#CCCCCC" bordercolordark="#FFFFFF" bgcolor="#FFFFFF"><tr><td width="15" align="center" valign="top">‧</td><td valign="top">內容</td></tr><tr><td align="center" valign="top">‧</td><td valign="top">內容</td></tr><tr><td align="center" valign="top">‧</td><td valign="top">內容</td></tr><tr><td align="center" valign="top">‧</td><td valign="top">內容</td></tr></table></td></tr></table></td><td valign="top"><table width="100" border="0" align="center" cellpadding="2" cellspacing="0"><tr><td align="center" valign="top"><img alt="【按右鍵影像屬性置入圖片】" width="100" height="100"/></td></tr><tr><td valign="top"><table width="100%" border="1" align="center" cellpadding="1" cellspacing="0" bordercolorlight="#CCCCCC" bordercolordark="#FFFFFF" bgcolor="#FFFFFF"><tr><td width="15" align="center" valign="top">‧</td><td valign="top">內容</td></tr><tr><td align="center" valign="top">‧</td><td valign="top">內容</td></tr><tr><td align="center" valign="top">‧</td><td valign="top">內容</td></tr><tr><td align="center" valign="top">‧</td><td valign="top">內容</td></tr></table></td></tr></table></td><td valign="top"><table width="100" border="0" align="center" cellpadding="2" cellspacing="0"><tr><td align="center" valign="top"><img alt="【按右鍵影像屬性置入圖片】" width="100" height="100"/></td></tr><tr><td valign="top"><table width="100%" border="1" align="center" cellpadding="1" cellspacing="0" bordercolorlight="#CCCCCC" bordercolordark="#FFFFFF" bgcolor="#FFFFFF"><tr><td width="15" align="center" valign="top">‧</td><td valign="top">內容</td></tr><tr><td align="center" valign="top">‧</td><td valign="top">內容</td></tr><tr><td align="center" valign="top">‧</td><td valign="top">內容</td></tr><tr><td align="center" valign="top">‧</td><td valign="top">內容</td></tr></table></td></tr></table></td></tr></table>'
    },
    {
        title: '表格樣版十',
        image: 'template10.gif',
        description: '一列4組條列式表格，適用於相關連結或通訊錄編輯。',
        html: '<table width="95%" border="0" align="center" cellpadding="5" cellspacing="0" bordercolorlight="#CCCCCC" bordercolordark="#FFFFFF" bgcolor="#FFFFFF"><tr><td width="25%" valign="top"><table width="100%" border="1" align="center" cellpadding="1" cellspacing="0" bordercolorlight="#CCCCCC" bordercolordark="#FFFFFF" bgcolor="#FFFFFF"><tr><td width="15" align="center" valign="top">‧</td><td valign="top">內容</td></tr><tr><td align="center" valign="top">‧</td><td valign="top">內容</td></tr><tr><td align="center" valign="top">‧</td><td valign="top">內容</td></tr><tr><td align="center" valign="top">‧</td><td valign="top">內容</td></tr></table></td><td width="25%" valign="top"><table width="100%" border="1" align="center" cellpadding="1" cellspacing="0" bordercolorlight="#CCCCCC" bordercolordark="#FFFFFF" bgcolor="#FFFFFF"><tr><td width="15" align="center" valign="top">‧</td><td valign="top">內容</td></tr><tr><td align="center" valign="top">‧</td><td valign="top">內容</td></tr><tr><td align="center" valign="top">‧</td><td valign="top">內容</td></tr><tr><td align="center" valign="top">‧</td><td valign="top">內容</td></tr></table></td><td width="25%" valign="top"><table width="100%" border="1" align="center" cellpadding="1" cellspacing="0" bordercolorlight="#CCCCCC" bordercolordark="#FFFFFF" bgcolor="#FFFFFF"><tr><td width="15" align="center" valign="top">‧</td><td valign="top">內容</td></tr><tr><td align="center" valign="top">‧</td><td valign="top">內容</td></tr><tr><td align="center" valign="top">‧</td><td valign="top">內容</td></tr><tr><td align="center" valign="top">‧</td><td valign="top">內容</td></tr></table></td><td width="25%" valign="top"><table width="100%" border="1" align="center" cellpadding="1" cellspacing="0" bordercolorlight="#CCCCCC" bordercolordark="#FFFFFF" bgcolor="#FFFFFF"><tr><td width="15" align="center" valign="top">‧</td><td valign="top">內容</td></tr><tr><td align="center" valign="top">‧</td><td valign="top">內容</td></tr><tr><td align="center" valign="top">‧</td><td valign="top">內容</td></tr><tr><td align="center" valign="top">‧</td><td valign="top">內容</td></tr></table></td></tr></table>'
    }
    ]
});

