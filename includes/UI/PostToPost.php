<?php

namespace TenUp\ContentConnect\UI;

class PostToPost extends PostUI {

	public function setup() {
		add_filter( 'tenup_content_connect_post_relationship_data', array( $this, 'filter_data' ), 10, 2 );
	}

	public function filter_data( $data, $post ) {
		// Don't add any data if we aren't on the post type we're supposed to render for
		if ( $post->post_type !== $this->render_post_type ) {
			return $data;
		}

		// Determine the other post type in the relationship
		$other_post_type = $this->relationship->from == $this->render_post_type ? $this->relationship->to : $this->relationship->from;

		$final_posts = array();

		$args = array (
			'post_type' => $other_post_type,
			'relationship_query' => array(
				'name' => $this->relationship->name,
				'related_to_post' => $post->ID,
			),
		);

		if ( $this->sortable ) {
			$args['orderby'] = 'relationship';
		}

		$query = new \WP_Query( $args );

		if ( $query->have_posts() ) {
			while( $query->have_posts() ) {
				$post = $query->next_post();

				$final_posts[] = array(
					'ID' => $post->ID,
					'name' => $post->post_title,
				);
			}
		}

		// @Todo add pagination

		$data[] = array(
			'reltype' => 'post-to-post',
			'object_type' => 'post', // The object type we'll be querying for in searches on the front end
			'post_type' => $other_post_type, // The post type we'll be querying for in searches on the front end (so NOT the current post type, but the matching one in the relationship)
			'relid' => "{$this->relationship->from}_{$this->relationship->to}_{$this->relationship->name}", // @todo should probably get this from the registry
			'name' => $this->relationship->name,
			'labels' => $this->labels,
			'sortable' => $this->sortable,
			'selected' => $final_posts,
		);

		return $data;
	}

	public function handle_save( $relationship_data, $post_id ) {
		$this->relationship->replace_relationships( $post_id, $relationship_data['add_items'] );

		if ( $this->sortable ) {
			$this->relationship->save_sort_data( $post_id, $relationship_data['add_items'] );
		}
	}

}
