<?php get_header(); ?>

<h1 class="h1">
  <?php the_archive_title(); ?>
</h1>
<?php the_archive_description( '<div class="archive_description_1">', '</div>' ); ?>

<?php if (have_posts()): ?>
  <div class="article_list_1">
    <?php while (have_posts()): the_post(); ?>
      <article class="article_list_1__item">
  <a class="article_list_1__link" href="<?php the_permalink(); ?>">
    <?php
    // NCFのメイン画像をサムネイルとして表示
    $list_img_id = get_post_meta( get_the_ID(), 'ncf_demo_image', true );
    if ( $list_img_id ) {
      echo wp_get_attachment_image( $list_img_id, 'medium', false, array( 'class' => 'article_list_1__image' ) );
    }
    ?>
    <h2 class="article_list_1__title"><?php the_title(); ?></h2>
  </a>
  <p class="article_list_1__meta">
    <time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
    <?php
    // NCFの優先度をラベル表示
    $list_priority = get_post_meta( get_the_ID(), 'ncf_priority_radio', true );
    if ( $list_priority ) {
      echo '<span class="article_list_1__priority">優先度: ' . esc_html( $list_priority ) . '</span>';
    }
    ?>
  </p>
  <div class="article_list_1__excerpt">
    <?php the_excerpt(); ?>
  </div>
</article>

    <?php endwhile; ?>
  </div>
  <?php my_pagination(); ?>
<?php else: ?>
  <p>記事が見つかりませんでした。</p>
<?php endif; ?>

<?php get_footer();
