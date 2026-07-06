/* global wp */
jQuery(document).ready(function($) {

  const l10n = window.ncfL10n || {};

  // ------------------------------------
  // カラーピッカー
  // ------------------------------------
  function initColorPickers($scope) {
    if (!$.fn.wpColorPicker) {
      return;
    }
    // テンプレート行は初期化するとクローン時に構造が壊れるため対象外
    $scope.find('.ncf-color-field')
      .not('.ncf-repeater-template .ncf-color-field')
      .each(function() {
        $(this).wpColorPicker();
      });
  }

  initColorPickers($(document.body));

  // ------------------------------------
  // リピーター機能
  // ------------------------------------
  $(document).on('click', '.ncf-add-row', function() {
    const wrapper = $(this).closest('.ncf-repeater-wrapper');
    const template = wrapper.find('.ncf-repeater-template').html();
    const index = new Date().getTime();
    const newRow = $(template.replace(/{index}/g, index));
    $(this).before(newRow);
    initColorPickers(newRow);
  });

  $(document).on('click', '.ncf-remove-row', function() {
    if (confirm(l10n.confirmRemoveRow || '削除しますか？')) {
      $(this).closest('.ncf-repeater-row').remove();
    }
  });

  // 行のドラッグ並べ替え（テンプレート行は .ncf-repeater-template 内なので直下セレクタで対象外）
  if ($.fn.sortable) {
    $('.ncf-repeater-wrapper').sortable({
      items: '> .ncf-repeater-row',
      handle: '.ncf-repeater-handle',
      cursor: 'move',
      placeholder: 'ncf-sortable-placeholder',
      forcePlaceholderSize: true
    });
  }

  // ------------------------------------
  // コードコピー機能
  // ------------------------------------
  $(document).on('click', '.ncf-copy-btn', function() {
    const btn = $(this);
    const textarea = btn.prev('.ncf-code-area');

    const showCopied = function() {
      const originalText = btn.text();
      btn.text(l10n.copied || 'コピーしました！');
      setTimeout(function() {
        btn.text(originalText);
      }, 1500);
    };

    const fallbackCopy = function() {
      textarea.select();
      try {
        document.execCommand('copy');
        showCopied();
      } catch {
        alert(l10n.copyFailed || 'コピーに失敗しました');
      }
    };

    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(textarea.val()).then(showCopied, fallbackCopy);
    } else {
      fallbackCopy();
    }
  });

  // ------------------------------------
  // 画像アップロード機能
  // ------------------------------------
  $(document).on('click', '.ncf-select-image', function(e) {
    e.preventDefault();

    const btn = $(this);
    const container = btn.closest('.ncf-image-wrapper');
    const inputId = container.find('.ncf-image-id');
    const preview = container.find('.ncf-image-preview');
    const removeBtn = container.find('.ncf-remove-image');

    // 複数フィールド対応のため、フレームは再利用せず毎回新規作成する
    const file_frame = wp.media({
      title: l10n.imageModalTitle || '画像を選択',
      button: { text: l10n.imageModalButton || '画像を決定' },
      multiple: false
    });

    // 画像選択時の処理
    file_frame.on('select', function() {
      const attachment = file_frame.state().get('selection').first().toJSON();

      // IDを隠しフィールドにセット
      inputId.val(attachment.id);

      // プレビュー表示
      let thumbUrl = attachment.url;
      if (attachment.sizes && attachment.sizes.thumbnail) {
        thumbUrl = attachment.sizes.thumbnail.url;
      }
      preview.attr('src', thumbUrl).removeClass('hidden');

      // ボタン表示切り替え
      btn.text(l10n.changeImage || '画像を変更');
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
    container.find('.ncf-select-image').text(l10n.selectImage || '画像を選択');
    $(this).addClass('hidden');
  });

});
