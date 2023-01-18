<?php

class wp_query_arguments {


	public static function arguments(string $num, int $paged, string $typeofEvents): array
	{

		$dateNow = date_create('now');
		$dateNow = date_format($dateNow, "Y-m-d");

		$args2 = array(
			'post_type' => 'post',
			'posts_per_page' => $num,
			'paged'          => $paged,
			'order'          => 'DESC',
			'meta_key' => 'eventdate',
			'meta_query' => array(
				array(
					'key' => 'status',
					'value' => $typeofEvents,//ищу  события по статусу
				),

				'eventdate_clause' => array(
					'key' => 'eventdate',
					'value' => $dateNow,
					'compare' => '>=',
					'type' => 'DATE',
				),
			),
			'orderby' => array(
				'eventdate_clause' => 'ASC',
			),
		);


		return $args2;

	}





}