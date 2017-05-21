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
  //路径跳转
  urlJump = function (url) {
    $.router.load(url);
  };
  // 登录后触发的方法
  indexLogin = function (url) {
    localStorage.login_url = url;
    /*if(localStorage.aid > 0) {
      if(url == '/index/order/recover' || url == '/index/order/orderList') {
        $.router.load('/index/order/userOrderList');
      } else {
        $.router.load(url);
      }
    } else {*/
      if (localStorage.member_id) {
        $.router.load(url);
      } else {
        $.toast('请先登录！');
        $.popup('.popup-login');
      }
    //}
  };
  //初始化用户信息
  infoIndex = function () {
    if (localStorage.member_id) {
      var aid = localStorage.aid ? localStorage.aid : 0;
      $.post('/index/index/autoLogin', {member_id: localStorage.member_id, aid:aid}, function (data) {
        if (data.code > 0) {
          if(data.aid) {
            localStorage.aid = data.aid;
          }
          localStorage.member_id = data.member_id;
          localStorage.member_info = data.member_info;
          localStorage.login_url = '/index/user/index';
        }
      });
    }
  };
  //var memberInfo = localStorage.member_info ? localStorage.member_info : '';
  var memberInfo = localStorage.member_info ? JSON.parse(localStorage.member_info) : '';
  var icon = (memberInfo.icon != null && memberInfo.icon != '') ? memberInfo.icon : '/public/static/index/img/null-icon.png';
  $('.item-photo').find('.member-icon').attr('src',icon);
  if(memberInfo) {
    $('.member-mobile').text(memberInfo.mobile);
  }
  price = function (id) {
    localStorage.price_id = id;
    $.router.load('/index/order/price');
  };
  infoIndex();
  // 用户首页
  $(document).on("pageInit", "#user-index", function (e, id, page) {
    var memberInfo = localStorage.member_info ? JSON.parse(localStorage.member_info) : '';
    $('.money').append(toDecimal2(memberInfo.yue));
    //$('.money').append(memberInfo.yue.toFixed(2));
    var integral = memberInfo.integral ? memberInfo.integral : 0;
    $('.integral').append(integral);
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
        $.toast('请选择省市区');
      }
      if (address_input.length > 8) {
        $.post('/index/user/editMember', {address_city: address_city, address_input: address_input}, function (data) {
            $.toast(data.msg);
            if (data.code > 0) {
              localStorage.member_info = data.data;
              $('.member-address').text(address_city + address_input + '<i class="icon icon-user-edit open-recover"></i>');
              $('.close-popup').click();
            }
        });
      } else {
        $.toast('请精确到门牌号!8字以上');
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
          $.toast(data.msg);
          if (data.code > 0) {
            localStorage.member_id = data.member_id;
            localStorage.member_info = data.member_info;
            if(data.aid) {
              localStorage.aid = data.aid;
              $('.close-popup').click();
              //indexLogin('/');
            }
            setTimeout("indexLogin(localStorage.login_url)",1500);
          } else {
            $('.submit-login').addClass('login-button');
          }
      });
    } else {
      $.toast('用户名/密码不能为空！');
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
            $.toast(data.msg);
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
            $.toast(data.msg);
            if (data.code) {
              checkm = false;
            } else {
              $(".register-code-send").hide();
              $(".register-code-resend").show();
              checkm = true;
              RemainTime();
            }
        });
      }
    };
    var checkCode = function (code) {
      if (code.length == 6) {
        $.post('/index/user/checkCode', {code: code}, function (data) {
          if (data.code) {
            $.toast(data.msg);
            checkc = false;
          } else {
            checkc = true;
          }
        });
      } else if (code == '') {
        checkc = false;
      } else {
        $.toast('验证码错误');
        checkc = false;
      }
    };
    $("input[name='mobile']").blur(function () {
      mobile = $(this).val().trim();
      checkMobile(mobile);
    });
    $(".register-code-send").on('click', function () {
      sendCode();
    });
    $("input[name='code']").blur(function () {
      code = $(this).val().trim();
      checkCode(code);
    });
    $("input[name='password']").blur(function () {
      password = $(this).val().trim();
      if (!checkPassword(password)) {
        checkp = false;
      } else {
        checkp = true;
      }
    });
    $("input[name='repassword']").blur(function () {
      password = $("input[name='password']").val().trim();
      repassword = $(this).val().trim();
      if (password != repassword) {
        $.toast('两次密码不一致');
        checkr = false;
      } else {
        checkr = true;
      }
    });
    $('.register-button').on('click', function () {
      $('.submit-register').removeClass('register-button');
      if (!checkm) {
        $.toast('手机号有误，请重新输入！');
        return false;
      }
      if (!checkc) {
        $.toast('验证码有误，请重新输入！');
        return false;
      }
      if (!checkp) {
        $.toast('密码格式有误，请重新输入！');
        return false;
      }
      if (!checkr) {
        $.toast('两次密码不一致，请重新输入！');
        return false;
      }
      if (!$("input[name='protocol']").prop('checked')) {
        $.toast('您还未接受用户协议');
        return false;
      }

      if (checkm && checkc && checkp && checkr) {
        $.post('/index/index/register', {mobile: mobile, password: password}, function (data) {
          if (data.code > 0) {
              $.toast(data.msg);
              localStorage.member_id = data.member_id;
              localStorage.member_info = data.member_info;
              indexLogin(localStorage.login_url);
          } else {
            $.toast('注册失败，请稍后再试！');
            $('.submit-register').addClass('register-button');
          }
        });
      } else {
        $('.submit-register').addClass('register-button');
      }
    });
  });

// 忘记密码
$(document).on("pageInit", "#repassword", function (e, id, page) {
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
                if (!data.code) {
                    $.toast('手机号未注册,请注册登录!');
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
            $.post('/index/user/sendCodePass', {mobile: mobile}, function (data) {
                $.toast(data.msg);
                if (data.code) {
                    checkm = false;
                } else {
                    $("input[name='id']").val(data.data.id);
                    $(".register-code-send").hide();
                    $(".register-code-resend").show();
                    checkm = true;
                    RemainTime();
                }
            });
        }
    };
    var checkCode = function (code) {
        if (code.length == 6) {
            $.post('/index/user/checkCode', {code: code}, function (data) {
                if (data.code) {
                    $.toast(data.msg);
                    checkc = false;
                } else {
                    checkc = true;
                }
            });
        } else if (code == '') {
            checkc = false;
        } else {
            $.toast('验证码错误');
            checkc = false;
        }
    };
    /*$("input[name='mobile']").blur(function () {
        mobile = $("input[name='mobile']").val().trim();
        checkMobile(mobile);
    });*/
    $(".register-code-send").on('click', function () {
        sendCode();
    });
    $("input[name='code']").blur(function () {
        code = $(this).val().trim();
        checkCode(code);
    });
    $("input[name='password']").blur(function () {
        password = $(this).val().trim();
        if (!checkPassword(password)) {
            checkp = false;
        } else {
            checkp = true;
        }
    });
    $("input[name='repassword']").blur(function () {
        password = $("input[name='password']").val().trim();
        repassword = $(this).val().trim();
        if (password != repassword) {
            $.toast('两次密码不一致');
            checkr = false;
        } else {
            checkr = true;
        }
    });
    $('.password-button').on('click', function () {
        $('.submit-password').removeClass('password-button');
        var id = $("input[name='id']").val();
        if (!checkm) {
            $.toast('手机号有误，请重新输入!');
            return false;
        }
        if (!checkc) {
            $.toast('验证码有误，请重新输入!');
            return false;
        }
        if (!checkp) {
            $.toast('密码格式有误，请重新输入!');
            return false;
        }
        if (!checkr) {
            $.toast('两次密码不一致，请重新输入!');
            return false;
        }
        if (!id) {
            $.toast('信息错误，请稍后再试!');
            return false;
        }

        if (checkm && checkc && checkp && checkr && id) {
            $.post('/index/index/repassword', {id:id, mobile: mobile, password: password}, function (data) {
                $.toast(data.msg);
                if (data.code > 0) {
                    $.popup('.popup-login');
                } else {
                    $('.submit-password').addClass('password-button');
                }
            });
        } else {
            $('.submit-password').addClass('password-button');
        }
    });
});
  //退出登录
  loginOut = function () {
    $.post('/index/index/loginOut', {}, function (data) {
        $.toast(data.msg);
        if (data.code > 0) {
          localStorage.clear();
          window.location.href = '/';
        }
    });
  };
  function checkPhone(mobile) {
    if (mobile == '') {
      $.toast('手机号不能为空！');
      return false;
    }
    var reg = /^1[34578]\d{9}$/;
    if (reg.test(mobile)) {
      return true;
    } else {
      $.toast('手机号有误，请重新输入!');
      return false;
    }
  }

  function checkPassword(password) {
    if (password == '') {
      $.toast('密码不能为空！');
      return false;
    }
    if (password.length < 6) {
      $.toast('密码必须 6 位以上，请重新输入!');
      return false;
    }
    return true;
  }

  //强制保留2位小数，如：2，会在2后面补上00.即2.00
  function toDecimal2(x) {
    var f = parseFloat(x);
    if (isNaN(f)) {
      return false;
    }
    var f = Math.round(x*100)/100;
    var s = f.toString();
    var rs = s.indexOf('.');
    if (rs < 0) {
      rs = s.length;
      s += '.';
    }
    while (s.length <= rs + 2) {
      s += '0';
    }
    return s;
  };

  $(document).on("pageInit", "#price", function (e, id, page) {
    var tab = $('.tab-link');
    var id = localStorage.price_id;
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
        $.toast('请选择省市区');
      }
      if (address_input.length > 5) {
        $.post('/index/user/editMember', {address_city: address_city, address_input: address_input}, function (data) {
            $.toast(data.msg);
              if (data.code > 0) {
                localStorage.member_info = data.data;
                $('.address-show').show();
                $('.address-hide').hide();
                $('.address_city').text(address_city);
                $('.address_input').text(address_input);
                $('.close-popup').click();
              }
        });
      } else {
        $.toast('请精确到门牌号!5字以上');
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
        $.toast('请选择服务地址');
      } else {
        $.post('/index/order/add',
          {task_city:address_city, task_address:address_input, task_date:recover_date, task_time:recover_time, day:day},
          function (data) {
              $.toast(data.msg);
              if (data.code > 0) {
                indexLogin('/index/order/orderList');
              }
          });
      }
    });
  });

  var getWeek = function (day) {
    var today = new Array('(星期日)', '(星期一)', '(星期二)', '(星期三)', '(星期四)', '(星期五)', '(星期六)');
    return today[day];
  };

  // 新增订单
  $(document).on("pageInit", "#add-order", function (e, id, page) {
    // 服务客户选择触发
    $('.member_id').change(function () {
      var id = $(this).val();
      $.post('/index/order/getMemberPice', {id:id}, function (data) {
        console.log(data.data);
        var price = '';
        if (data.data && data.data.length > 0) {
            $.each(data.data, function (i, n) {
                price += '<option value="' + n.id + '" data-price="' + n.price + '">' + n.name + '</option>';
            });
            $('.recover_type').append(price);
            // 回收类型选择触发
            $('.recover_type').change(function () {
                var id = $(this).val();
                var price = $(this).find('option[value="'+id+'"]').data('price');
                $('.task_price').text(price);
                $('input[name="task_price"]').val(price);
                var num = $('input[name="number"]').val();
                if(num > 0) {
                    var sum = ((price * 100) * (num * 100)) / 10000;
                    var task_money = toDecimal2(sum);
                    $('.task_money').text(task_money);
                    $('input[name="task_money"]').val(task_money);
                }
            });
            //计算总价
            sum = function (val) {
                var task_price = $('input[name="task_price"]').val();
                var task_type = $('.task_type').val();
                if(task_type == 0) {$.toast('请选择支付方式！'); return false;}
                if(task_price > 0) {
                    var sum = ((task_price * 100) * (val * 100)) / 10000;
                    var task_money = toDecimal2(sum);
                    $('.task_money').text(task_money);
                    $('input[name="task_money"]').val(task_money);
                } else {
                    $.toast('请选择回收品类！');return false;
                }
            };
        }
      });
    });
    //提交订单数据
    $('.submit-order').on('click',function () {
        //日期时间初始化
        var task_time;
        var mydate = new Date();
        var time = mydate.getTime();
        mydate.setTime(time);
        var week = getWeek(mydate.getDay());
        var task_date = (mydate.getMonth() + 1) + '月' + mydate.getDate() + '日' + week;
        var hours = mydate.getHours();
        if(hours < 13) {
            task_time = '8:00~12:00';
        } else if(hours < 17) {
            task_time = '13:00~17:00';
        } else {
            task_time = '17:00~19:00';
        }
      var member_id = $('.member_id').val();
      var task_type = $('.task_type').val();
      var recover_type = $('.recover_type').val();
      var task_price = $('input[name="task_price"]').val();
      var number = $('input[name="number"]').val();
      var task_money = $('input[name="task_money"]').val();
      if(member_id == 0) {$.toast('请选择服务客户！'); return false;}
      if(task_type == 0) {$.toast('请选择支付方式！'); return false;}
      if(recover_type == 0) {$.toast('请选择回收品类！'); return false;}
      if(number == 0) {$.toast('请输入数量！'); return false;}
      if(member_id > 0 && task_type > 0 && recover_type > 0 && task_price > 0 && number > 0 && task_money > 0) {
          $.post('/index/order/addOrder', {
              member_id:member_id,task_date:task_date,task_time:task_time,task_type:task_type,recover_type:recover_type,task_price:task_price,number:number,task_money:task_money
          }, function (data) {
              $.toast(data.msg);
              if(data.code == 1) {
                  localStorage.member_info = data.data;
                  setTimeout("$.router.back()",1500);
              }
          });
      } else {
          $.toast('数据错误!请重新填写!');return false;
      }
    });
  });
  // 添加客户
  $(document).on("pageInit", "#add-user", function (e, id, page) {
      var checkm = false,mobile = '';
      var checkMobile = function (mobile) {
          if (!checkPhone(mobile)) {
              checkm = false;
          } else {
              $.post('/index/user/checkMobile', {mobile: mobile}, function (data) {
                  if (data.code) {
                      $.toast('该手机号已经注册');
                      checkm = false;
                  } else {
                      checkm = true;
                  }
              });
          }
      };
      $("input[name='mobile']").blur(function () {
          mobile = $(this).val().trim();
          checkMobile(mobile);
      });
      // 地址初始化
      var arr = '四川 南充 高坪区'.split(' ');
      var input = '';
      $("#city-picker").cityPicker({
          value: [arr[0], arr[1], arr[2]]
      });
      $("input[name='address_input']").val(input);
      // 提交
      $('.submit-addUser').on('click', function () {
          var username = $("input[name='username']").val().trim();
          var address_city = $("input[name='address_city']").val().trim();
          var address_input = $("input[name='address_input']").val().trim();
          if (address_input == '' && username == '' && !checkm) {
              $.toast('均为必填项,不可为空!');
          } else {
              $.post('/index/user/addUser',
                  {address_city:address_city, address_input:address_input, username:username, mobile:mobile},
                  function (data) {
                      $.toast(data.msg);
                      if (data.code > 0) {
                          setTimeout("$.router.back()",1500);
                      }
                  });
          }
      });
  });
  memberPage = function (id) {
      localStorage.member_page_id = id;
      indexLogin('/index/user/memberInfo');
  };
  // 客户列表
  $(document).on("pageInit", "#member-list", function (e, id, page) {
    var orderListLoading = false;
    var getMemberList = function (type, id) {
        var limit = $('#order-list ' + id + ' li').length;
        $.post('/index/user/memberList', {type:type, limit:limit}, function (data) {
            if(data.sum <= limit) {$(id + ' .infinite-scroll-preloader').hide(); return;}
            if (data.code > 0 && data.sum > 0) {
                var list = '';
                $.each(data.data, function (i,n) {
                    list += '<li><a href="#" class="item-link item-content">';
                    //list += '<li><a href="#" onclick="memberPage('+ n.id +')" class="item-link item-content">';
                    list += '<div class="item-media"><img src="/public/static/sui/img/icon_recover.svg" style="width: 2.2rem;"></div>';
                    list += '<div class="item-inner"> <div class="item-title-row">';
                    list += '<div class="item-title">' + n.username + '</div>';
                    list += '</div> <div class="item-subtitle">'+ n.address_city + n.address_input;
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

    getMemberList(0, '#tab1');
    $('.order-tab1').on('click', function () {
        if(!$(this).hasClass('active')) {
            $('#tab1 .media-list ul').html('');
            getMemberList(0, '#tab1');
        }
    });
    $('.order-tab2').on('click', function () {
        if(!$(this).hasClass('active')) {
            $('#tab2 .media-list ul').html('');
            getMemberList(1, '#tab2');
        }
    });
    $(page).on('infinite', function () {
        if(orderListLoading) return;
        orderListLoading = true;
        if($(this).find('.infinite-scroll.active').attr('id') == "tab1"){
            getMemberList(0, '#tab1');
        } else {
            getMemberList(1, '#tab2');
        }

    });
  });

  orderInfo = function (id) {
    localStorage.task_id = id;
    indexLogin('/index/order/orderInfo');
  };
  // 我的回收单
  $(document).on("pageInit", "#order-list", function (e, id, page) {
    var orderListLoading = false;
    var getOrderList = function (status, id, type) {
      var limit = $('#order-list ' + id + ' li').length;
      console.log(limit);
      $.post('/index/order/orderList', {status:status, limit:limit}, function (data) {
        if(data.sum <= limit) {$(id + ' .infinite-scroll-preloader').hide(); return;}
        if (data.code > 0 && data.sum > 0) {
          var list = '';
          $.each(data.data, function (i,n) {
              var recover_name = n.recover_name ? n.recover_name : '再生资源回收';
            list += '<li><a href="#" onclick="orderInfo('+ n.id +')" class="item-link item-content">';
            list += '<div class="item-media"><img src="/public/static/sui/img/icon_recover.svg" style="width: 2.2rem;"></div>';
            list += '<div class="item-inner"> <div class="item-title-row">';
            list += '<div class="item-title">' + recover_name + '</div>';
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
        if(!$(this).hasClass('active')) {
            $('#tab1 .media-list ul').html('');
            getOrderList(2, '#tab1');
        }
    });
    $('.order-tab2').on('click', function () {
        if(!$(this).hasClass('active')) {
            $('#tab2 .media-list ul').html('');
            getOrderList(1, '#tab2');
        }
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
  // 员工的回收单
  $(document).on("pageInit", "#user-order-list", function (e, id, page) {
    var orderListLoading = false;
    var getOrderList = function (status, id) {
      var limit = $('#order-list ' + id + ' li').length;
      console.log(limit);
      $.post('/index/order/userOrderList', {status:status, limit:limit}, function (data) {
        if(data.sum <= limit) {$(id + ' .infinite-scroll-preloader').hide(); return;}
        if (data.code > 0 && data.sum > 0) {
          var list = '';
          $.each(data.data, function (i,n) {
            var recover_name = n.recover_name ? n.recover_name : '再生资源回收';
            list += '<li><a href="#" onclick="orderInfo('+ n.id +')" class="item-link item-content">';
            list += '<div class="item-media"><img src="/public/static/sui/img/icon_recover.svg" style="width: 2.2rem;"></div>';
            list += '<div class="item-inner"> <div class="item-title-row">';
            list += '<div class="item-title">' + recover_name + '</div>';
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
        if(!$(this).hasClass('active')) {
            $('#tab1 .media-list ul').html('');
            getOrderList(2, '#tab1');
        }
    });
    $('.order-tab2').on('click', function () {
        if(!$(this).hasClass('active')) {
            $('#tab2 .media-list ul').html('');
            getOrderList(1, '#tab2');
        }
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

  // 订单详情部分内容拼接
  orderContent = function (task) {
      var task_type = task.task_type == 2 ? '现金交易' : '线上交易';
      var content = '';
      content += '<li> <div class="item-content"> <div class="item-inner">';
      content += '<div class="item-title label">支付方式：</div>';
      if(task.task_type) { content += '<div class="item-input">' + task_type + '</div>';}
      content += '</div> </div> </li>';
      content += '<li> <div class="item-content"> <div class="item-inner">';
      content += '<div class="item-title label">回收品类：</div>';
      if(task.recover_name) { content += '<div class="item-input">' + task.recover_name + '</div>';}
      content += '</div> </div> </li>';
      content += '<li> <div class="item-content"> <div class="item-inner">';
      content += '<div class="item-title label">回收单价：</div>';
      var task_price = task.task_price ? task.task_price : '';
      content += '<div class="item-input task_price">' + task_price + '</div>';
      content += '</div> </div> </li>';
      content += '<li> <div class="item-content"> <div class="item-inner">';
      content += '<div class="item-title label">回收数量：</div>';
      if(task.number) { content += '<div class="item-input">' + task.number + '</div>';}
      content += '</div> </div> </li>';
      content += '<li> <div class="item-content"> <div class="item-inner">';
      content += '<div class="item-title label">回收总价：</div>';
      if(task.task_money) { content += '<div class="item-input">' + task.task_money + '</div>';}
      content += '</div> </div> </li>';
      return content;
  };
  // 回收单详情
  $(document).on("pageInit", "#order-info", function (e, id, page) {
    var task_id = localStorage.task_id;
    var aid = localStorage.aid > 0 ? localStorage.aid : 0;
    $.post('/index/order/orderInfo', {task_id:task_id}, function (data) {
      if(data.code > 0) {
        var task = data.data;
        $('input[name="id"]').val(task_id);
        $('.task_date').text(task.task_date);
        $('.order_id').text(task.order_id);

        if(task.task_status == 2) {
          if(task.user_id > 0) {
            $('.recover_user').html(task.username +'(<a href="tel:' + task.mobile + '">' + task.user_mobile + '</a>)');
            if(task.task_money > 0) {
                $('.task_status').text('等待确认');
                var content = orderContent(task);
                content += '<a href="#" class="button button-fill button-big submit-order">如数据无误 请确认!</a>';
                $(page).find('ul').append(content);
                $('.submit-order').on('click', function () {
                    $.confirm('您确认数据无误吗？', function () {
                        $.post('/index/order/confirmOrder', {id:task_id}, function (data) {
                          $.toast(data.msg);
                            if(data.code) {
                                $('.task_status').text('已完成');
                                $('.submit-order').hide();
                                var content = '<li> <div class="item-content"> <div class="item-inner">';
                                content += '<div class="item-title label">完成时间：</div>';
                                var mydate = new Date();
                                mydate.setTime(data.data.end_time * 1000);
                                content += '<div class="item-input">' + mydate.format('yyyy-MM-dd h:m') + '</div>';
                                content += '</div> </div> </li>';
                                $(page).find('ul').append(content);
                            }
                        });
                    });
                });
            } else {
                $('.task_status').text('已派单');
            }
          } else {
            $('.task_status').text('等待派单');
            $('.recover_user').text('待定');
          }
        } else if(task.task_status == 1) {
          $('.task_status').text('已完成');
          $('.recover_user').html(task.username +'(<a href="tel:' + task.mobile + '">' + task.user_mobile + '</a>)');
          var content = orderContent(task);
          content += '<li> <div class="item-content"> <div class="item-inner">';
          content += '<div class="item-title label">完成时间：</div>';
          var mydate = new Date();
          mydate.setTime(task.end_time * 1000);
          content += '<div class="item-input">' + mydate.format('yyyy-MM-dd h:m') + '</div>';
          content += '</div> </div> </li>';
          $(page).find('ul').append(content);
        }
      }

    });
  });

  // 员工回收单详情
  $(document).on("pageInit", "#user-order-info", function (e, id, page) {
    var task_id = localStorage.task_id;

    $.post('/index/order/userOrderInfo', {task_id:task_id}, function (data) {
      if(data.code > 0) {
        var task = data.data;
        $('input[name="id"]').val(task_id);
        $('.task_date').text(task.task_date);
        $('.order_id').text(task.order_id);
        $('.member_name').text(task.member_name);
        $('.member_mobile').html('<a href="tel:' + task.member_mobile + '">' + task.member_mobile + '</a>');
        $('.task_city').text(task.task_city);
        $('.task_address').text(task.task_address);

        if(task.task_status == 2) {
          if(task.user_id > 0) {
            var user_content = '';
            if(task.task_money > 0) {
                $('.task_status').text('等待用户确认');
              var content = orderContent(task);
                $(page).find('ul').append(content);
            } else {
                $('.task_status').text('待服务');
                user_content += '<a href="#" class="button button-fill button-big popup-order">填写回收数据</a>';
                $(page).find('ul').append(user_content);
                // 回收数据填写
                $('.popup-order').on('click',function () {
                  var user_content = '';
                    user_content += '<li class="back-white"> <div class="item-content"> <div class="item-inner">';
                    user_content += '<div class="item-title label">支付方式：</div>';
                    user_content += '<div class="item-input"><select name="task_type" class="task_type"><option value="0">请选择支付方式</option>';
                    user_content += '<option value="1">线上交易</option><option value="2">现金交易</option>';
                    user_content += '</select></div></div> </div> </li>';
                    user_content += '<li class="back-white"> <div class="item-content"> <div class="item-inner">';
                    user_content += '<div class="item-title label">回收品类：</div>';
                    user_content += '<div class="item-input"><select name="recover_type" class="recover_type"><option value="0">请选择回收品类</option>';
                    if (data.price && data.price.length > 0) {
                        $.each(data.price, function (i, n) {
                            user_content += '<option value="' + n.id + '" data-price="' + n.price + '">' + n.name + '</option>';
                        });
                    }
                    user_content += '</select></div></div> </div> </li>';
                    user_content += '<li class="back-white"> <div class="item-content"> <div class="item-inner">';
                    user_content += '<div class="item-title label">回收单价：</div>';
                    var task_price = task.task_price ? task.task_price : '';
                    user_content += '<div class="item-input task_price">' + task_price + '</div>';
                    user_content += '<input type="hidden" name="task_price" value="' + task_price + '"/>';
                    user_content += '</div> </div> </li>';
                    user_content += '<li class="back-white"> <div class="item-content"> <div class="item-inner">';
                    user_content += '<div class="item-title label">回收数量：</div>';
                    var number = task.number ? task.number : '';
                    user_content += '<div class="item-input"><input type="number" name="number" oninput="sum(value)" placeholder="请输入数量" value="' + number + '" /></div>';
                    user_content += '</div> </div> </li>';
                    user_content += '<li class="back-white"> <div class="item-content"> <div class="item-inner">';
                    user_content += '<div class="item-title label">回收总价：</div>';
                    var task_money = task.task_money ? task.task_money : '';
                    user_content += '<div class="item-input task_money">' + task_money + '</div>';
                    user_content += '<input type="hidden" name="task_money" value="' + task_money + '"/>';
                    user_content += '</div> </div> </li>';
                    var popupHTML = '<div class="popup">'+
                        '<header class="bar bar-nav"> ' +
                        '<a class="button button-link button-nav pull-left close-popup" href="javascript:;">' +
                        '<span class="icon icon-left"></span>返回</a>' +
                        '<h1 class="title">回收数据</h1>' +
                        '</header>'+
                        '<div class="content" id="info"> <div class="list-block">' +
                        '<ul><form class="edit-order">' +
                        '<input type="hidden" name="id" value="'+ task_id +'" />'+
                        user_content +
                        '<a href="#" class="button button-fill button-big submit-order">提交</a>'+
                        '</form></ul> </div> </div>'+
                        '</div>';
                    $.popup(popupHTML);
                    // 支付类型选择触发
                    $('.task_type').change(function () {
                        var price = $('input[name="task_price"]').val();
                        var num = $('input[name="number"]').val();
                        if(num > 0 && price > 0) {
                            var sum = ((price * 100) * (num * 100)) / 10000;
                            var task_money = toDecimal2(sum);
                            $('.task_money').text(task_money);
                            $('input[name="task_money"]').val(task_money);
                        }
                    });
                    // 回收类型选择触发
                    $('.recover_type').change(function () {
                        var id = $(this).val();
                        var price = $(this).find('option[value="'+id+'"]').data('price');
                        $('.task_price').text(price);
                        $('input[name="task_price"]').val(price);
                        var num = $('input[name="number"]').val();
                        if(num > 0) {
                            var sum = ((price * 100) * (num * 100)) / 10000;
                            var task_money = toDecimal2(sum);
                            $('.task_money').text(task_money);
                            $('input[name="task_money"]').val(task_money);
                        }
                    });
                    //计算总价
                    sum = function (val) {
                        var task_price = $('input[name="task_price"]').val();
                        var task_type = $('.task_type').val();
                        if(task_type == 0) {$.toast('请选择支付方式！'); return false;}
                        if(task_price > 0) {
                            var sum = ((task_price * 100) * (val * 100)) / 10000;
                            var task_money = toDecimal2(sum);
                            $('.task_money').text(task_money);
                            $('input[name="task_money"]').val(task_money);
                        } else {
                            $.toast('请选择回收品类！');return false;
                        }
                    };
                    //提交订单数据
                    $('.submit-order').on('click',function () {
                        var id = $('input[name="id"]').val();
                        var task_type = $('.task_type').val();
                        var recover_type = $('.recover_type').val();
                        var task_price = $('input[name="task_price"]').val();
                        var number = $('input[name="number"]').val();
                        var task_money = $('input[name="task_money"]').val();
                        if(task_type == 0) {$.toast('请选择支付方式！'); return false;}
                        if(recover_type == 0) {$.toast('请选择回收品类！'); return false;}
                        if(number == 0) {$.toast('请输入数量！'); return false;}
                        if(id > 0 && task_type > 0 && recover_type > 0 && task_price > 0 && number > 0 && task_money > 0) {
                            $.post('/index/order/editOrder', {
                                id:id,task_type:task_type,recover_type:recover_type,task_price:task_price,number:number,task_money:task_money
                            }, function (data) {
                                $('.task_status').text('等待用户确认');
                                $.closeModal('.popup');
                                if(data.code == 1) {
                                  var content = orderContent(data.data);
                                    $(page).find('ul').append(content);
                                    $('.popup-order').hide();
                                }
                                $.toast(data.msg);
                            });
                        } else {
                            $.toast('数据错误!请重新填写!');return false;
                        }
                    });
                });
            }

          } else {
            $('.task_status').text('等待派单');
          }
        } else if(task.task_status == 1) {
          $('.task_status').text('已完成');
          var content = orderContent(task);
          content += '<li> <div class="item-content"> <div class="item-inner">';
          content += '<div class="item-title label">完成时间：</div>';
          var mydate = new Date();
          mydate.setTime(task.end_time * 1000);
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
    var money = toDecimal2(memberInfo.yue);
    $('.money').text('余额为：￥' + money);
    $('.open-transfer').on('click', function () {
      $.popup('.popup-transfer');
        checkMoney = function (i) {
            if(i <= 0) {
                $.toast('请输入合法数值');
                $("input[name='withdraw']").val('');
            }
            if((i-money) > 0) {
                $.toast('最多可提现' + money);
                $("input[name='withdraw']").val(money);
            }
            var arr = i.split('.');
            if(arr[1] && arr[1].length >2) {
                $.toast('最多至小数后两位');
                $("input[name='withdraw']").val(Math.floor(i*100)/100);
            }
        };
    });
  });

  /*提现*/
  $(document).on("pageInit", "#withdraw", function (e, id, page) {
    var memberInfo = JSON.parse(localStorage.member_info);
    var money = toDecimal2(memberInfo.yue);
    //var money = memberInfo.yue.toFixed(2);
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
            localStorage.member_info = task.data;
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
            $.toast(data.msg);
            if(data.code > 0) {
              localStorage.member_info = data.data;
              memberInfo = JSON.parse(data.data);
              $('.money').text('可提现余额为：￥' + toDecimal2(memberInfo.yue));
              //$('.money').text('可提现余额为：￥' + memberInfo.yue.toFixed(2));
              $.router.back()
            }
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
    var integral = memberInfo.integral ? memberInfo.integral : 0;
    $('.integral').text('可使用积分为：' + integral);
  });

  /*我的统计*/
  $(document).on("pageInit", "#count", function (e, id, page) {
    var memberInfo = JSON.parse(localStorage.member_info);
    var sum_money = Number(memberInfo.yue) + Number(memberInfo.money);
    //var sum_money = (memberInfo.yue + memberInfo.money).toFixed(2);
    $('.sum_money').text('￥' + toDecimal2(sum_money));
    $('.use_money').text('￥' + toDecimal2(memberInfo.money));
    //$('.use_money').text('￥' + memberInfo.money.toFixed(2));
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
            list += '<div class="item-after">-' + toDecimal2(n.money) + '</div>';
            //list += '<div class="item-after">-' + n.money.toFixed(2) + '</div>';
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
        $('.withdraw_money').text(toDecimal2(data.money));//金额
        //$('.withdraw_money').text(data.money.toFixed(2));//金额
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
  /*收支明细*/
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
            list += money + toDecimal2(n.money) + '</div>';
            //list += money + n.money.toFixed(2) + '</div>';
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
    if(/Android [4-7]/.test(navigator.appVersion)) {
        window.addEventListener("resize", function() {
            if(document.activeElement.tagName=="INPUT" || document.activeElement.tagName=="TEXTAREA") {
                window.setTimeout(function() {
                    document.activeElement.scrollIntoViewIfNeeded();
                },0);
            }
        })
    }
  $.init();
});
