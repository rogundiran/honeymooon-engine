jQuery(function($){
  $(document).on('click','.hm-add-row',function(){
    const $wrap=$(this).closest('.hm-repeater');
    const name=$wrap.data('name');
    const $rows=$wrap.find('.hm-repeater-rows');
    const idx=$rows.children('.hm-row').length;
    const html=$wrap.find('template').html().replace(/__index__/g, idx);
    $rows.append(html);
  });
  $(document).on('click','.hm-remove-row',function(){
    $(this).closest('.hm-row').remove();
  });
});
