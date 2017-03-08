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
  infoIndex();
  // 用户首页
  $(document).on("pageInit", "#user-index", function (e, id, page) {
    var memberInfo = JSON.parse(localStorage.member_info);
    $('.money').append(memberInfo.yue);
    $('.integral').append(memberInfo.integral);
    var icon = (memberInfo.icon != null && memberInfo.icon != '') ? memberInfo.icon : '/public/static/index/img/null-icon.png';
    $('.item-photo').find('.member-icon').attr('src',icon);
    $('.member-mobile').text(memberInfo.mobile);
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
  // 登录后触发的方法
  indexLogin = function (url) {
    localStorage.login_url = url;
    if (localStorage.member_id) {
      $.router.load(url);
    } else {
      $.popup('.popup-login');
    }
  };
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
      if (address_input.length > 8) {
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
        $.alert('请精确到门牌号!8字以上');
      }
    });
    // 回收时间
    $('.recover-date').find('.tab-item').on('click', function () {
      $("input[name='recover_date']").val($(this).find('.font').text() + $(this).find('.tab-label').text());
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
      if (!address_city) {
        $.alert('请选择服务地址');
      } else {
        $.post('/index/order/add',
          {task_city: address_city, task_address: address_input, task_date: recover_date, task_time: recover_time},
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

  // 我的回收单
  $(document).on("pageInit", "#order-list", function (e, id, page) {
    orderInfo = function (id) {
      localStorage.task_id = id;
      indexLogin('/index/order/orderInfo');
    };
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
            list += '<div class="item-media"><img src="/favicon.png" style="width: 2.2rem;"></div>';
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
          var content = '';
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
