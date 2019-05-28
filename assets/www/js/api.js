var apiList = {
    doLogin: ['post', 'Index/ajax_login'],
    checkLogin: ['post', 'Index/check_login'],
    setNewToken: ['post', 'Manger/set_token', { key: 'code_token' }],
    setImgToken: ['post', 'Manger/set_token', { key: 'checknum' }],
    setSmsToken: ['post', 'Manger/set_token', { key: 'sms_token' }],
    setRegToken: ['post', 'Manger/set_token', { key: 'reg_checknum' }],
    //getImgToken: ['post', 'Manger/getImgToken'],
    //getSmsToken: ['post', 'Manger/getSmsToken'],
    getAllSession: ['post', 'Manger/getAllSession'],
    doTransfer: ['post', 'Manger/transfer_do'],
    getMakersBalance: ['post', 'Manger/ajax_balance'],
    getPhoneCode: ['post', 'Manger/ajax_phonecode'],
    doRegister: ['post', 'Manger/register_do'],
    doForgetPassword: ['post', 'Forget/forget_do'],
    checkAccount: ['post', 'Manger/ajax_chkupaccount']
}

function callApi(name, data, option) {
    option = option || {}
    data = data || {}

    if (!apiList[name]) return
    var type = apiList[name][0]
    var uri = apiList[name][1]
    var params = apiList[name][2]
    var url = CI_URL + uri

    if (params) {
        data = Object.assign(data, params)
    }

    var body = Object.assign({
        type: type,
        url: url,
        data: data,
        cache: false,
        dataType:"json"
    }, option)

    return $.ajax(body)
}
