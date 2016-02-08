<?php

class SampleTest extends WP_UnitTestCase {
	function setUp() {
		$this->tags = $this->factory->term->create_many( 10, array( 'taxonomy' => 'post_tag' ) );
	}

	function test_hides_tag() {
		global $post;

		$post_ID = $this->factory->post->create();
		wp_set_object_terms( $post_ID, $this->tags, 'post_tag' );
		$hide = get_term( $this->tags[2], 'post_tag' );

		update_option( Oomph_Hidden_Tags::OPTION_NAME, array( 'tags' => array( $hide->name ) ) );

		$post = get_post( $post_ID );
		setup_postdata( $post );
		$result = get_the_tag_list();

		// The hidden tag should not show up in the post list
		// replace this with some actual testing code
		$this->assertTrue( strpos( $result, '>' . $hide->name . '<' ) === false, "Hidden tag is not found in tag list" );
	}

	function test_shows_hidden_tag_admin() {
		global $post;

		$user_ID = $this->factory->user->create();

		$current_user = wp_set_current_user( $user_ID );
		$current_user->add_cap( Oomph_Hidden_Tags::CAPABILITY );

		$post_ID = $this->factory->post->create();
		wp_set_object_terms( $post_ID, $this->tags, 'post_tag' );
		$hide = get_term( $this->tags[3], 'post_tag' );

		echo "hide="; print_r($hide);
		update_option( Oomph_Hidden_Tags::OPTION_NAME, array( 'tags' => array( $hide->name ) ) );

		$post = get_post( $post_ID );
		setup_postdata( $post );
		$result = get_the_tag_list();

		// The hidden tag should not show up in the post list
		// replace this with some actual testing code
		$this->assertTrue( strpos( $result, '>' . $hide->name . '<' ) != false, "Hidden tag is found in tag list" );
		$this->assertTrue( strpos( $result, " hidden-tag\"" ) != false, ".hidden-tag is found in class list" );
	}

	function test_hides_from_tag_cloud() {
		$post_ID = $this->factory->post->create();
		wp_set_object_terms( $post_ID, $this->tags, 'post_tag' );
		$hide = get_term( $this->tags[2], 'post_tag' );

		update_option( Oomph_Hidden_Tags::OPTION_NAME, array( 'tags' => array( $hide->name ) ) );

		$post = get_post( $post_ID );
		setup_postdata( $post );

		ob_start();
		$result = wp_tag_cloud();
		$cloud = ob_get_clean();

		// The hidden tag should not show up in the post list
		// replace this with some actual testing code
		$this->assertTrue( strpos( $result, '>' . $hide->name . '<' ) === false, "Hidden tag is not found in tag cloud" );
	}
}

