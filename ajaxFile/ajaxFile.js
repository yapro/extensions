/*
функция ajaxFile обрабатывает INPUT поля с объявленным CSS классом, например ajaxFile, под адреса Ajax-файлов (С) Лебеденко Н.Н. / Lebnik 2011
пример запуска:
$.getScript("http://"+window.location.host+"/res/img/jquery/ajaxFile.js", function(){
	ajaxFile(".ajaxFile", "http://"+window.location.host+"/uploader/files.php");
});
*/

function ajaxFile(e, address, HTML5Multiple){

  $(e).each(function(){

    var address_split = address.split("?");
    var separate = address_split[1]? "&" : "?";
    var ajaxAddress = address + separate;

    var iframeName = 'id_'+Math.round(Math.random() * 1000000000);

    var mouseoverStopTD = mouseleaveStopTD = false;// переменные для правильной обработки подстановки невидимой формы выбра файла

    var input = $(this).css("width","100%").attr("title","Двойной клик - просмотр загруженного");

    var table = $('<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td width="20">&nbsp;</td><td></td></tr></table>');

    $(this).css("visibility","hidden").after(table);
    $("TD:last", table).append(this);

    var td = $("TD:first", table).css("cursor","pointer").data("stop", false);

    var ajaxFileWrapper = function(td, input, response){

      input[0].style.visibility = "visible";

      if(response){ $(input[0]).val( response ); }

      $(input).unbind("click").unbind("dblclick").click(function(){

        this.select();

      }).dblclick(function(){

        window.open( "http://"+window.location.host + this.value );

      });

      $(td).data("stop", true).css("background","url(/res/img/jquery/del.gif) no-repeat 2px 2px").unbind("click").click(function(){
        // добавляем изображение символизирующее возможность Добавить
        $(this).data("stop", false);
        input[0].value = "";
        input[0].style.visibility = "hidden";
        $(this).css("background","url(/res/img/jquery/add.gif) no-repeat 5px 5px").trigger("mouseover");
        return false;

      });
    };

    var info = $(this).attr("info");

    var info = $('<input type="hidden" name="info" value="'+ ( (typeof info !== 'undefined' && info !== false)? info : "" ) +'">');

    var file = $('<input type="file" name="file' + (HTML5Multiple? "s[]" : "") + '" style="cursor:pointer" title="Выбрать файл для загрузки на сайт">');

    // гет-переменную ajaxFile назначаем чтобы: 1. не кешировался сабмит, 2. чтобы идентифицировать ajax загрузку файла
    var form = $('<form method="post" enctype="multipart/form-data" action="'+ajaxAddress+'ajaxFile='+iframeName+'" style="position: absolute; left: -555px; z-index: 12345" target="'+iframeName+'" class="ajaxFileForm"></form>');

    var iframe = $('<iframe name="'+iframeName+'" src="'+ajaxAddress+'blank=1" style="display:none"></iframe>');

    $(document.body).prepend( form.prepend( info ).prepend( file ) ).prepend( iframe );

    // обрабатываем td у которого поле с классом ajaxFile
    $(td).mouseover(function(){

      console.log( $(this).data("stop") );

      if( $(this).data("stop") == true ){ return false; }

      $(form).css("display","block");// сначала показываем форму (чтобы правильно определить offsetWidth)

      var position = $(this).offset();

      $(form).css({
        top: position.top+'px',
        left: (position.left - file[0].offsetWidth + 20)+'px'
      });

    });

    // обрабатываем файловое поле
    $(file).css("opacity","0").change(function(){

      $(td).css("background","url(/res/img/jquery/loading.gif) no-repeat 2px 2px");

      form.submit();

    }).mouseover(function(){

        $(td).data("stop", true);

    }).mouseleave(function(){

      $(form).css("left","-555px");

        $(td).data("stop", true);

      setTimeout(function(){

        $(td).data("stop", false);

      }, 123);

    });

    // обрабатываем iframe-загрузку
    iframe.unbind().load(function(){

      $(form).css("display","none");

      var response = $(this.contentWindow.document.body).html();// информация о завершении загрузки файла

      if(response!=""){

        $(td).data("stop", false).css("background","url(/res/img/jquery/add.gif) no-repeat 5px 5px");

        var r = $.parseJSON(response);

        if( r && r.filePath && r.filePath != "" && r.filePath.substr(0,1) == "/"){// если файл загружен

          ajaxFileWrapper(td, input, r.filePath);

        }else if( r && r.messageError && r.messageError != "" ){// если файл загружен с ошибкой

          jAlert(r.messageError);

        }else{

          jAlert("Возможно Вы попытались загрузить неразрешенный тип файла.<br>" +
            "Если Вы считаете что это не так, то наверняка возникла<br>" +
            "ошибка программного характера, пожалуйста сообщите нам об этом");

        }

      }else{// всегда срабатывает при создании iframe

      }
    });

    if( $(input).val() != "" ){// если изображение уже загружено в базу

      ajaxFileWrapper(td, input);

    }else{

      $(td).css("background","url(/res/img/jquery/add.gif) no-repeat 5px 5px");

    }
  });
};
