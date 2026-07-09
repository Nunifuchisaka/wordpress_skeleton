<?php get_header(); ?>

<h1 class="h1">
  「<?php echo esc_html( get_search_query() ); ?>」の検索結果
</h1>
<p class="search_result_1__count"><?php echo esc_html( $wp_query->found_posts ); ?>件見つかりました。</p>

<?php if (have_posts()): ?>
  <div class="article_list_1">
    <?php while (have_posts()): the_post(); ?>
      <article class="article_list_1__item">
  <a class="article_list_1__link" href="<?php the_permalink(); ?>">
    <h2 class="article_list_1__title"><?php the_title(); ?></h2>
  </a>
  <p class="article_list_1__meta">
    <time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
  </p>
  <div class="article_list_1__excerpt">
    <?php the_excerpt(); ?>
  </div>
</article>

    <?php endwhile; ?>
  </div>
  <?php my_pagination(); ?>
<?php else: ?>
  <p>検索条件に一致する記事が見つかりませんでした。</p>
  <?php get_search_form(); ?>
<?php endif; ?>

<?php get_footer();
