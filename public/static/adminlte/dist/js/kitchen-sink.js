/**
 * Created by Administrator on 2017/4/4.
 */

$(function () {
  //路由跳转
  router = function (url) {
    location.href = url;
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
  var checkFromArray = function (arr) {
    $.each(arr, function (i,n) {
      if(!n.value) {
        console.log(n.name);
        return false;
      }
    });
    return true;
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
    if(!checkFromArray(arr)) { alert('请填写完整信息！'); return false;}
    $.post('/admin/admin/add', from, function (data) {
      if(data.code) {
        router('/admin/admin');
      }
      alert(data.msg);
    },'json');
  });

});
