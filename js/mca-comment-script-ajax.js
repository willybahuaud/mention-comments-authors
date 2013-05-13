//WRAP INTO ANOTHER FUNCT TO LAUNCH AT EACH AJAX CHANGE
function mcaAjaxChange(){
    jQuery(document).ready(function($){  
        //FIND mcaAuthors
        var mcaAuthors = new Array;
        var $elems = $('.mca-author');
        $elems.each(function(index){
            mcaAuthors.push({val:$(this).attr('data-name'),meta:$(this).attr('data-realname')});
        });

        //ADD AUTOSUGGEST
        var customItemTemplate = "<div><span />&nbsp;<small /></div>";

        function elementFactory(element, e) {
            var template = $(customItemTemplate).find('span')
                                                .text('@' + e.val).end()
                                                .find('small')
                                                .text("(" + (e.meta || e.val) + ")").end();
            element.append(template);
        };

        $comment = $('#comment');
        $comment.sew({values: mcaAuthors, elementFactory: elementFactory});

        //SCROLL TO LAST COMMS
        $('.mca-button').on('click',function(){
            $('.mca-fired').removeClass('mca-fired');
            $('.mca-prevent-elem').removeClass('mca-prevent-elem');
            $('.mca-comment-text-wrapper').removeClass('mca-comment-text-wrapper');

            var target = $(this).attr('data-target');
            var $elems = $('.mca-author');
            var $ishim = null;

            $(this).parents('.mca-author').addClass('mca-fired');

            $elems.each(function(index){
                if($(this).hasClass('mca-fired') || index == $elems.length-1){
                    if($ishim != null){
                        $ishim.addClass('mca-prevent-elem').parent().addClass('mca-comment-text-wrapper');
                        $('body,html').animate({scrollTop:$ishim.offset().top-200}, 200);
                    }
                    return false;
                }
                if($(this).attr('data-name') == target)
                    $ishim = $(this);
            });
        });
    });
}
mcaAjaxChange();