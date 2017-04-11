$(function () {
  //首页
  $(".swiper-container").swiper({
    loop: true,
    lazyLoading: true,
    autoplay: 3000,
    height: 200,
    autoplayDisableOnInteraction: false,
    //centeredSlides: true,
  });
  urlJump = function (url) {
    $.router.load(url);
  }
  // 登录后触发的方法
  indexLogin = function (url) {
    localStorage.login_url = url;
    if (localStorage.member_id) {
      $.router.load(url);
    } else {
      $.toast('请先登录！');
      $.popup('.popup-login');
    }
  };
  //初始化用户信息
  var infoIndex = function () {
    if (localStorage.member_id) {
      $.post('/index/index/autoLogin', {member_id: localStorage.member_id}, function (data) {
        if (data.code > 0) {
          localStorage.member_id = data.member_id;
          localStorage.member_info = data.member_info;
          localStorage.login_url = '/index/user/index';
        }
      });
    }
  };
  var memberInfo = localStorage.member_info ? JSON.parse(localStorage.member_info) : '';
  var icon = (memberInfo.icon != null && memberInfo.icon != '') ? memberInfo.icon : '/public/static/index/img/null-icon.png';
  $('.item-photo').find('.member-icon').attr('src',icon);
  if(memberInfo) {
    $('.member-mobile').text(memberInfo.mobile);
  }
  price = function (id) {
    localStorage.tab_id = id;
    $.router.load('/index/order/price');
  }
  infoIndex();
  // 用户首页
  $(document).on("pageInit", "#user-index", function (e, id, page) {
    var memberInfo = localStorage.member_info ? JSON.parse(localStorage.member_info) : '';
    $('.money').append(memberInfo.yue.toFixed(2));
    $('.integral').append(memberInfo.integral);
    var address = (memberInfo.address_city != null && memberInfo.address_city != '') ? memberInfo.address_city + memberInfo.address_input : '请填写地址';
    $('.member-address').html(address + '<i class="icon icon-user-edit open-recover"></i>');
    // 地址初始化
    if (!memberInfo.address_city) {
      var arr = '四川 南充 高坪区'.split(' ');
      var input = '';
    } else {
      var arr = memberInfo.address_city.split(' ');
      var input = memberInfo.address_input;
      $('.address_city').text(memberInfo.address_city);
      $('.address_input').text(memberInfo.address_input);
    }
    $('.open-recover').on('click', function () {
      $.popup('.popup-recover');
    });
    $("#city-picker").cityPicker({
      value: [arr[0], arr[1], arr[2]]
    });
    $("input[name='address_input']").val(input);
    $('.edit-address').find('.submit-address').on('click', function () {
      var address_city = $("input[name='address_city']").val().trim();
      var address_input = $("input[name='address_input']").val().trim();
      if (address_city == '') {
        $.alert('请选择省市区');
      }
      if (address_input.length > 8) {
        $.post('/index/user/editMember', {address_city: address_city, address_input: address_input}, function (data) {
          $.alert(data.msg, function () {
            if (data.code > 0) {
              localStorage.member_info = data.data;
              $('.member-address').text(address_city + address_input + '<i class="icon icon-user-edit open-recover"></i>');
              $('.close-popup').click();
            }
          });
        });
      } else {
        $.alert('请精确到门牌号!8字以上');
      }
    });

  });
  // 登录
  $('#login').find('.login-button').on('click', function () {
    $('.submit-login').removeClass('login-button');
    var username = $('#login').find('input[name="username"]').val().trim();
    var password = $('#login').find('input[name="password"]').val().trim();
    if (username != '' && password != '') {
      $.post('/index/index/login', {username: username, password: password}, function (data) {
        $.alert(data.msg, function () {
          if (data.code > 0) {
            localStorage.member_id = data.member_id;
            localStorage.member_info = data.member_info;
            indexLogin(localStorage.login_url);
          } else {
            $('.submit-login').addClass('login-button');
          }
        });
      });
    } else {
      $.alert('用户名/密码不能为空！');
      $('.submit-login').addClass('login-button');
    }
  });
  // 注册
  $(document).on("pageInit", "#register", function (e, id, page) {
    $("input[name='mobile']").val('');
    $("input[name='code']").val('');
    $("input[name='password']").val('');
    $("input[name='repassword']").val('');
    var mobile, code, password, repassword, Account;
    var checkm, checkc, checkp, checkr;
    var itime = 59;
    var RemainTime = function () {
      if (itime > 0) {
        $(".register-code-resend c").text(itime);
        Account = setTimeout(function () {
          RemainTime();
        }, 1000);
        itime = itime - 1;
      } else {
        clearTimeout(Account);
        itime = 59;
        $(".register-code-send").show();
        $(".register-code-resend").hide();
      }
    };
    var checkMobile = function (mobile) {
      if (!checkPhone(mobile)) {
        checkm = false;
      } else {
        $.post('/index/user/checkMobile', {mobile: mobile}, function (data) {
          if (data.code) {
            $.alert(data.msg);
            checkm = false;
          } else {
            checkm = true;
          }
        });
      }
    };
    var sendCode = function () {
      mobile = $("input[name='mobile']").val().trim();
      if (!checkPhone(mobile)) {
        checkm = false;
      } else {
        $.post('/index/user/sendCode', {mobile: mobile}, function (data) {
          $.alert(data.msg, function (data) {
            if (data.code) {
              checkm = false;
            } else {
              $(".register-code-send").hide();
              $(".register-code-resend").show();
              checkm = true;
              RemainTime();
            }
          });
        });
      }
    };
    var checkCode = function (code) {
      if (code.length == 6) {
        $.post('/index/user/checkCode', {code: code}, function (data) {
          if (data.code) {
            $.alert(data.msg);
            checkc = false;
          } else {
            checkc = true;
          }
        });
      } else if (code == '') {
        checkc = false;
      } else {
        $.alert('验证码错误');
        checkc = false;
      }
    };
    $("input[name='mobile']").blur(function () {
      mobile = $("input[name='mobile']").val().trim();
      checkMobile(mobile);
    });
    $(".register-code-send").on('click', function () {
      sendCode();
    });
    $("input[name='code']").blur(function () {
      code = $("input[name='code']").val().trim();
      checkCode(code);
    });
    $("input[name='password']").blur(function () {
      password = $("input[name='password']").val().trim();
      if (!checkPassword(password)) {
        checkp = false;
      } else {
        checkp = true;
      }
    });
    $("input[name='repassword']").blur(function () {
      password = $("input[name='password']").val().trim();
      repassword = $("input[name='repassword']").val().trim();
      if (password != repassword) {
        $.alert('两次密码不一致');
        checkr = false;
      } else {
        checkr = true;
      }
    });
    $('.register-button').on('click', function () {
      $('.submit-register').removeClass('register-button');
      if (!checkm) {
        $.alert('手机号有误，请重新输入！');
        return false;
      }
      if (!checkc) {
        $.alert('验证码有误，请重新输入！');
        return false;
      }
      if (!checkp) {
        $.alert('密码格式有误，请重新输入！');
        return false;
      }
      if (!checkr) {
        $.alert('两次密码不一致，请重新输入！');
        return false;
      }
      if (!$("input[name='protocol']").prop('checked')) {
        $.alert('您还未接受用户协议');
        return false;
      }

      if (checkm && checkc && checkp && checkr) {
        $.post('/index/index/register', {mobile: mobile, password: password}, function (data) {
          if (data.code > 0) {
            $.alert(data.msg, function () {
              localStorage.member_id = data.member_id;
              localStorage.member_info = data.member_info;
              indexLogin(localStorage.login_url);
            });
          } else {
            $.alert('注册失败，请稍后再试！');
            $('.submit-register').addClass('register-button');
          }
        });
      } else {
        $('.submit-register').addClass('register-button');
      }
    });
  });
  //退出登录
  loginOut = function () {
    $.post('/index/index/loginOut', {}, function (data) {
      $.alert(data.msg, function () {
        if (data.code > 0) {
          var color_theme = localStorage.color_theme;
          var layout_theme = localStorage.layout_theme;
          localStorage.clear();
          localStorage.color_theme = color_theme;
          localStorage.layout_theme = layout_theme;
          infoIndex();
          window.location.href = '/';
        }
      });
    });
  };
  function checkPhone(mobile) {
    if (mobile == '') {
      $.alert('手机号不能为空！');
      return false;
    }
    var reg = /^1[34578]\d{9}$/;
    if (reg.test(mobile)) {
      return true;
    } else {
      $.alert('手机号有误，请重新输入!');
      return false;
    }
  };

  function checkPassword(password) {
    if (password == '') {
      $.alert('密码不能为空！');
      return false;
    }
    if (password.length < 6) {
      $.alert('密码必须 6 位以上，请重新输入!');
      return false;
    }
    return true;
  };

  $(document).on("pageInit", "#price", function (e, id, page) {
    var tab = $('.tab-link');
    var id = localStorage.tab_id;
    tab.eq(id).click();
  });
  // 开始回收
  $(document).on("pageInit", "#recover", function (e, id, page) {
    //日期时间初始化
    var mydate = new Date();
    for (var i = 1; i < 5; i++) {
      var time = 86400000 + mydate.getTime();
      mydate.setTime(time);
      var week = getWeek(mydate.getDay());
      $('.tab' + i).find('.font').text((mydate.getMonth() + 1) + '月' + mydate.getDate() + '日');
      $('.tab' + i).find('.tab-label').text(week);
      if (i == 1) {
        $("input[name='recover_date']").val((mydate.getMonth() + 1) + '月' + mydate.getDate() + '日' + week);
      }
    }
    // 地址初始化
    var memberInfo = JSON.parse(localStorage.member_info);
    if (!memberInfo.address_city) {
      $('.address-show').hide();
      $('.address-hide').show();
      var arr = '四川 南充 高坪区'.split(' ');
      var input = '';
    } else {
      var arr = memberInfo.address_city.split(' ');
      var input = memberInfo.address_input;
      $('.address-hide').hide();
      $('.address_city').text(memberInfo.address_city);
      $('.address_input').text(memberInfo.address_input);
    }
    $('.open-recover').on('click', function () {
      $.popup('.popup-recover');
    });
    $("#city-picker").cityPicker({
      value: [arr[0], arr[1], arr[2]]
    });
    $("input[name='address_input']").val(input);
    $('.edit-address').find('.submit-address').on('click', function () {
      var address_city = $("input[name='address_city']").val().trim();
      var address_input = $("input[name='address_input']").val().trim();
      if (address_city == '') {
        $.alert('请选择省市区');
      }
      if (address_input.length > 5) {
        $.post('/index/user/editMember', {address_city: address_city, address_input: address_input}, function (data) {
            $.alert(data.msg, function () {
              if (data.code > 0) {
                localStorage.member_info = data.data;
                $('.address-show').show();
                $('.address-hide').hide();
                $('.address_city').text(address_city);
                $('.address_input').text(address_input);
                $('.close-popup').click();
              }
            });
        });
      } else {
        $.alert('请精确到门牌号!5字以上');
      }
    });
    // 回收时间
    $('.recover-date').find('.tab-item').on('click', function () {
      $("input[name='recover_date']").val($(this).find('.font').text() + $(this).find('.tab-label').text());
      $("input[name='task_time']").val($(this).find("input[name='date']").val());
    });
    $('.recover-time').find('.tab-item').on('click', function () {
      $("input[name='recover_time']").val($(this).find('.tab-label').text());
    });
    // 提交
    $('.submit-recover').on('click', function () {
      var address_city = $("input[name='address_city']").val().trim();
      var address_input = $("input[name='address_input']").val().trim();
      var recover_date = $("input[name='recover_date']").val().trim();
      var recover_time = $("input[name='recover_time']").val().trim();
      var day = $("input[name='task_time']").val().trim();
      if (!address_city) {
        $.alert('请选择服务地址');
      } else {
        $.post('/index/order/add',
          {task_city:address_city, task_address:address_input, task_date:recover_date, task_time:recover_time, day:day},
          function (data) {
            $.alert(data.msg, function () {
              if (data.code > 0) {
                indexLogin('/index/order/orderList');
              }
            });
          });
      }
    });
  });

  var getWeek = function (day) {
    var today = new Array('(星期日)', '(星期一)', '(星期二)', '(星期三)', '(星期四)', '(星期五)', '(星期六)');
    return today[day];
  };
  var getRecoverName = function (type) {
    switch (type)
    {
      case 1:
        return '纸皮';break;
      case 2:
        return '塑料筐';break;
      case 3:
        return '进行中';break;
      default:
        return '废品';
    }
  };

  orderInfo = function (id) {
    localStorage.task_id = id;
    indexLogin('/index/order/orderInfo');
  };
  // 我的回收单
  $(document).on("pageInit", "#order-list", function (e, id, page) {
    var orderListLoading = false;
    var getOrderList = function (status, id) {
      var limit = $('#order-list ' + id + ' li').length;
      console.log(limit);
      $.post('/index/order/orderList', {status:status, limit:limit}, function (data) {
        if(data.sum <= limit) {$(id + ' .infinite-scroll-preloader').hide(); return;}
        if (data.code > 0 && data.sum > 0) {
          var list = '';
          $.each(data.data, function (i,n) {
            list += '<li><a href="#" onclick="orderInfo('+ n.task_id +')" class="item-link item-content">';
            list += '<div class="item-media"><img src="/public/static/sui/img/icon_recover.svg" style="width: 2.2rem;"></div>';
            list += '<div class="item-inner"> <div class="item-title-row">';
            list += '<div class="item-title">' + getRecoverName(n.task_type) + '回收</div>';
            list += '</div> <div class="item-subtitle">'+ n.task_date + n.task_time;
            list += '</div> </div> </a></li>';
          });
          $(id + ' .media-list ul').append(list);
          if(data.data.length < 8) {$(id + ' .infinite-scroll-preloader').hide(); return;}
          if(data.data.length == data.sum) {$(id + ' .infinite-scroll-preloader').hide(); return;}
        } else {
          $(id + ' .infinite-scroll-preloader').hide(); return;
        }
      });
    };

    getOrderList(2, '#tab1');
    $('.order-tab1').on('click', function () {
      getOrderList(2, '#tab1');
    });
    $('.order-tab2').on('click', function () {
      getOrderList(1, '#tab2');
    });
    $(page).on('infinite', function () {
      if(orderListLoading) return;
      orderListLoading = true;
      if($(this).find('.infinite-scroll.active').attr('id') == "tab1"){
        getOrderList(2, '#tab1');
      } else {
        getOrderList(1, '#tab2');
      }

    });
  });
  // 回收单详情
  $(document).on("pageInit", "#order-info", function (e, id, page) {
    var task_id = localStorage.task_id;
    $.post('/index/order/orderInfo', {task_id:task_id}, function (data) {
      if(data.code > 0) {
        var data = data.data;
        $('.task_date').text(data.task_date);
        if(data.task_status == 2) {
          if(data.user_id > 0) {
            $('.task_status').text('已派单');
            $('.recover_user').text(data.username + data.mobile);
          } else {
            $('.task_status').text('等待派单');
            $('.recover_user').text('待定');
          }
        } else if(data.task_status == 1) {
          $('.task_status').text('已完成');
          $('.recover_user').text(data.username + data.mobile);
          var task_type = data.task_type == 2 ? '现金交易' : '线上交易';
          var content = '';
          content += '<li> <div class="item-content"> <div class="item-inner">';
          content += '<div class="item-title label">支付方式：</div>';
          content += '<div class="item-input">' + task_type + '</div>';
          content += '</div> </div> </li>';
          content += '<li> <div class="item-content"> <div class="item-inner">';
          content += '<div class="item-title label">回收品类：</div>';
          content += '<div class="item-input">' + data.recover_name + '</div>';
          content += '</div> </div> </li>';
          content += '<li> <div class="item-content"> <div class="item-inner">';
          content += '<div class="item-title label">回收单价：</div>';
          content += '<div class="item-input">' + data.task_price + '</div>';
          content += '</div> </div> </li>';
          content += '<li> <div class="item-content"> <div class="item-inner">';
          content += '<div class="item-title label">回收数量：</div>';
          content += '<div class="item-input">' + data.number + '</div>';
          content += '</div> </div> </li>';
          content += '<li> <div class="item-content"> <div class="item-inner">';
          content += '<div class="item-title label">回收总价：</div>';
          content += '<div class="item-input">' + data.task_money + '</div>';
          content += '</div> </div> </li>';
          content += '<li> <div class="item-content"> <div class="item-inner">';
          content += '<div class="item-title label">完成时间：</div>';
          var mydate = new Date();
          mydate.setTime(data.end_time * 1000);
          content += '<div class="item-input">' + mydate.format('yyyy-MM-dd h:m') + '</div>';
          content += '</div> </div> </li>';
          $(page).find('ul').append(content);
        }
      }

    });
  });
  /*我的钱包*/
  $(document).on("pageInit", "#money", function (e, id, page) {
    var memberInfo = JSON.parse(localStorage.member_info);
    $('.money').text('可提现余额为：￥' + memberInfo.yue.toFixed(2));
  });
  /*checkMoney = function (i) {
    $.toast(i);
    /!*if (mobile == '') {
      $.alert('手机号不能为空！');
      return false;
    }
    var reg = /^1[34578]\d{9}$/;
    if (reg.test(mobile)) {
      return true;
    } else {
      $.alert('手机号有误，请重新输入!');
      return false;
    }*!/
  };*/
  /*提现*/
  $(document).on("pageInit", "#withdraw", function (e, id, page) {
    var memberInfo = JSON.parse(localStorage.member_info);
    var money = memberInfo.yue.toFixed(2);
    $('.money').text('可提现余额为：￥' + money);
    checkMoney = function (i) {
      if(i <= 0) {
        $.toast('请输入合法数值');
        $("input[name='withdraw']").val('');
      }
      if(i > memberInfo.yue) {
        $.toast('最多可提现' + money);
        $("input[name='withdraw']").val(money);
      }
      var arr = i.split('.');
      if(arr[1] && arr[1].length >2) {
        $.toast('最多至小数后两位');
        $("input[name='withdraw']").val(Math.floor(i*100)/100);
      }
    };
    // 微信
    if(memberInfo.wechat != null && memberInfo.wechat != '') {
      $('.wechat').text(memberInfo.wechat);
      $("input[name='wechat']").val(memberInfo.wechat);
    } else {
      $('.button-wx').on('click', function () {
        $.popup('.popup-wx');
      });
      $('.submit-wechat').on('click', function () {
        var wechat = $("input[name='wechat']").val().trim();
        if(wechat != '') {
          $.post('/index/user/editMember', {wechat:wechat}, function (data) {
            localStorage.member_info = data.data;
            $('.wechat').text(wechat);
            $('.close-popup').click();
          });
        } else {
          $.toast('请完整填写信息'); return false;
        }
      });
    }
    // 支付宝
    if(memberInfo.alipay != null && memberInfo.alipay != '') {
      $('.alipay').text(memberInfo.alipay);
      $("input[name='alipay']").val(memberInfo.alipay);
    } else {
      $('.button-zfb').on('click', function () {
        $.popup('.popup-zfb');
      });
      $('.submit-alipay').on('click', function () {
        var alipay = $("input[name='alipay']").val().trim();
        if(alipay != '') {
          $.post('/index/user/editMember', {alipay:alipay}, function (data) {
            localStorage.member_info = data.data;
            $('.alipay').text(alipay);
            $('.close-popup').click();
          });
        } else {
          $.toast('请完整填写信息'); return false;
        }
      });
    }
    // 银行卡
    $("input[name='actual_name']").val(memberInfo.actual_name);
    $("input[name='bank_name']").val(memberInfo.bank_name);
    if(memberInfo.bank != null && memberInfo.bank != '') {
      $('.bank').text(memberInfo.bank);
      $("input[name='bank']").val(memberInfo.bank);
    } else {
      $('.button-bank').on('click', function () {
        $.popup('.popup-bank');
      });
      $('.submit-bank').on('click', function () {
        var actual_name = $("input[name='actual_name']").val().trim();
        var bank_name = $("input[name='bank_name']").val().trim();
        var bank = $("input[name='bank']").val().trim();
        if(actual_name != '' && bank_name != '' && bank != '') {
          $.post('/index/user/editMember', {actual_name:actual_name, bank_name:bank_name, bank:bank}, function (data) {
            localStorage.member_info = data.data;
            $('.bank').text(bank);
            $('.close-popup').click();
          });
        } else {
          $.toast('请完整填写信息'); return false;
        }
      });
    }
    //提交
    $('.submit-withdraw').on('click', function () {
      var memberInfo = JSON.parse(localStorage.member_info);
      var withdraw = $("input[name='withdraw']").val().trim();
      var my_radio = $("input[name='my_radio']:checked").val();
      if(!withdraw) {$.toast('请输入提现金额'); return false;}
      if(withdraw <= 0) {$.toast('提现金额必须大于0'); return false;}
      if(withdraw > memberInfo.yue) {$.toast('提现金额大于余额'); return false;}
      if(!my_radio) {$.toast('请选择提现方式'); return false;}
      var account = eval('memberInfo.'+my_radio);
      if(account != '') {
        $.post('/index/funds/withdraw', {money:withdraw, type:my_radio}, function (data) {
          $.alert(data.msg, function () {
            if(data.code > 0) {
              localStorage.member_info = data.data;
              memberInfo = JSON.parse(data.data);
              $('.money').text('可提现余额为：￥' + memberInfo.yue.toFixed(2));
              $('.back').click();
            }
          });
        });
      } else {
        $.toast('该提现方式为空，请填写或选择其他提现方式');
        return false;
      }
    });
  });

  /*我的积分*/
  $(document).on("pageInit", "#integral", function (e, id, page) {
    var memberInfo = JSON.parse(localStorage.member_info);
    $('.integral').text('可使用积分为：' + memberInfo.integral);
  });

  /*我的统计*/
  $(document).on("pageInit", "#count", function (e, id, page) {
    var memberInfo = JSON.parse(localStorage.member_info);
    var sum_money = (memberInfo.yue + memberInfo.money).toFixed(2);
    $('.sum_money').text('￥' + sum_money);
    $('.use_money').text('￥' + memberInfo.money.toFixed(2));
    $('.use_integral').text(memberInfo.use_integral);
  });

  withdrawInfo = function (id) {
    localStorage.withdraw_id = id;
    indexLogin('/index/funds/withdrawInfo');
  };
  /*提现列表*/
  $(document).on("pageInit", "#withdraw-list", function (e, id, page) {
    var orderListLoading = false;
    var getOrderList = function (status, id) {
      var limit = $('#withdraw-list ' + id + ' li').length;
      console.log(limit);
      $.post('/index/funds/withdrawList', {status:status, limit:limit}, function (data) {
        if(data.sum <= limit) {$(id + ' .infinite-scroll-preloader').hide(); return;}
        if (data.code > 0 && data.sum > 0) {
          var list = '',icon = '',title;
          var mydate = new Date();
          $.each(data.data, function (i,n) {
            //日期时间初始化
            mydate.setTime(n.create_time * 1000);
            list += '<li><a href="#" onclick="withdrawInfo('+ n.id +')" class="item-link item-content">';
            if(n.type == 1) {
              icon = '/public/static/sui/img/icon-wechat.svg';
              title = '微信提现';
            } else if(n.type == 2) {
              icon = '/public/static/sui/img/icon-alipay.svg';
              title = '支付宝提现';
            } else if(n.type == 3) {
              icon = '/public/static/sui/img/icon-card.svg';
              title = '银行卡提现';
            }
            list += '<div class="item-media"><img src="'+ icon +'" style="width: 2.2rem;"></div>';
            list += '<div class="item-inner"> <div class="item-title-row">';
            list += '<div class="item-title">' + title + '</div>';
            list += '<div class="item-after">-' + n.money.toFixed(2) + '</div>';
            list += '</div> <div class="item-subtitle">'+ mydate.format('yyyy-MM-dd h:m');
            list += '</div> </div> </a></li>';
          });
          $(id + ' .media-list ul').append(list);
          if(data.data.length < 8) {$(id + ' .infinite-scroll-preloader').hide(); return;}
          if(data.data.length == data.sum) {$(id + ' .infinite-scroll-preloader').hide(); return;}
        } else {
          $(id + ' .infinite-scroll-preloader').hide(); return;
        }
      });
    };

    getOrderList(2, '#tab1');
    $('.order-tab1').on('click', function () {
      getOrderList(2, '#tab1');
    });
    $('.order-tab2').on('click', function () {
      getOrderList(1, '#tab2');
    });
    $(page).on('infinite', function () {
      if(orderListLoading) return;
      orderListLoading = true;
      if($(this).find('.infinite-scroll.active').attr('id') == "tab1"){
        getOrderList(2, '#tab1');
      } else {
        getOrderList(1, '#tab2');
      }

    });
  });

  /*提现详情*/
  $(document).on("pageInit", "#withdraw-info", function (e, id, page) {
    var withdraw_id = localStorage.withdraw_id;
    $.post('/index/funds/withdrawInfo', {withdraw_id:withdraw_id}, function (data) {
      if(data.code > 0) {
        var data = data.data;
        var title = '';
        $('.withdraw_money').text(data.money.toFixed(2));//金额
        if(data.type == 1) {
          title = '微信提现';
        } else if(data.type == 2) {
          title = '支付宝提现';
        } else if(data.type == 3) {
          title = '银行卡提现';
        }
        $('.withdraw_type').text(title);//类型
        var mydate = new Date();
        mydate.setTime(data.create_time * 1000);
        $('.withdraw_date').text(mydate.format('yyyy-MM-dd'));//时间
        $('.withdraw_info').html(data.info);//信息
        if(data.status == 1) {
          var content = '';
          content += '<li> <div class="item-content"> <div class="item-inner">';
          content += '<div class="item-title label">回执单号：</div>';
          content += '<div class="item-input">' + data.receipt_id + '</div>';
          content += '</div> </div> </li>';
          content += '<li> <div class="item-content"> <div class="item-inner">';
          content += '<div class="item-title label">完成时间：</div>';
          mydate.setTime(data.run_time * 1000);
          content += '<div class="item-input">' + mydate.format('yyyy-MM-dd h:m:s') + '</div>';
          content += '</div> </div> </li>';
          $(page).find('ul').append(content);
        }
      }

    });
  });
  /*提现详情*/
  $(document).on("pageInit", "#income-list", function (e, id, page) {
    var orderListLoading = false;
    var getOrderList = function () {
      var limit = $('#withdraw-list li').length;
      console.log(limit);
      $.post('/index/user/incomeList', {limit:limit}, function (data) {
        if(data.sum <= limit) {$('.infinite-scroll-preloader').hide(); return;}
        if (data.code > 0 && data.sum > 0) {
          var list = '',title,money;
          var mydate = new Date();
          $.each(data.data, function (i,n) {
            //日期时间初始化
            mydate.setTime(n.create_time * 1000);
            if(n.type == 1) {
              list += '<li><a href="#" onclick="orderInfo('+ n.order_id +')" class="item-link item-content">';
              title = '废品回收';
              money = '<div class="item-after" style="color:green">+';
            } else if(n.type == 2) {
              list += '<li><a href="#" onclick="withdrawInfo('+ n.order_id +')" class="item-link item-content">';
              title = '提现';
              money = '<div class="item-after">-';
            }
            list += '<div class="item-inner"> <div class="item-title-row">';
            list += '<div class="item-title">' + title + '</div>';
            list += money + n.money.toFixed(2) + '</div>';
            list += '</div> <div class="item-subtitle">'+ mydate.format('yyyy-MM-dd h:m');
            list += '</div> </div> </a></li>';
          });
          $('.media-list ul').append(list);
          if(data.data.length < 8) {$('.infinite-scroll-preloader').hide(); return;}
          if(data.data.length == data.sum) {$('.infinite-scroll-preloader').hide(); return;}
        } else {
          $('.infinite-scroll-preloader').hide(); return;
        }
      });
    };
    getOrderList();
    $(page).on('infinite', function () {
      if(orderListLoading) return;
      orderListLoading = true;
        getOrderList();
    });
  });

  //时间处理函数
  Date.prototype.format = function(format) {
    var date = {
      "M+": this.getMonth() + 1,
      "d+": this.getDate(),
      "h+": this.getHours(),
      "m+": this.getMinutes(),
      "s+": this.getSeconds(),
      "q+": Math.floor((this.getMonth() + 3) / 3),
      "S+": this.getMilliseconds()
    };
    if (/(y+)/i.test(format)) {
      format = format.replace(RegExp.$1, (this.getFullYear() + '').substr(4 - RegExp.$1.length));
    }
    for (var k in date) {
      if (new RegExp("(" + k + ")").test(format)) {
        format = format.replace(RegExp.$1, RegExp.$1.length == 1
          ? date[k] : ("00" + date[k]).substr(("" + date[k]).length));
      }
    }
    return format;
  };

  $.init();
});
