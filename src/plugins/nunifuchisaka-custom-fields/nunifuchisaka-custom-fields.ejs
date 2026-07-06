<?php
/**
 * Plugin Name: Nunifuchisaka Custom Fields
 * Description: カスタマイズ可能な軽量カスタムフィールド管理プラグイン
 * Version: 1.1.0
 * Author: Nunifuchisaka
 * License: GPL2
 * Text Domain: nunifuchisaka-custom-fields
 */

namespace NCF;

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class Custom_Fields {

  private $prefix = 'ncf_';
  private $nonce  = 'ncf_nonce_action';

  public function __construct() {
    add_action( 'init', [ $this, 'load_textdomain' ] );
    add_action( 'add_meta_boxes', [ $this, 'register_meta_boxes' ] );
    add_action( 'save_post', [ $this, 'save_meta_data' ] );
    add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
  }

  public function load_textdomain() {
    load_plugin_textdomain( 'nunifuchisaka-custom-fields', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
  }

  public function enqueue_admin_assets( $hook ) {
    if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
      return;
    }

    // メディアアップローダーの読み込み
    wp_enqueue_media();

    // カラーピッカー
    wp_enqueue_style( 'wp-color-picker' );

    wp_enqueue_script(
      'ncf-admin-script',
      plugins_url( 'js/ncf-admin.js', __FILE__ ),
      [ 'jquery', 'jquery-ui-sortable', 'wp-color-picker' ],
      '1.1.0',
      true
    );

    wp_localize_script( 'ncf-admin-script', 'ncfL10n', [
      'confirmRemoveRow' => __( '削除しますか？', 'nunifuchisaka-custom-fields' ),
      'copied'           => __( 'コピーしました！', 'nunifuchisaka-custom-fields' ),
      'copyFailed'       => __( 'コピーに失敗しました', 'nunifuchisaka-custom-fields' ),
      'imageModalTitle'  => __( '画像を選択', 'nunifuchisaka-custom-fields' ),
      'imageModalButton' => __( '画像を決定', 'nunifuchisaka-custom-fields' ),
      'selectImage'      => __( '画像を選択', 'nunifuchisaka-custom-fields' ),
      'changeImage'      => __( '画像を変更', 'nunifuchisaka-custom-fields' ),
    ] );

    wp_enqueue_style(
      'ncf-admin-style',
      plugins_url( 'css/ncf-admin.css', __FILE__ ),
      [],
      '1.1.0'
    );
  }

  public function register_meta_boxes() {
    $configs = apply_filters( 'ncf_register_fields', [] );

    if ( empty( $configs ) || ! is_array( $configs ) ) return;

    foreach ( $configs as $box_id => $box_args ) {
      add_meta_box(
        $box_id,
        $box_args['title'] ?? __( 'Custom Fields', 'nunifuchisaka-custom-fields' ),
        [ $this, 'render_meta_box' ],
        $box_args['screen'] ?? 'post',
        $box_args['context'] ?? 'advanced',
        $box_args['priority'] ?? 'default',
        $box_args['fields'] ?? []
      );
    }
  }

  public function render_meta_box( $post, $callback_args ) {
    wp_nonce_field( $this->nonce, $this->nonce . '_field' );
    $fields = $callback_args['args'];

    if ( empty( $fields ) ) return;

    // labelのfor属性で入力欄に紐付けられるフィールド型
    $focusable_types = [ 'text', 'textarea', 'select', 'post', 'number', 'url', 'email', 'date', 'color' ];

    echo '<table class="form-table"><tbody>';

    foreach ( $fields as $field ) {
      $key      = $field['key'];
      $meta_key = $this->prefix . $key;
      $value    = get_post_meta( $post->ID, $meta_key, true );
      $type     = $field['type'] ?? 'text';

      echo '<tr>';
      echo '<th class="ncf-field-th">';
      if ( in_array( $type, $focusable_types, true ) ) {
        echo '<label for="' . esc_attr( $this->field_input_id( $meta_key ) ) . '">' . esc_html( $field['label'] ?? '' ) . '</label>';
      } else {
        echo '<label>' . esc_html( $field['label'] ?? '' ) . '</label>';
      }
      echo '</th>';
      echo '<td>';

      if ( $type === 'repeater' ) {
        $this->render_repeater_field( $meta_key, $value, $field['sub_fields'] ?? [] );
      } else {
        $this->render_single_field( $meta_key, $field, $value );
      }

      if ( ! empty( $field['desc'] ) ) {
        echo '<p class="description">' . esc_html( $field['desc'] ) . '</p>';
      }
      echo '</td></tr>';
    }
    echo '</tbody></table>';

    $this->render_all_code_snippet( $fields );
  }

  /**
   * コード出力
   */
  private function render_all_code_snippet( $fields ) {
    $code = "<?php\n";

    foreach ( $fields as $field ) {
      $key      = $field['key'];
      $label    = $field['label'] ?? $key;
      $type     = $field['type'] ?? 'text';
      $meta_key = $this->prefix . $key;

      $var_name = '$' . $key;

      $code .= "// --- {$label} ({$type}) ---\n";

      if ( $type === 'repeater' ) {
        $sub_fields = $field['sub_fields'] ?? [];
        $code .= "{$var_name}_rows = get_post_meta( get_the_ID(), '{$meta_key}', true );\n";
        $code .= "if ( ! empty( {$var_name}_rows ) && is_array( {$var_name}_rows ) ) {\n";
        $code .= "  foreach ( {$var_name}_rows as \$row ) {\n";
        foreach ( $sub_fields as $sub ) {
          $sub_key = $sub['key'];
          $sub_lbl = $sub['label'] ?? '';

          if ( isset($sub['type']) && $sub['type'] === 'image' ) {
             $code .= "    // {$sub_lbl} (画像ID)\n";
             $code .= "    \$img_id = \$row['{$sub_key}'] ?? 0;\n";
             $code .= "    echo wp_get_attachment_image( \$img_id, 'medium' );\n";
          } elseif ( isset($sub['type']) && $sub['type'] === 'checkbox' ) {
             $code .= "    // {$sub_lbl} (配列)\n";
             $code .= "    \$sub_vals = \$row['{$sub_key}'] ?? [];\n";
             $code .= "    echo esc_html( implode(', ', \$sub_vals) );\n";
          } elseif ( isset($sub['type']) && $sub['type'] === 'post' ) {
             $code .= "    // {$sub_lbl} (記事ID)\n";
             $code .= "    \$pid = \$row['{$sub_key}'] ?? 0;\n";
             $code .= "    if( \$pid ) echo '<a href=\"' . get_permalink(\$pid) . '\">' . get_the_title(\$pid) . '</a>';\n";
          } elseif ( isset($sub['type']) && $sub['type'] === 'url' ) {
             $code .= "    // {$sub_lbl} (URL)\n";
             $code .= "    echo esc_url( \$row['{$sub_key}'] ?? '' );\n";
          } else {
             $code .= "    // {$sub_lbl}\n";
             $code .= "    echo esc_html( \$row['{$sub_key}'] ?? '' );\n";
          }
          $code .= "    echo '<br>';\n";
        }
        $code .= "  }\n";
        $code .= "}\n";

      } elseif ( $type === 'image' ) {
        $code .= "{$var_name}_id = get_post_meta( get_the_ID(), '{$meta_key}', true );\n";
        $code .= "if ( {$var_name}_id ) {\n";
        $code .= "  echo wp_get_attachment_image( {$var_name}_id, 'full' );\n";
        $code .= "}\n";

      } elseif ( $type === 'post' ) {
        // 投稿選択用の出力コード
        $code .= "{$var_name}_id = get_post_meta( get_the_ID(), '{$meta_key}', true );\n";
        $code .= "if ( {$var_name}_id ) {\n";
        $code .= "  echo '<a href=\"' . get_permalink( {$var_name}_id ) . '\">' . get_the_title( {$var_name}_id ) . '</a>';\n";
        $code .= "}\n";

      } elseif ( $type === 'checkbox' ) {
        $code .= "{$var_name} = get_post_meta( get_the_ID(), '{$meta_key}', true );\n";
        $code .= "if ( ! empty( {$var_name} ) && is_array( {$var_name} ) ) {\n";
        $code .= "  echo esc_html( implode(', ', {$var_name}) );\n";
        $code .= "}\n";

      } elseif ( $type === 'url' ) {
        $code .= "{$var_name} = get_post_meta( get_the_ID(), '{$meta_key}', true );\n";
        $code .= "echo esc_url( {$var_name} );\n";

      } else {
        $code .= "{$var_name} = get_post_meta( get_the_ID(), '{$meta_key}', true );\n";
        if ( $type === 'textarea' ) {
          $code .= "echo nl2br( esc_html( {$var_name} ) );\n";
        } else {
          $code .= "echo esc_html( {$var_name} );\n";
        }
      }
      $code .= "\n";
    }
    $code .= "?>";

    echo '<details class="ncf-code-details">';
    echo '<summary>' . esc_html__( '▶ まとめて出力コードを取得', 'nunifuchisaka-custom-fields' ) . '</summary>';
    echo '<textarea class="ncf-code-area" readonly>' . esc_textarea( $code ) . '</textarea>';
    echo '<button type="button" class="button button-small ncf-copy-btn">' . esc_html__( 'コードをコピー', 'nunifuchisaka-custom-fields' ) . '</button>';
    echo '</details>';
  }

  /**
   * フィールドのinput要素のid属性を生成する
   * name属性から決定的に導出する（labelのforと一致させるため）
   * リピーターのテンプレート行では {index} を残し、JS側の置換で行ごとに一意になる
   */
  private function field_input_id( $name ) {
    return 'ncf_field_' . preg_replace( '/[^a-zA-Z0-9_{}-]/', '_', $name );
  }

  /**
   * 単一フィールド描画
   */
  private function render_single_field( $name, $field, $value ) {
    $type = $field['type'] ?? 'text';
    $id   = esc_attr( $this->field_input_id( $name ) );

    switch ( $type ) {
      case 'post': // 投稿選択フィールド
        $post_type = $field['post_type'] ?? 'post';
        // 投稿を取得（軽量化のため既定で最大50件。posts_per_pageで変更可）
        $args = [
          'post_type'      => $post_type,
          'posts_per_page' => $field['posts_per_page'] ?? 50,
          'orderby'        => 'date',
          'order'          => 'DESC',
        ];
        $posts_array = get_posts( $args );

        // 保存済みの投稿が取得件数から漏れていても選択肢に含める（再保存で選択が消えるのを防ぐ）
        if ( $value && ! in_array( (int) $value, array_map( 'intval', wp_list_pluck( $posts_array, 'ID' ) ), true ) ) {
          $current_post = get_post( (int) $value );
          if ( $current_post ) {
            array_unshift( $posts_array, $current_post );
          }
        }

        echo '<select name="' . esc_attr( $name ) . '" id="' . $id . '">';
        echo '<option value="">' . esc_html__( '選択してください', 'nunifuchisaka-custom-fields' ) . '</option>';
        foreach ( $posts_array as $p ) {
          $selected = selected( $value, $p->ID, false );
          // 投稿IDをValue、タイトルを表示
          echo '<option value="' . esc_attr( $p->ID ) . '" ' . $selected . '>' . esc_html( $p->post_title ) . '</option>';
        }
        echo '</select>';
        break;

      case 'image':
        $img_url = '';
        $has_img = ! empty( $value );
        if ( $has_img ) {
          $img_url = wp_get_attachment_image_url( $value, 'thumbnail' );
        }
        echo '<div class="ncf-image-wrapper">';
        echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" class="ncf-image-id">';
        echo '<div class="ncf-image-preview-wrapper">';
        echo '<img src="' . esc_url( $img_url ) . '" class="ncf-image-preview ' . ( $has_img ? '' : 'hidden' ) . '">';
        echo '</div>';
        echo '<button type="button" class="button ncf-select-image">' . esc_html( $has_img ? __( '画像を変更', 'nunifuchisaka-custom-fields' ) : __( '画像を選択', 'nunifuchisaka-custom-fields' ) ) . '</button> ';
        echo '<button type="button" class="button ncf-remove-image ' . ( $has_img ? '' : 'hidden' ) . '">' . esc_html__( '削除', 'nunifuchisaka-custom-fields' ) . '</button>';
        echo '</div>';
        break;

      case 'textarea':
        echo '<textarea name="' . esc_attr( $name ) . '" id="' . $id . '" rows="4" class="large-text">' . esc_textarea( $value ) . '</textarea>';
        break;

      case 'select':
        echo '<select name="' . esc_attr( $name ) . '" id="' . $id . '">';
        foreach ( ($field['options'] ?? []) as $opt_val => $opt_label ) {
          $selected = selected( $value, $opt_val, false );
          echo '<option value="' . esc_attr( $opt_val ) . '" ' . $selected . '>' . esc_html( $opt_label ) . '</option>';
        }
        echo '</select>';
        break;

      case 'radio':
        echo '<div class="ncf-input-list">';
        foreach ( ($field['options'] ?? []) as $opt_val => $opt_label ) {
          $checked = checked( $value, $opt_val, false );
          $opt_id  = $id . '_' . $opt_val;
          echo '<label for="' . esc_attr( $opt_id ) . '">';
          echo '<input type="radio" name="' . esc_attr( $name ) . '" id="' . esc_attr( $opt_id ) . '" value="' . esc_attr( $opt_val ) . '" ' . $checked . '> ';
          echo esc_html( $opt_label );
          echo '</label>';
        }
        echo '</div>';
        break;

      case 'checkbox':
        if ( ! is_array( $value ) ) $value = [];
        echo '<div class="ncf-input-list">';
        foreach ( ($field['options'] ?? []) as $opt_val => $opt_label ) {
          $checked = in_array( $opt_val, $value ) ? 'checked="checked"' : '';
          $opt_id  = $id . '_' . $opt_val;
          echo '<label for="' . esc_attr( $opt_id ) . '">';
          echo '<input type="checkbox" name="' . esc_attr( $name ) . '[]" id="' . esc_attr( $opt_id ) . '" value="' . esc_attr( $opt_val ) . '" ' . $checked . '> ';
          echo esc_html( $opt_label );
          echo '</label>';
        }
        echo '</div>';
        break;

      case 'number':
      case 'url':
      case 'email':
      case 'date':
        echo '<input type="' . esc_attr( $type ) . '" name="' . esc_attr( $name ) . '" id="' . $id . '" value="' . esc_attr( $value ) . '" class="regular-text">';
        break;

      case 'color':
        echo '<input type="text" name="' . esc_attr( $name ) . '" id="' . $id . '" value="' . esc_attr( $value ) . '" class="ncf-color-field">';
        break;

      case 'text':
      default:
        echo '<input type="text" name="' . esc_attr( $name ) . '" id="' . $id . '" value="' . esc_attr( $value ) . '" class="regular-text">';
        break;
    }
  }

  /**
   * リピーター描画
   */
  private function render_repeater_field( $parent_key, $values, $sub_fields ) {
    if ( ! is_array( $values ) ) $values = [];
    if ( empty( $values ) ) $values = [ [] ];

    echo '<div class="ncf-repeater-wrapper" data-parent="' . esc_attr( $parent_key ) . '">';

    foreach ( $values as $index => $row_data ) {
      $this->render_repeater_row( $parent_key, $index, $sub_fields, $row_data );
    }

    echo '<div class="ncf-repeater-template" style="display:none;">';
    $this->render_repeater_row( $parent_key, '{index}', $sub_fields, [] );
    echo '</div>';

    echo '<button type="button" class="button button-primary ncf-add-row">' . esc_html__( '行を追加', 'nunifuchisaka-custom-fields' ) . '</button>';
    echo '</div>';
  }

  private function render_repeater_row( $parent_key, $index, $sub_fields, $row_data ) {
    echo '<div class="ncf-repeater-row">';
    echo '<span class="ncf-repeater-handle dashicons dashicons-menu" title="' . esc_attr__( 'ドラッグで並べ替え', 'nunifuchisaka-custom-fields' ) . '"></span>';
    foreach ( $sub_fields as $sub ) {
      $sub_key = $sub['key'];
      $input_name = $parent_key . '[' . $index . '][' . $sub_key . ']';
      $sub_val    = $row_data[ $sub_key ] ?? '';

      echo '<div class="ncf-repeater-field">';
      echo '<label class="ncf-repeater-field-label">' . esc_html( $sub['label'] ) . '</label>';
      echo '<div class="ncf-repeater-field-input">';
      $this->render_single_field( $input_name, $sub, $sub_val );
      echo '</div>';
      echo '</div>';
    }
    echo '<button type="button" class="button ncf-remove-row">' . esc_html__( '削除', 'nunifuchisaka-custom-fields' ) . '</button>';
    echo '</div>';
  }

  /**
   * フィールド型に応じたサニタイズ
   * $field['sanitize_callback'] が指定されていればそちらを優先する
   */
  private function sanitize_field_value( $field, $value ) {
    if ( isset( $field['sanitize_callback'] ) && is_callable( $field['sanitize_callback'] ) ) {
      return call_user_func( $field['sanitize_callback'], $value, $field );
    }

    if ( is_array( $value ) ) {
      return array_map( 'sanitize_text_field', $value );
    }

    switch ( $field['type'] ?? 'text' ) {
      case 'image':
      case 'post':
        return $value ? absint( $value ) : '';
      case 'number':
        return is_numeric( $value ) ? 0 + $value : '';
      case 'url':
        return esc_url_raw( $value );
      case 'email':
        return sanitize_email( $value );
      case 'color':
        return sanitize_hex_color( $value ) ?: '';
      case 'textarea':
        return sanitize_textarea_field( $value );
      default:
        return sanitize_text_field( $value );
    }
  }

  /**
   * リピーター行が全サブフィールド空かどうか
   */
  private function is_empty_row( $row ) {
    foreach ( $row as $value ) {
      if ( is_array( $value ) ) {
        if ( ! empty( $value ) ) return false;
      } elseif ( '' !== trim( (string) $value ) ) {
        return false;
      }
    }
    return true;
  }

  /**
   * 保存処理
   */
  public function save_meta_data( $post_id ) {
    if ( ! isset( $_POST[ $this->nonce . '_field' ] ) ) return;
    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $this->nonce . '_field' ] ) ), $this->nonce ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $configs   = apply_filters( 'ncf_register_fields', [] );
    $post_type = get_post_type( $post_id );

    foreach ( $configs as $box ) {
      if ( empty( $box['fields'] ) ) continue;

      // 対象外のpost_typeのフィールドは保存も削除もしない
      $screens = (array) ( $box['screen'] ?? 'post' );
      if ( ! in_array( $post_type, $screens, true ) ) continue;

      foreach ( $box['fields'] as $field ) {
        $key      = $field['key'];
        $meta_key = $this->prefix . $key;
        $type     = $field['type'] ?? 'text';

        if ( isset( $_POST[ $meta_key ] ) ) {
          // WordPressが$_POSTに付与するスラッシュを除去してからサニタイズする
          $data = wp_unslash( $_POST[ $meta_key ] );

          if ( $type === 'repeater' && is_array( $data ) ) {
            $sub_defs = [];
            foreach ( ( $field['sub_fields'] ?? [] ) as $sub ) {
              $sub_defs[ $sub['key'] ] = $sub;
            }

            $clean_data = [];
            foreach ( $data as $row_index => $row ) {
              if ( $row_index === '{index}' ) continue;
              if ( ! is_array( $row ) ) continue;
              if ( $this->is_empty_row( $row ) ) continue;

              $clean_row = [];
              foreach ( $row as $sub_key => $sub_val ) {
                $clean_row[ $sub_key ] = $this->sanitize_field_value( $sub_defs[ $sub_key ] ?? [], $sub_val );
              }
              $clean_data[] = $clean_row;
            }
            update_post_meta( $post_id, $meta_key, $clean_data );

          } else {
            update_post_meta( $post_id, $meta_key, $this->sanitize_field_value( $field, $data ) );
          }

        } else {
          if ( in_array( $type, [ 'checkbox', 'radio', 'image', 'post' ], true ) ) {
             delete_post_meta( $post_id, $meta_key );
          }
        }
      }
    }
  }
}

new Custom_Fields();
