(function ($) {
  "use strict";

  Drupal.behaviors.socialApprove = {
    attach: function (context, settings) {

      var $checkbox_html = '<input type="checkbox" />';
      var $checkbox_enabled = '<input type="checkbox" checked="checked" />';

      var $rows = $("#feed-table tbody tr", context);
      $.each($rows, function(index, element) {
        var $td = jQuery("td", element);
        var $textarea = jQuery("textarea", element);

        var $image = jQuery("img", $td.first());
        var approved = $image.data('approved');

        var $checkbox = $td.last();
        var id = $checkbox.html();

        if (approved == 1) {
          $checkbox.html($checkbox_enabled);
        }
        else {
          $checkbox.html($checkbox_html);
        }

        $checkbox.click(function() {

          jQuery.get( Drupal.url('admin/config/media/instagram/ajaxApproveFeed?instagram_id=') + id);
        });



        $textarea.focusout(function() {

          if( $(this).val() ){
            
            jQuery.get( Drupal.url('admin/config/media/instagram/ajaxCommentsFeed?instagram_id=') +  $(this).attr('name') + '&comment='+ $(this).val());
          }
         });
      });

      var $select = $("select", context);
      $select.change(function(data) {
        window.location.href= Drupal.url('admin/config/media/instagram/admin/moderation?feed_id=') + this.value;
      });

    }
  }
}(jQuery));
