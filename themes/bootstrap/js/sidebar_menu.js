$("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
    });
     $("#menu-toggle-2").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled-2");
        $('#menu ul').hide();
    });

     function initMenu() {
      $('#menu ul').hide();
      $('#menu ul').children('.current').parent().show();


      //$('#menu ul:first').show();
      $('#menu li a').click(
        function() {
          var checkElement = $(this).next();
          if((checkElement.is('ul')) && (checkElement.is(':visible'))) {
            //handle second click to collapse menu
            $(this).find('.fa-folder-open').addClass('fa-folder').removeClass('fa-folder-open');

            $('#menu ul:visible').slideDown('normal');

            checkElement.slideUp('normal');
            return false;
            }
          if((checkElement.is('ul')) && (!checkElement.is(':visible'))) {
            //handles first click to expand menu
            $(this).find('.fa-folder').addClass('fa-folder-open').removeClass('fa-folder');

            //collapse any open folder
            $('#menu ul:visible').slideUp('normal');

            //for any folder previously open, change the icon to normal
            $('#menu ul:visible').each(function() {
              $(this).parents('li').find('.fa-folder-open').addClass('fa-folder').removeClass('fa-folder-open');
            });

            checkElement.slideDown('normal');
            return false;
            }
          }
        );


        //look for an active submenu
        $active=$('#menu ul > li.active');
        if($active.length>0) {
          $link=$active.parents('li').find('a:first');
          $link.trigger('click');
          //alert($link.length);
        }

      }
    $(document).ready(function() {initMenu();});
