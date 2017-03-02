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
  var memberInfo;
  var infoIndex = function () {
    if (localStorage.member_id) {
        $.post('/index/index/autoLogin', {member_id: localStorage.member_id}, function (data) {
            if (data.code > 0) {
                localStorage.member_id = data.member_id;
                localStorage.member_info = data.member_info;
                initMember(localStorage.member_info);
                localStorage.login_url = '/index/user/index';
            }
        });
    } else {

    }
  };
      infoIndex();
  // 用户信息初始化
  var initMember = function (member) {
      memberInfo = member;
  };
  // 用户首页
  $(document).on("pageInit", "#user-index", function (e, id, page) {

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
                    initMember(localStorage.member_info);
                    //$.closeModal('.login-screen');
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
                          initMember(localStorage.member_info);
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
        //var reg = /^(?=.{6,16}$)(?![0-9]+$)(?!.*(.).*\1)[0-9a-zA-Z]+$/;
        /*var reg = /[a-zA-Z0-9]{6,16}/;
         if(reg.test(password)) {
         return true;
         } else {
         $.alert('密码格式有误，请重新输入!');
         return false;
         }*/
    }

  $(document).on("pageInit", "#price", function (e, id, page) {

  });
  $(document).on("pageInit", "#recover", function (e, id, page) {
      if(!localStorage.address_city) {
          $('.address-show').hide();
          $('.address-hide').show();
          var arr = '四川 南充 高坪区'.split(' ');
          var input = '';
      } else {
          var arr = localStorage.address_city.split(' ');
          var input = localStorage.address_input;
          $('.address-hide').hide();
          $('.address_city').text(localStorage.address_city);
          $('.address_input').text(localStorage.address_input);
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
          if(address_city == '') {$.alert('请选择省市区');}
          if(address_input.length > 8) {
              $('.address-show').show();
              $('.address-hide').hide();
              $('.address_city').text(address_city);
              $('.address_input').text(address_input);
              localStorage.address_city = address_city;
              localStorage.address_input = address_input;
              $('.close-popup').click();
          } else {
              $.alert('请精确到门牌号!8字以上');
          }
      });
  });


  //下拉刷新页面
  $(document).on("pageInit", "#page-ptr", function(e, id, page) {
    var $content = $(page).find(".content").on('refresh', function(e) {
      // 模拟2s的加载过程
      setTimeout(function() {
        var cardHTML = '<div class="card">' +
          '<div class="card-header">标题</div>' +
          '<div class="card-content">' +
          '<div class="card-content-inner">内容内容内容内容内容内容内容内容内容内容内容内容内容内容内容内容内容内容内容内容内容内容' +
          '</div>' +
          '</div>' +
          '</div>';

        $content.find('.card-container').prepend(cardHTML);
        // $(window).scrollTop(0);
        // 加载完毕需要重置
        $.pullToRefreshDone($content);
      }, 2000);
    });
  });

  //无限滚动
  $(document).on("pageInit", "#page-infinite-scroll-bottom", function(e, id, page) {
    var loading = false;
    // 每次加载添加多少条目
    var itemsPerLoad = 20;
    // 最多可加载的条目
    var maxItems = 100;
    var lastIndex = $('.list-container li').length;
    function addItems(number, lastIndex) {
      // 生成新条目的HTML
      var html = '';
      for (var i = lastIndex + 1; i <= lastIndex + number; i++) {
        html += '<li class="item-content"><div class="item-inner"><div class="item-title">新条目</div></div></li>';
      }
      // 添加新条目
      $('.infinite-scroll .list-container').append(html);
    }
    $(page).on('infinite', function() {
      // 如果正在加载，则退出
      if (loading) return;
      // 设置flag
      loading = true;
      // 模拟1s的加载过程
      setTimeout(function() {
        // 重置加载flag
        loading = false;
        if (lastIndex >= maxItems) {
          // 加载完毕，则注销无限加载事件，以防不必要的加载
          $.detachInfiniteScroll($('.infinite-scroll'));
          // 删除加载提示符
          $('.infinite-scroll-preloader').remove();
          return;
        }
        addItems(itemsPerLoad,lastIndex);
        // 更新最后加载的序号
        lastIndex = $('.list-container li').length;
        $.refreshScroller();
      }, 1000);
    });
  });

  //顶部无限滚动
  $(document).on("pageInit", "#page-infinite-scroll-top", function(e, id, page) {
    function addItems(number, lastIndex) {
      // 生成新条目的HTML
      var html = '';
      for (var i = lastIndex+ number; i > lastIndex ; i--) {
        html += '<li class="item-content"><div class="item-inner"><div class="item-title">条目'+i+'</div></div></li>';
      }
      // 添加新条目
      $('.infinite-scroll .list-container').prepend(html);

    }
    var timer = false;
    $(page).on('infinite', function() {
       var lastIndex = $('.list-block li').length;
       var lastLi = $(".list-container li")[0];
       var scroller = $('.infinite-scroll-top');
       var scrollHeight = scroller[0].scrollHeight; // 获取当前滚动元素的高度
      // 如果正在加载，则退出
      if (timer) {
        clearTimeout(timer);
      }

      // 模拟1s的加载过程
      timer = setTimeout(function() {

        addItems(20,lastIndex);

        $.refreshScroller();
        //  lastLi.scrollIntoView({
        //     behavior: "smooth",
        //     block:    "start"
        // });
        // 将滚动条的位置设置为最新滚动元素高度和之前的高度差
        scroller.scrollTop(scroller[0].scrollHeight - scrollHeight);
      }, 1000);
    });

  });
  //test demo js

  //多个标签页下的无限滚动
  $(document).on("pageInit", "#page-fixed-tab-infinite-scroll", function(e, id, page) {
    var loading = false;
    // 每次加载添加多少条目
    var itemsPerLoad = 20;
    // 最多可加载的条目
    var maxItems = 100;
    var lastIndex = $('.list-container li')[0].length;
    function addItems(number, lastIndex) {
      // 生成新条目的HTML
      var html = '';
      for (var i = lastIndex + 1; i <= lastIndex + number; i++) {
        html += '<li class="item-content""><div class="item-inner"><div class="item-title">新条目</div></div></li>';
      }
      // 添加新条目
      $('.infinite-scroll.active .list-container').append(html);
    }
    $(page).on('infinite', function() {
      // 如果正在加载，则退出
      if (loading) return;
      // 设置flag
      loading = true;
      var tabIndex = 0;
      if($(this).find('.infinite-scroll.active').attr('id') == "tab2"){
        tabIndex = 0;
      }
      if($(this).find('.infinite-scroll.active').attr('id') == "tab3"){
        tabIndex = 1;
      }
      lastIndex = $('.list-container').eq(tabIndex).find('li').length;
      // 模拟1s的加载过程
      setTimeout(function() {
        // 重置加载flag
        loading = false;
        if (lastIndex >= maxItems) {
          // 加载完毕，则注销无限加载事件，以防不必要的加载
          //$.detachInfiniteScroll($('.infinite-scroll').eq(tabIndex));
          // 删除加载提示符
          $('.infinite-scroll-preloader').eq(tabIndex).hide();
          return;
        }
        addItems(itemsPerLoad,lastIndex);
        // 更新最后加载的序号
        lastIndex =  $('.list-container').eq(tabIndex).find('li').length;
        $.refreshScroller();
      }, 1000);
    });
  });

  //图片浏览器
  $(document).on("pageInit", "#page-photo-browser", function(e, id, page) {
    var myPhotoBrowserStandalone = $.photoBrowser({
      photos : [
        '//img.alicdn.com/tps/i3/TB1kt4wHVXXXXb_XVXX0HY8HXXX-1024-1024.jpeg',
        '//img.alicdn.com/tps/i1/TB1SKhUHVXXXXb7XXXX0HY8HXXX-1024-1024.jpeg',
        '//img.alicdn.com/tps/i4/TB1AdxNHVXXXXasXpXX0HY8HXXX-1024-1024.jpeg',
      ]
    });
    //点击时打开图片浏览器
    $(page).on('click','.pb-standalone',function () {
      myPhotoBrowserStandalone.open();
    });
    /*=== Popup ===*/
    var myPhotoBrowserPopup = $.photoBrowser({
      photos : [
        '//img.alicdn.com/tps/i3/TB1kt4wHVXXXXb_XVXX0HY8HXXX-1024-1024.jpeg',
        '//img.alicdn.com/tps/i1/TB1SKhUHVXXXXb7XXXX0HY8HXXX-1024-1024.jpeg',
        '//img.alicdn.com/tps/i4/TB1AdxNHVXXXXasXpXX0HY8HXXX-1024-1024.jpeg',
      ],
      type: 'popup'
    });
    $(page).on('click','.pb-popup',function () {
      myPhotoBrowserPopup.open();
    });
    /*=== 有标题 ===*/
    var myPhotoBrowserCaptions = $.photoBrowser({
      photos : [
        {
          url: '//img.alicdn.com/tps/i3/TB1kt4wHVXXXXb_XVXX0HY8HXXX-1024-1024.jpeg',
          caption: 'Caption 1 Text'
        },
        {
          url: '//img.alicdn.com/tps/i1/TB1SKhUHVXXXXb7XXXX0HY8HXXX-1024-1024.jpeg',
          caption: 'Second Caption Text'
        },
        // 这个没有标题
        {
          url: '//img.alicdn.com/tps/i4/TB1AdxNHVXXXXasXpXX0HY8HXXX-1024-1024.jpeg',
        },
      ],
      theme: 'dark',
      type: 'standalone'
    });
    $(page).on('click','.pb-standalone-captions',function () {
      myPhotoBrowserCaptions.open();
    });
  });


  //对话框
  $(document).on("pageInit", "#page-modal", function(e, id, page) {
    var $content = $(page).find('.content');
    $content.on('click','.alert-text',function () {
      $.alert('这是一段提示消息');
    });

    $content.on('click','.alert-text-title', function () {
      $.alert('这是一段提示消息', '这是自定义的标题!');
    });

    $content.on('click', '.alert-text-title-callback',function () {
      $.alert('这是自定义的文案', '这是自定义的标题!', function () {
        $.alert('你点击了确定按钮!')
      });
    });
    $content.on('click','.confirm-ok', function () {
      $.confirm('你确定吗?', function () {
        $.alert('你点击了确定按钮!');
      });
    });
    $content.on('click','.prompt-ok', function () {
      $.prompt('你叫什么问题?', function (value) {
        $.alert('你输入的名字是"' + value + '"');
      });
    });
  });

  //操作表
  $(document).on("pageInit", "#page-action", function(e, id, page) {
    $(page).on('click','.create-actions', function () {
      var buttons1 = [
        {
          text: '请选择',
          label: true
        },
        {
          text: '卖出',
          bold: true,
          color: 'danger',
          onClick: function() {
            $.alert("你选择了“卖出“");
          }
        },
        {
          text: '买入',
          onClick: function() {
            $.alert("你选择了“买入“");
          }
        }
      ];
      var buttons2 = [
        {
          text: '取消',
          bg: 'danger'
        }
      ];
      var groups = [buttons1, buttons2];
      $.actions(groups);
    });
  });

  //加载提示符
  $(document).on("pageInit", "#page-preloader", function(e, id, page) {
    $(page).on('click','.open-preloader-title', function () {
      $.showPreloader('加载中...')
      setTimeout(function () {
        $.hidePreloader();
      }, 2000);
    });
    $(page).on('click','.open-indicator', function () {
      $.showIndicator();
      setTimeout(function () {
        $.hideIndicator();
      }, 2000);
    });
  });


  //选择颜色主题
  $(document).on("click", ".select-color", function(e) {
    var b = $(e.target);
    document.body.className = "theme-" + (b.data("color") || "");
    b.parent().find(".active").removeClass("active");
    b.addClass("active");
  });

  //picker
  $(document).on("pageInit", "#page-picker", function(e, id, page) {
    $("#picker").picker({
      toolbarTemplate: '<header class="bar bar-nav">\
        <button class="button button-link pull-left">\
      按钮\
      </button>\
      <button class="button button-link pull-right close-picker">\
      确定\
      </button>\
      <h1 class="title">标题</h1>\
      </header>',
      cols: [
        {
          textAlign: 'center',
          values: ['iPhone 4', 'iPhone 4S', 'iPhone 5', 'iPhone 5S', 'iPhone 6', 'iPhone 6 Plus', 'iPad 2', 'iPad Retina', 'iPad Air', 'iPad mini', 'iPad mini 2', 'iPad mini 3'],
          cssClass: 'picker-items-col-normal'
        }
      ]
    });
    $("#picker-name").picker({
      toolbarTemplate: '<header class="bar bar-nav">\
      <button class="button button-link pull-right close-picker">确定</button>\
      <h1 class="title">请选择称呼</h1>\
      </header>',
      cols: [
        {
          textAlign: 'center',
          values: ['赵', '钱', '孙', '李', '周', '吴', '郑', '王']
        },
        {
          textAlign: 'center',
          values: ['杰伦', '磊', '明', '小鹏', '燕姿', '菲菲', 'Baby']
        },
        {
          textAlign: 'center',
          values: ['先生', '小姐']
        }
      ]
    });
  });
  $(document).on("pageInit", "#page-datetime-picker", function(e) {
    $("#datetime-picker").datetimePicker({
      toolbarTemplate: '<header class="bar bar-nav">\
      <button class="button button-link pull-right close-picker">确定</button>\
      <h1 class="title">选择日期和时间</h1>\
      </header>'
    });
  });

  $(document).on("pageInit", "#page-city-picker", function(e) {
    $("#city-picker").cityPicker({
        value: ['天津', '河东区']
        //value: ['四川', '内江', '东兴区']
    });
  });

  $.init();
});
