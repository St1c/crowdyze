$(function(){if(window.history.replaceState){l=window.location.toString();uri=l.indexOf("_fid=");if(uri!=-1){uri=l.substr(0,uri)+l.substr(uri+10);if(uri.substr(uri.length-1)=="?"||uri.substr(uri.length-1)=="&")uri=uri.substr(0,uri.length-1);window.history.replaceState("",document.title,uri)}}$.nette.ext("SuccessfullFlashHide",{load:function(){setTimeout(function(){$(".alert-success").fadeOut(3500)},1e3)}});$.nette.ext("tagsmanager",{load:function(){var e=$(".tm-input").tagsManager({});$(".tm-input").typeahead({name:"countries",prefetch:"/homepage/countries"}).on("typeahead:selected",function(t,n){e.tagsManager("pushTag",n.value)})}});$.nette.ext("masonry",{load:function(){$("#container").masonry({columnWidth:".item",gutter:30,itemSelector:".item"})}});$.nette.ext("ias",{load:function(){jQuery.ias({container:"#container",item:".item",pagination:".paginator",next:".next",trigger:"Show more",loader:'<img src="/images/loader.gif"/>',triggerPageThreshold:2,onLoadItems:function(e){var t=$(e).show().css({opacity:0});t.imagesLoaded(function(){t.animate({opacity:1});$("#container").masonry("appended",t,!0)});return!0}})}});$.nette.ext("jeditable",{load:function(){$(".editable").editable(function(e,t){var n=$(this);$.nette.ajax({url:n.data("handle"),data:{elementId:n.attr("id"),elementValue:e}});return e},{style:"inherit"})}});$.nette.ext("datepicker",{load:function(){$("input.deadline").datepicker()}});$.nette.ext("init").linkSelector="a.ajax";$.nette.init();$("input[type='file']").on("change",function(){$("input[name='upload[]']").each(function(){var e=$(this).val().split("/").pop().split("\\").pop();$(this).before("<p>"+e+"</p>")})})});