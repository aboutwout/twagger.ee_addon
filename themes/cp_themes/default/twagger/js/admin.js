$(function() {
  $('input[name^=weblogs]').change(showTwaggerSettings);
  $('.itoggle input').change(toggleItoggle);
  $('.itoggle input').trigger('change')
});

function toggleItoggle(evt) {
  var wrap = $(this).closest('.itoggle');  
  ($(this).is(':checked')) ? wrap.addClass('itoggle_checked') : wrap.removeClass('itoggle_checked');
}

function showTwaggerSettings(evt) {
  var tr = $(this).closest('tr');
  ($(this).is(':checked')) ? tr.addClass('show_settings') : tr.removeClass('show_settings');
}

