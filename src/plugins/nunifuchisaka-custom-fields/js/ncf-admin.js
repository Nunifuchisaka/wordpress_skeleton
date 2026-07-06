jQuery(document).ready(function($) {
  
  // ------------------------------------
  // リピーター機能
  // ------------------------------------
  $(document).on('click', '.ncf-add-row', function() {
    const wrapper = $(this).closest('.ncf-repeater-wrapper');
    const template = wrapper.find('.ncf-repeater-template').html();
    const index = new Date().getTime(); 
    const newRow = template.replace(/{index}/g, index);
    $(this).before(newRow);
  });

  $(document).on('click', '.ncf-remove-row', function() {
    if(confirm('削除しますか？')) {
      $(this).closest('.ncf-repeater-row').remove();
    }
  });

  // ------------------------------------
  // コードコピー機能
  // ------------------------------------
  $(document).on('click', '.ncf-copy-btn', function() {
    const textarea = $(this).prev('.ncf-code-area');
    textarea.select();
    try {
      document.execCommand('copy');
      const originalText = $(this).text();
      $(this).text('コピーしました！');
      const btn = $(this);
      setTimeout(function() {
        btn.text(originalText);
      }, 1500);
    } catch (err) {
      alert('コピーに失敗しました');
    }
  });

  // ------------------------------------
  // 画像アップロード機能
  // ------------------------------------
  let file_frame;

  $(document).on('click', '.ncf-select-image', function(e) {
    e.preventDefault();

    const btn = $(this);
    const container = btn.closest('.ncf-image-wrapper');
    const inputId = container.find('.ncf-image-id');
    const preview = container.find('.ncf-image-preview');
    const removeBtn = container.find('.ncf-remove-image');

    // すでにフレームが開いていればそれを開く（再利用はせず毎回作成するほうが安全）
    // if ( file_frame ) { ... } の処理は複数フィールド対応のため省略し、毎回新規作成する

    file_frame = wp.media({
      title: '画像を選択',
      button: { text: '画像を決定' },
      multiple: false
    });

    // 画像選択時の処理
    file_frame.on('select', function() {
      const attachment = file_frame.state().get('selection').first().toJSON();
      
      // IDを隠しフィールドにセット
      inputId.val(attachment.id);
      
      // プレビュー表示
      let thumbUrl = attachment.url;
      if ( attachment.sizes && attachment.sizes.thumbnail ) {
        thumbUrl = attachment.sizes.thumbnail.url;
      }
      preview.attr('src', thumbUrl).removeClass('hidden');
      
      // ボタン表示切り替え
      btn.text('画像を変更');
      removeBtn.removeClass('hidden');
    });

    file_frame.open();
  });

  // 画像削除ボタン
  $(document).on('click', '.ncf-remove-image', function(e) {
    e.preventDefault();
    const container = $(this).closest('.ncf-image-wrapper');
    
    // 値をクリア
    container.find('.ncf-image-id').val('');
    container.find('.ncf-image-preview').attr('src', '').addClass('hidden');
    container.find('.ncf-select-image').text('画像を選択');
    $(this).addClass('hidden');
  });

});