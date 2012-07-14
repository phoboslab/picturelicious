<?php

/*
	This class takes an array of images and sorts them into a grid view.
	This is the equivalent of the javascript gridSolve function in picturelicious.js
	with the difference that it runs server side of course.
*/

require_once( 'lib/config.php' );

class GridView {

	public $height = 0;
	protected $grid = array();
	protected $gridWidth = 20;

	public function __construct( $gridWidth = 20 ) {
		$this->gridWidth = $gridWidth;
		$this->grid = array_fill( 0, $this->gridWidth, 0 );
	}
	
	// solve expects an array with thumbnails with the keys name and score
	public function solve( &$thumbs ) {
		if( !is_array( $thumbs ) ) {
			return false;
		}
		
		// Order thumbs by importance (score), but leave the keys as they are
		uasort( $thumbs, array( $this, 'compareScore' ) );
		
		// How many thumbs get which CSS-Class? The first n thumbnails
		// get the biggest box etc.
		$total = count( $thumbs );
		$classCounts = array();
		$currentCount = 0;
		foreach( Config::$gridView['classes'] as $className => $gc ) {
			$currentCount += $gc['percentage'] * $total;
			$classCounts[$className] =  ceil( $currentCount );
		}
		
		// Assign a CSS-Class to each thumb
		$currentMax = -1;
		$currentClass = '';
		$j = 0;
		foreach( array_keys( $thumbs ) as $i ) {
			if( $j >= $currentMax ) {
				list( $currentClass, $currentMax ) = each( $classCounts );
			}
			$j++;
			$thumbs[$i]['class'] = $currentClass;
			$thumbs[$i]['thumb'] = Config::$absolutePath . Config::$images['thumbPath'] . 
				str_replace( '-', '/', substr( $thumbs[$i]['logged'], 0, 7 ) )
				.'/'. Config::$gridView['classes'][$currentClass]['dir']
				.'/'. $thumbs[$i]['thumb'];
		}

		// Sort the thumbs back in the order we got them
		ksort( $thumbs );

		// Now that every thumb has a CSS-Class, we can sort them into our grid
		for( $i = 0; $i < $total; $i++ ) {
			list( $x, $y ) = $this->insert( 
				Config::$gridView['classes'][$thumbs[$i]['class']]['width'], 
				Config::$gridView['classes'][$thumbs[$i]['class']]['height'] 
			);
			$thumbs[$i]['left'] = $x * Config::$gridView['gridSize'];
			$thumbs[$i]['top'] = $y * Config::$gridView['gridSize'];
		}
		
		// Calculate the final grid height
		for( $i = 0; $i < $this->gridWidth; $i++ ) {
			if( $this->grid[$i] > $this->height ) {
				$this->height = $this->grid[$i];
			}
		}
		
		return true;
	}
	
	// Callback for uasort()
	protected function compareScore( $a, $b ) {
		return $a['score'] > $b['score'] ? 
			1 : 
			( $a['score'] == $b['score'] &&  $a['votes'] > $b['votes'] ?
				1 :
				-1
			);
	}
		
	protected function insert( $boxWidth, $boxHeight ) {
		
		// Height of the grid
		$maxHeight = 0; 
		
		// Height of the grid at the last position
		$currentHeight = $this->grid[0];
		
		// Find free spots within the grid and collect them in an arry
		// A spot is a area in the grid with equal height
		$spotWidth = 0; // Width of the spot in grid units
		$spotLeft = 0; // Position in the grid (relative to left border)
		$freeSpots = array();
		
		for( $i = 0; $i < $this->gridWidth; $i++ ) {

			// Height is the same as at the last position?
			// -> increase the size of this spot
			if( $currentHeight == $this->grid[$i] ) {
				$spotWidth++;
			}

			// The height is different from the last position, and our current spot 
			// is wider than 0
			if( ( $currentHeight != $this->grid[$i] || $i+1 == $this->gridWidth) && $spotWidth > 0 ) {
				$freeSpots[] = array( 'width' =>$spotWidth, 'left' => $spotLeft, 'height' => $currentHeight );
				$spotWidth = 1;
				$spotLeft = $i;
				
				// Make sure we don't miss the last one
				if( $currentHeight != $this->grid[$i] && $i+1 == $this->gridWidth ) {
					$freeSpots[] = array( 'width' =>$spotWidth, 'left' => $spotLeft, 'height' => $this->grid[$i] );
				}
			}
						
			$currentHeight = $this->grid[$i];
			$maxHeight = max( $maxHeight, $this->grid[$i] );
		}
			
		// Loop through all found spots and rate them, based on their size and height
		// This way the smallest possible spot in the lowest possible height is filled
		$targetHeight = 0;
		$targetLeft = 0;
		
		// Default spot (left border) if we don't find a better one
		for( $i = 0; $i < $boxWidth; $i++ ) {
			if( $this->grid[$i] > $targetHeight ) {
				$targetHeight = $this->grid[$i];
			}
		}		
		
		$bestScore = -1;
		foreach( array_keys( $freeSpots ) as $i ) {
			
			// Difference of the height of this spot to the total height of the grid
			$heightScore = ( $maxHeight - $freeSpots[$i]['height'] );
			
			// Relation of the required and the available space
			$widthScore = $boxWidth / $freeSpots[$i]['width'];
			
			// The score for this spot is calculated by these both criteria
			$score = $heightScore * $heightScore + $widthScore * 2;
			
			// Is the score for this spot higher than for the last one we found?
			if( $freeSpots[$i]['width'] >= $boxWidth && $score > $bestScore ) {
				$targetHeight = $freeSpots[$i]['height'];
				$targetLeft = $freeSpots[$i]['left'];
				$bestScore = $score;
			}
		}

		$newHeight = $targetHeight + $boxHeight;
		
		// Adjust grid height
		for( $j = 0; $j < $boxWidth; $j++ ) {
			$this->grid[$targetLeft + $j] = $newHeight;
		}
			
		return array( $targetLeft, $targetHeight );
	}
}

?>