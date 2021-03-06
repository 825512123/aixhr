/**
 * Created by Administrator on 2017/4/4.
 */

$(function () {
  //路由跳转
  router = function (url) {
    location.href = url;
  };
  pageBack = function (t) {
    t = t*1000;
    setTimeout("history.back()", t);
  };
  ajax_status = function (url,id) {
    $.post(url, {id:id}, function (data) {
      if(data.code) {
        $('#id_'+id).remove();
        successModal();
      } else {
        errorModal();
      }
    },'json');
  };
  //图片上传初始化
  var uploadImg = function (url) {
    $("#file-Portrait").fileinput({
      showUpload : false,
      language: 'zh',
      uploadAsync: true, //设置上传同步异步 此为同步
      uploadUrl: url, //上传后台操作的方法
      maxFileCount: 1,
      maxFileSize: 200,
      allowedPreviewTypes: ['image'],
      allowedFileExtensions: ['jpg', 'png', 'bmp', 'jpeg', 'pbm', 'gif'] //限制上传文件后缀
    }).on('filebatchselected', function (event, files) {
      $(this).fileinput("upload");
    }).on("fileuploaded", function(event, data) {
      if(data.response.code)
      {
        $('input[name="url"]').val(data.response.data);
        $('.btn-file').hide();
        $('.fileinput-remove').on('click', function () {
          $('.btn-file').show();
        });
      }
    }).on('filesuccessremove', function(event, id) {
      $('.btn-file').show();
    });//初始化 后 上传插件的样子
  };
  //判断表单数组
  var checkFromArray = function (arr) {
    $.each(arr, function (i,n) {
      if(!n.value) {
        console.log(n.name);
        return false;
      }
    });
    return true;
  };
  //提示框
  var alertModal = function (msg, time) {
    $('#alertModal .box .box-body').html(msg);
    $('#alertModal').modal('show');
    if(!time) {time = 3000}
    setTimeout("$('#alertModal').modal('hide')", time);
  };
  //成功提示框
  var successModal = function (msg, time) {
    if(msg) {$('#successModal h4').text(msg);}
    $('#successModal').modal('show');
    if(time) {setTimeout("$('#successModal').modal('hide')", time);}
  };
  //失败提示框
  var errorModal = function (msg, time) {
    if(msg) {$('#errorModal h4').text(msg);}
    $('#errorModal').modal('show');
    if(time) {setTimeout("$('#errorModal').modal('hide')", time);}
  };


  //编辑/新增员工
  //Datemask dd/mm/yyyy
  $("#datemask").inputmask("dd/mm/yyyy", {"placeholder": "dd/mm/yyyy"});
  //Datemask2 mm/dd/yyyy
  $("#datemask2").inputmask("mm/dd/yyyy", {"placeholder": "mm/dd/yyyy"});
  //Money Euro
  $("[data-mask]").inputmask();


  //新增企业初始化图片上传
  uploadImg('/admin/file/uploadThumb');
  //新增企业提交
  $('.admin-add').on('click', function () {
    var from = $('#admin-add').serialize();
    var arr = $('#admin-add').serializeArray();
    if(!checkFromArray(arr)) { alertModal('请填写完整信息！'); return false;}
    $.post('/admin/admin/add', from, function (data) {
      if(data.code) {
        successModal();
        setTimeout("router('/admin/admin')",10000);
      } else {
        errorModal();
      }
    },'json');
  });

  //编辑角色提交
  $('.role-edit').on('click', function () {
    var from = $('#role-edit').serialize();
    var roleName =  $('input[name="rolename"]').val().trim();
    if(!roleName) { alertModal('请填写角色名称！'); return false;}
    $.post('/admin/role/edit', from, function (data) {
      if(data.code) {
        successModal();
        setTimeout("router('/admin/role/index')", 1000);
      } else {
        errorModal();
      }
    },'json');
  });

  //编辑节点提交
  $('.node-edit').on('click', function () {
    var from = $('#node-edit').serialize();
    var arr = $('#node-edit').serializeArray();
    if(!checkFromArray(arr)) { alertModal('请填写完整信息！'); return false;}
    $.post('/admin/role/node_edit', from, function (data) {
      if(data.code) {
        successModal();
        setTimeout("router('/admin/role/node')", 1000);
      } else {
        errorModal();
      }
    },'json');
  });
  // 编辑类型价格提交
  $('.price-add').on('click', function () {
    var from = $('#price-add').serialize();
    var name = $('input[name="name"]').val().trim();
    var price = $('input[name="price"]').val().trim();
    if(!name) { alertModal('请填写类型名称！'); return false;}
    if(!name) { alertModal('请填写类型价格！'); return false;}
    $.post('/admin/admin/price_add', from, function (data) {
      if(data.code) {
        successModal();
        pageBack(1.5);
      } else {
        errorModal();
      }
    },'json');
  });


  var change = function (obj) {
    if(obj.hasClass('btn-danger')) {
      obj.attr('data-url','/admin/role/up');
      obj.parent().prev('td').text('禁用');
      obj.text('启用');
    } else {
      obj.attr('data-url','/admin/role/down');
      obj.parent().prev('td').text('正常');
      obj.text('禁用');
    }
    obj.toggleClass('btn-danger');
    obj.toggleClass('btn-info');
  };
  // 角色禁用
  $('#role-index .btn-danger').on('click', function () {
    var obj = $(this);
    var url = obj.hasClass('btn-danger') ? obj.data('url') : '/admin/role/up';
    var id = obj.data('id');
    console.log(url);
    $.post(url, {id:id}, function (data) {
      if(data.code) {
        change(obj);
        successModal();
      } else {
        errorModal();
      }
    },'json');
  });
  // 角色启用
  $('#role-index .btn-info').on('click', function () {
    var obj = $(this);
    var url = obj.hasClass('btn-info') ? obj.data('url') : '/admin/role/down';
    var id = obj.data('id');

    $.post(url, {id:id}, function (data) {
      if(data.code) {
        change(obj);
        successModal();
      } else {
        errorModal();
      }
    },'json');
  });
  // 列表删除
  $('.ajax-delete').on('click', function () {
    var obj = $(this);
    var url = obj.data('url');
    var id = obj.data('id');

    $.post(url, {id:id}, function (data) {
      if(data.code) {
        obj.parent().parent().remove();
        successModal();
      } else {
        errorModal();
      }
    },'json');
  });

  // 退出登录
  loginOut = function () {
    $.post('/admin/index/loginOut', {id:1}, function (data) {
      if (data.code > 0) {
        successModal(data.msg);
        setTimeout("router('/admin/login')", 1000);
      }
    });
  };

  // 授权页面
  $('.access').on('click', function () {
    var from = $('#access').serialize();
    $.post('/admin/role/access', from, function (data) {
      if(data.code) {
        successModal();
        setTimeout("router('/admin/role/')", 1000);
      } else {
        errorModal();
      }
    },'json');
  });

  // 订单管理-订单分配/资金管理-提现确认
  send_task = function (id) {
    $('#send-task').find('input[name="id"]').val(id);
    $('#sendModal').modal('show');
  };
  $('.send-task').on('click', function () {
    var from = $('#send-task').serialize();
    var id = $('#send-task').find('input[name="id"]').val();
    $.post('/admin/task/sendTask', from, function (data) {
      if(data.code) {
        //$('#id_'+id).find('.btn-success').remove();
        successModal();
        setTimeout("router('/admin/task/')", 1000);
      } else {
        errorModal();
      }
    },'json');
  });
  // 资金管理-提现确认
  $('.confirm-withdraw').on('click', function () {
    var receipt_id = $("input[name='receipt_id']").val().trim();
    var id = $('#send-task').find('input[name="id"]').val();
    $.post('/admin/funds/confirmWithdraw', {id:id,receipt_id:receipt_id,status:1}, function (data) {
      if(data.code) {
        successModal();
        setTimeout("router('/admin/funds/withdraw')", 1000);
      } else {
        errorModal();
      }
    },'json');
  });
  // 取消提现
  cancel_withdraw = function (id) {
      $.post('/admin/funds/cancelWithdraw', {id:id,status:0}, function (data) {
          if(data.code) {
              successModal();
              setTimeout("router('/admin/funds/withdraw')", 1000);
          } else {
              errorModal();
          }
      },'json');
  };

  // 订单管理-订单取消
  cancel_task = function (id) {
    $('#cancel-task').find('input[name="id"]').val(id);
    $('#cancelModal').modal('show');
  };
  $('.cancel-task').on('click', function () {
    var from = $('#send-task').serialize();
    $.post('/admin/task/sendTask', from, function (data) {
      if(data.code) {
        successModal();
        setTimeout("router('/admin/role/')", 1000);
      } else {
        errorModal();
      }
    },'json');
  });
});
