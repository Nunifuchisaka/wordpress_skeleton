<?php
/**
 * Plugin Name: Nunifuchisaka Custom Fields
 * Description: カスタマイズ可能な軽量カスタムフィールド管理プラグイン
 * Version: 1.0.0
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
    add_action( 'add_meta_boxes', [ $this, 'register_meta_boxes' ] );
    add_action( 'save_post', [ $this, 'save_meta_data' ] );
    add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
  }

  public function enqueue_admin_assets( $hook ) {
    if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
      return;
    }

    // メディアアップローダーの読み込み
    wp_enqueue_media();

    wp_enqueue_script(
      'ncf-admin-script',
      plugins_url( 'js/ncf-admin.js', __FILE__ ),
      [ 'jquery' ],
      '1.0.0',
      true
    );

    wp_enqueue_style(
      'ncf-admin-style',
      plugins_url( 'css/ncf-admin.css', __FILE__ ),
      [],
      '1.0.0'
    );
  }

  public function register_meta_boxes() {
    $configs = apply_filters( 'ncf_register_fields', [] );

    if ( empty( $configs ) || ! is_array( $configs ) ) return;

    foreach ( $configs as $box_id => $box_args ) {
      add_meta_box(
        $box_id,
        $box_args['title'] ?? 'Custom Fields',
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

    echo '<table class="form-table"><tbody>';

    foreach ( $fields as $field ) {
      $key      = $field['key'];
      $meta_key = $this->prefix . $key;
      $value    = get_post_meta( $post->ID, $meta_key, true );
      
      echo '<tr>';
      echo '<th style="width:20%">';
      echo '<label>' . esc_html( $field['label'] ?? '' ) . '</label>';
      echo '</th>';
      echo '<td>';

      if ( isset( $field['type'] ) && $field['type'] === 'repeater' ) {
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
    echo '<summary>▶ まとめて出力コードを取得</summary>';
    echo '<textarea class="ncf-code-area" readonly>' . esc_textarea( $code ) . '</textarea>';
    echo '<button type="button" class="button button-small ncf-copy-btn">コードをコピー</button>';
    echo '</details>';
  }

  /**
   * 単一フィールド描画
   */
  private function render_single_field( $name, $field, $value ) {
    $type = $field['type'] ?? 'text';
    $id   = esc_attr( $name . '_' . uniqid() );

    switch ( $type ) {
      case 'post': // 投稿選択フィールド
        $post_type = $field['post_type'] ?? 'post';
        // 投稿を取得（軽量化のため最大50件に制限していますが、必要に応じて変更可）
        $args = [
          'post_type'      => $post_type,
          'posts_per_page' => 50, 
          'orderby'        => 'date',
          'order'          => 'DESC',
        ];
        $posts_array = get_posts( $args );

        echo '<select name="' . esc_attr( $name ) . '" id="' . $id . '">';
        echo '<option value="">選択してください</option>';
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
        echo '<button type="button" class="button ncf-select-image">' . ( $has_img ? '画像を変更' : '画像を選択' ) . '</button> ';
        echo '<button type="button" class="button ncf-remove-image ' . ( $has_img ? '' : 'hidden' ) . '" style="color: #a00;">削除</button>';
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

    echo '<button type="button" class="button button-primary ncf-add-row">行を追加</button>';
    echo '</div>';
  }

  private function render_repeater_row( $parent_key, $index, $sub_fields, $row_data ) {
    echo '<div class="ncf-repeater-row" style="background:#f9f9f9; padding:10px; margin-bottom:10px; border:1px solid #ddd;">';
    foreach ( $sub_fields as $sub ) {
      $sub_key = $sub['key'];
      $input_name = $parent_key . '[' . $index . '][' . $sub_key . ']';
      $sub_val    = $row_data[ $sub_key ] ?? '';
      
      echo '<div style="margin-bottom:5px;">';
      echo '<label style="display:inline-block; width:100px; font-weight:bold; vertical-align:top;">' . esc_html( $sub['label'] ) . '</label>';
      echo '<div style="display:inline-block; max-width: 80%; vertical-align:top;">';
      $this->render_single_field( $input_name, $sub, $sub_val );
      echo '</div>';
      echo '</div>';
    }
    echo '<button type="button" class="button ncf-remove-row">削除</button>';
    echo '</div>';
  }

  /**
   * 保存処理
   */
  public function save_meta_data( $post_id ) {
    if ( ! isset( $_POST[ $this->nonce . '_field' ] ) || ! wp_verify_nonce( $_POST[ $this->nonce . '_field' ], $this->nonce ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $configs = apply_filters( 'ncf_register_fields', [] );
    
    foreach ( $configs as $box ) {
      if ( empty( $box['fields'] ) ) continue;

      foreach ( $box['fields'] as $field ) {
        $key      = $field['key'];
        $meta_key = $this->prefix . $key;
        $type     = $field['type'] ?? 'text';

        if ( isset( $_POST[ $meta_key ] ) ) {
          $data = $_POST[ $meta_key ];

          if ( $type === 'repeater' && is_array( $data ) ) {
            $clean_data = [];
            foreach ( $data as $row_index => $row ) {
              if ( $row_index === '{index}' ) continue;
              if ( is_array( $row ) ) {
                $clean_row = [];
                foreach ( $row as $sub_key => $sub_val ) {
                  if ( is_array( $sub_val ) ) {
                    $clean_row[ $sub_key ] = array_map( 'sanitize_text_field', $sub_val );
                  } else {
                    $clean_row[ $sub_key ] = sanitize_text_field( $sub_val );
                  }
                }
                $clean_data[] = $clean_row;
              }
            }
            update_post_meta( $post_id, $meta_key, $clean_data );

          } elseif ( is_array( $data ) ) {
            $sanitized = array_map( 'sanitize_text_field', $data );
            update_post_meta( $post_id, $meta_key, $sanitized );

          } else {
            $sanitized = ( $type === 'textarea' ) ? sanitize_textarea_field( $data ) : sanitize_text_field( $data );
            update_post_meta( $post_id, $meta_key, $sanitized );
          }

        } else {
          if ( $type === 'checkbox' || $type === 'radio' || $type === 'image' || $type === 'post' ) {
             delete_post_meta( $post_id, $meta_key );
          }
        }
      }
    }
  }
}

new Custom_Fields();