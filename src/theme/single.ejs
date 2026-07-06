<?php get_header(); ?>

<?php if (have_posts()):
  while (have_posts()):
    the_post(); ?>
    <article class="article">
      <h1 class="h1">
        <?php the_title(); ?>
      </h1>
      <p class="article__meta">
        <time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
        <?php echo get_the_category_list( ', ' ); ?>
      </p>
      <div class="wp_content_1">
        <?php the_content(); ?>
      </div>

      <?php
      // --------------------------------------------------
      // NCF（カスタムフィールド）の出力
      // フィールド定義: src/theme/functions/_ncf.ejs
      // --------------------------------------------------
      ?>
      <section class="ncf_1">
        <h2 class="ncf_1__title">カスタムフィールド（NCF）</h2>
        <dl class="ncf_1__list">

          <dt>一行テキスト (text)</dt>
          <dd><?php echo esc_html( get_post_meta( get_the_ID(), 'ncf_demo_text', true ) ); ?></dd>

          <dt>テキストエリア (textarea)</dt>
          <dd><?php echo nl2br( esc_html( get_post_meta( get_the_ID(), 'ncf_demo_textarea', true ) ) ); ?></dd>

          <dt>セレクトボックス (select)</dt>
          <dd><?php echo esc_html( get_post_meta( get_the_ID(), 'ncf_demo_select', true ) ); ?></dd>

          <dt>優先度 (radio)</dt>
          <dd><?php echo esc_html( get_post_meta( get_the_ID(), 'ncf_priority_radio', true ) ); ?></dd>

          <dt>設備オプション (checkbox)</dt>
          <dd>
            <?php
            $options_check = get_post_meta( get_the_ID(), 'ncf_options_check', true );
            if ( ! empty( $options_check ) && is_array( $options_check ) ) {
              echo esc_html( implode( ', ', $options_check ) );
            }
            ?>
          </dd>

          <dt>数値 (number)</dt>
          <dd><?php echo esc_html( get_post_meta( get_the_ID(), 'ncf_demo_number', true ) ); ?></dd>

          <dt>URL (url)</dt>
          <dd>
            <?php
            $demo_url = get_post_meta( get_the_ID(), 'ncf_demo_url', true );
            if ( $demo_url ) {
              echo '<a href="' . esc_url( $demo_url ) . '">' . esc_html( $demo_url ) . '</a>';
            }
            ?>
          </dd>

          <dt>メールアドレス (email)</dt>
          <dd><?php echo esc_html( get_post_meta( get_the_ID(), 'ncf_demo_email', true ) ); ?></dd>

          <dt>日付 (date)</dt>
          <dd>
            <?php
            $demo_date = get_post_meta( get_the_ID(), 'ncf_demo_date', true );
            if ( $demo_date ) {
              echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $demo_date ) ) );
            }
            ?>
          </dd>

          <dt>カラー (color)</dt>
          <dd>
            <?php
            $demo_color = get_post_meta( get_the_ID(), 'ncf_demo_color', true );
            if ( $demo_color ) {
              echo '<span class="ncf_1__color" style="background-color:' . esc_attr( $demo_color ) . ';"></span> ' . esc_html( $demo_color );
            }
            ?>
          </dd>

          <dt>メイン画像 (image)</dt>
          <dd>
            <?php
            $demo_image_id = get_post_meta( get_the_ID(), 'ncf_demo_image', true );
            if ( $demo_image_id ) {
              echo wp_get_attachment_image( $demo_image_id, 'medium' );
            }
            ?>
          </dd>

          <dt>関連記事 (post)</dt>
          <dd>
            <?php
            $post_select_id = get_post_meta( get_the_ID(), 'ncf_demo_post_select', true );
            if ( $post_select_id ) {
              echo '<a href="' . esc_url( get_permalink( $post_select_id ) ) . '">' . esc_html( get_the_title( $post_select_id ) ) . '</a>';
            }
            ?>
          </dd>

          <dt>関連固定ページ (post)</dt>
          <dd>
            <?php
            $page_select_id = get_post_meta( get_the_ID(), 'ncf_demo_page_select', true );
            if ( $page_select_id ) {
              echo '<a href="' . esc_url( get_permalink( $page_select_id ) ) . '">' . esc_html( get_the_title( $page_select_id ) ) . '</a>';
            }
            ?>
          </dd>

        </dl>

        <h3 class="ncf_1__subtitle">リピーター (repeater)</h3>
        <?php
        $repeater_rows = get_post_meta( get_the_ID(), 'ncf_demo_repeater', true );
        if ( ! empty( $repeater_rows ) && is_array( $repeater_rows ) ): ?>
          <ul class="ncf_1__repeater">
            <?php // $i は0始まりの行番号（奇数/偶数の出し分けやid属性の付与に使えます）
            foreach ( $repeater_rows as $i => $row ): ?>
              <li class="ncf_1__repeater_row" id="ncf_repeater_row_<?php echo esc_attr( $i ); ?>">
                <h4 class="ncf_1__repeater_title"><?php echo esc_html( $row['sub_title'] ?? '' ); ?></h4>
                <?php
                $sub_image_id = $row['sub_image'] ?? 0;
                if ( $sub_image_id ) {
                  echo wp_get_attachment_image( $sub_image_id, 'thumbnail' );
                }
                ?>
                <p>優先度: <?php echo esc_html( $row['priority_radio'] ?? '' ); ?></p>
                <p>設備オプション: <?php echo esc_html( implode( ', ', (array) ( $row['options_check'] ?? array() ) ) ); ?></p>
                <p>リンク先記事:
                  <?php
                  $sub_link_post_id = $row['sub_link_post'] ?? 0;
                  if ( $sub_link_post_id ) {
                    echo '<a href="' . esc_url( get_permalink( $sub_link_post_id ) ) . '">' . esc_html( get_the_title( $sub_link_post_id ) ) . '</a>';
                  }
                  ?>
                </p>
                <p>外部リンク:
                  <?php
                  $sub_url = $row['sub_url'] ?? '';
                  if ( $sub_url ) {
                    echo '<a href="' . esc_url( $sub_url ) . '">' . esc_html( $sub_url ) . '</a>';
                  }
                  ?>
                </p>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>
    </article>

    <nav class="post_nav_1">
      <p class="post_nav_1__prev"><?php previous_post_link( '%link', '« %title' ); ?></p>
      <p class="post_nav_1__next"><?php next_post_link( '%link', '%title »' ); ?></p>
    </nav>
  <?php endwhile; endif; ?>

<?php get_footer();
