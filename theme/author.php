<?php
// Set the Current Author Variable $curauth
$curauth = (isset($_GET['author_name'])) ? get_user_by('slug', $author_name) : get_userdata(intval($author));
// $curauth = get_the_author_meta( 'nicename', $author_id );
?>

<meta name="twitter:card" content="summary" />
<meta name="twitter:image:src" content="<?php echo get_avatar_url( $curauth->user_email ); ?>" />
</meta>

<meta property="og:description" content="Prayers and other works by <?php echo $curauth->display_name; ?> shared through the Open Siddur Project.">

<meta property="og:image" content="<?php echo get_avatar_url( $curauth->user_email , 'large '); ?>" />

<?php 
list($bfa_ata, $cols, $left_col, $left_col2, $right_col, $right_col2, $bfa_ata['h_blogtitle'], $bfa_ata['h_posttitle']) = bfa_get_options();
get_header(); 
extract($bfa_ata); 
global $bfa_ata_postcount;
?>



<style type="text/css">
.author-profile-card {
    background: #eee;
    border: 1px solid #ccc;
    padding: 20px;
    margin-bottom: 20px;
    display: block;
    overflow: auto;
    line-height: 1.5em;
}
.author-photo {
    float: left;
    text-align: left;
    padding: 10px;
}
</style></head>


<div class="author-profile-card">
    <div class="author-photo">
        <?php echo get_avatar( $curauth->user_email , '250 '); ?>
    </div>
    
<?php // Set the Current Author via the current method, find: https://developer.wordpress.org/reference/functions/get_the_author_meta/ ?>
<h2><?php echo get_the_author_meta( 'display_name', $author_id ); ?></h2>

    <?php echo $curauth->user_description; ?>
    <p />
    <a href="<?php echo $curauth->user_url; ?>"><?php echo $curauth->user_url; ?></a>
</div>
     
     <p />
 

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
<h3>
<a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link: <?php the_title(); ?>">
<?php the_title(); ?></a>
</h3>
<p class="posted-on">Contributed on: <?php the_time('d M Y'); ?> by 

		<?php if ( class_exists( 'coauthors_plus' ) ) { 		// Get the Co-Authors for the post
			$co_authors = get_coauthors();  // For each Co-Author, echo a wrapper div, their name, and their bio if they have one
			foreach ( $co_authors as $key => $co_author ) {
				$co_author_classes = array(
					'co-author-wrap',
					'co-author-number-' . ( $key + 1 ),
				);
				echo '<span class="' . implode( ' ', $co_author_classes ) . '">';
				if ($co_author->display_name !== $curauth->display_name) { echo '<a href="/profile/' . $co_author->user_nicename . '">'; }
				echo '<span class="co-author-display-name">' . $co_author->display_name . '</span></a> | </span>';
			}
		
		} echo '&#10087;';  ?>


</p>
 
<?php the_excerpt(); ?><hr />
 
<?php endwhile; 
 
// Previous/next page navigation.

the_posts_pagination();
 
 
else: ?>
<p><?php _e('No posts by this author.'); ?></p>
 
<?php endif; ?>

<?php get_footer(); ?>


