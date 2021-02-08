<?php

/*
 * BlackJack PHP
 * Benjamin Venezia / Teva Keo
 * Crea Dev4 Février 2021
 */

session_start();

// Initialisation des valeurs uniquement lors d'une nouvelle partie
if( empty($_SESSION['money']) ):
	// Variables
	$_SESSION['playername'] = 'invité';
	$_SESSION['player_cards'] = $_SESSION['croupier_cards'] = array();
	$_SESSION['player_total'] = $_SESSION['croupier_total'] = 0;
	$_SESSION['money'] = 20;
	$_SESSION['bet'] = 5;
	$_SESSION['gamestate'] = 0;
	$_SESSION['skip_joueur'] = $_SESSION['skip_croupier'] = 0;

	$colors = array( 'heart', 'diam', 'club', 'spade' );

	// Pour chaque couleur
	foreach ( $colors as $color )
	{
		$_SESSION['deck'][] = array( 'value' => '2', 'color' => $color, 'points' => 2 );
		$_SESSION['deck'][] = array( 'value' => '3', 'color' => $color, 'points' => 3 );
		$_SESSION['deck'][] = array( 'value' => '4', 'color' => $color, 'points' => 4 );
		$_SESSION['deck'][] = array( 'value' => '5', 'color' => $color, 'points' => 5 );
		$_SESSION['deck'][] = array( 'value' => '6', 'color' => $color, 'points' => 6 );
		$_SESSION['deck'][] = array( 'value' => '7', 'color' => $color, 'points' => 7 );
		$_SESSION['deck'][] = array( 'value' => '8', 'color' => $color, 'points' => 8 );
		$_SESSION['deck'][] = array( 'value' => '9', 'color' => $color, 'points' => 9 );
		$_SESSION['deck'][] = array( 'value' => '10', 'color' => $color, 'points' => 10 );
		$_SESSION['deck'][] = array( 'value' => 'V', 'color' => $color, 'points' => 10 );
		$_SESSION['deck'][] = array( 'value' => 'D', 'color' => $color, 'points' => 10 );
		$_SESSION['deck'][] = array( 'value' => 'R', 'color' => $color, 'points' => 10 );
		$_SESSION['deck'][] = array( 'value' => 'As', 'color' => $color, 'points' => 11 );
	}
endif;

// Initialisation des objets

class Joueur {
	// Init
	var $nom ;
	var $jetons ;
	var $bet ;
	var $cards ;
	var $skip;
	var $state;

	function __construct($nom, $jetons, $bet, $cards, $state, $skip){
		$this->nom = $nom;
		$this->jetons = $jetons;
		$this->bet = $bet;
		$this->cards = $cards;
		$this->state = $state;
		$this->skip = $skip;
	}

	// Getters
	function get_nom(){
		return $this->nom;
	}

	function get_jetons(){
		return $this->jetons;
	}

	function get_bet(){
		return $this->bet;
	}

	function get_cards(){
		return $this->cards;
	}

	function get_points(){
		$points = 0;
		foreach( $this->cards as $card ):
			$points += $card['points'];
		endforeach;
		return $points;
	}

	function get_skip(){
		return $this->skip;
	}

	function gamestate(){
		return $this->state;
	}
	
	// Setters
	function reset_nom(){
		$this->nom = 'invité';
		 $_SESSION['playername'] = 'invité';
	}

	function change_nom( $playername ){
		$this->nom = $playername;
		$_SESSION['playername'] = $playername;
	}

	function reset_cards( $type ){
		$this->cards = array();
		if( $type == 'joueur' ):
			$_SESSION['player_cards'] = array();
		elseif( $type == 'croupier' ):
			$_SESSION['croupier_cards'] = array();
		endif;
	}

	function reset_skip(){
		$this->skip = 0;
		$_SESSION['skip_joueur'] = 0;
		$_SESSION['skip_croupier'] = 0;
	}

	function reset_bet(){
		$this->bet = 5;
		$_SESSION['bet'] = 5;
	}

	function reset_jetons(){
		$this->jetons = 20;
		$_SESSION['money'] = 20;
	}

	function double_bet(){
		if( $this->bet * 2 > $this->jetons ):
			return false;
		else:
			$this->bet *= 2;
			$_SESSION['bet'] *= 2;
			return true;
		endif;
	}

	function gain_jetons( $jetons ){
		$this->jetons += $jetons;
		$_SESSION['money'] = $this->jetons;
	}

	function perte_jetons( $jetons ){
		$this->jetons -= $jetons;
		$_SESSION['money'] = $this->jetons;
	}

	function skip_turn( $type ){
		if( $type == 'joueur' ):
			$this->passe_tour = 1;
			$_SESSION['skip_joueur'] = 1;
		elseif( $type == 'croupier'):
			$this->passe_tour = 1;
			$_SESSION['skip_croupier'] = 1;
		endif;
	}

	function edit_gamestate( $state ){
		if( $state == 'win' ):
			$this->state = 1;
			$_SESSION['gamestate'] = 1;
		elseif( $state == 'lose' ):
			$this->state = 2;
			$_SESSION['gamestate'] = 2;
		endif;
	}

	function reset_gamestate(){
		$this->state = 0;
		$_SESSION['gamestate'] = 0;
	}

	// Functions
	function add_card( $card, $player ){
		array_push( $this->cards, $card );
		if( $player == 'joueur' ):
			array_push($_SESSION['player_cards'], $card);
		elseif( $player == 'croupier' ):
			array_push($_SESSION['croupier_cards'], $card);
		endif;
	}

	function check_bet( $bet ){
		if( $bet > $this->jetons ):
			return false;
		else:
			$this->bet = $bet;
			$_SESSION['bet'] = $bet;
			return true;
		endif;
	}

	function count_cards(){
		return count( $this->cards );
	}

	function has_ace(){
		foreach( $this->cards as $card ):
			if( in_array( "As", $card ) ):
				return true;
			endif;
		endforeach;
		return false;
	}
}

class Deck {
	// Init
	var $cardlist;
	var $colors;

	function __construct($deck){
		$this->cardlist = $deck;
		$this->colors = array( 'heart', 'diam', 'club', 'spade' );
	}

	// Getter
	function get_deck(){
		return $this->cardlist;
	}

	// Functions
	function draw_card(){
		$rand = rand( 0, count( $this->cardlist ) - 1 );
		$card = $this->cardlist[$rand];
		unset( $this->cardlist[$rand] );
		$this->cardlist = array_values( $this->cardlist );

		$_SESSION['deck'] = $this->cardlist;
		return $card;
	}

	function shuffle_deck(){
		$this->cardlist = array();
		foreach( $this->colors as $color ):
			$this->cardlist[] = array( 'value' => '2', 'color' => $color, 'points' => 2 );
			$this->cardlist[] = array( 'value' => '3', 'color' => $color, 'points' => 3 );
			$this->cardlist[] = array( 'value' => '4', 'color' => $color, 'points' => 4 );
			$this->cardlist[] = array( 'value' => '5', 'color' => $color, 'points' => 5 );
			$this->cardlist[] = array( 'value' => '6', 'color' => $color, 'points' => 6 );
			$this->cardlist[] = array( 'value' => '7', 'color' => $color, 'points' => 7 );
			$this->cardlist[] = array( 'value' => '8', 'color' => $color, 'points' => 8 );
			$this->cardlist[] = array( 'value' => '9', 'color' => $color, 'points' => 9 );
			$this->cardlist[] = array( 'value' => '10', 'color' => $color, 'points' => 10 );
			$this->cardlist[] = array( 'value' => 'V', 'color' => $color, 'points' => 10 );
			$this->cardlist[] = array( 'value' => 'D', 'color' => $color, 'points' => 10 );
			$this->cardlist[] = array( 'value' => 'R', 'color' => $color, 'points' => 10 );
			$this->cardlist[] = array( 'value' => 'As', 'color' => $color, 'points' => 11 );
		endforeach;
		$_SESSION['deck'] = $this->cardlist;
	}
}

// Functions
function reset_game( $joueur , $croupier , $deck ){
	$joueur->reset_skip();
	$joueur->reset_gamestate();
	$joueur->reset_jetons();
	$joueur->reset_bet();
	$joueur->reset_cards( 'joueur' );
	$joueur->reset_nom();
	$croupier->reset_cards( 'croupier' );
	$deck->shuffle_deck();
}

function outcome_checker( $player, $type , $joueur ){

	if( ( $player->get_points() == 21 && $type == 'joueur' ) || ( $player->get_points() > 21 && $type == 'croupier' ) ):
		$gains = $player->get_bet() * 1.5;
		$player->gain_jetons( $gains );
		$step = 2;
		$joueur->edit_gamestate( 'win' );

	elseif( ( $player->get_points() > 21 && $type == 'joueur' ) || ( $player->get_points() == 21 && $type == 'croupier' ) ):
		$player->perte_jetons( $player->get_bet() );
		$step = 2;
		$joueur->edit_gamestate( 'lose' );

	endif;
}

// Objetcs init
$joueur = new Joueur( $_SESSION['playername'], $_SESSION['money'], $_SESSION['bet'], $_SESSION['player_cards'], $_SESSION['gamestate'], $_SESSION['skip_joueur'] );
$croupier = new Joueur( 'Risicroupier', null, null, $_SESSION['croupier_cards'], null, $_SESSION['skip_croupier'] );
$deck = new Deck( $_SESSION['deck'] );

// En cas de reset (à voir si on garde)
if( isset( $_REQUEST['reset'] ) ):
	reset_game( $joueur , $croupier , $deck );
	$step = 1;
endif;

// AMPP Fix
if( isset( $_REQUEST['playername'] ) ):
	$joueur->change_nom( $_REQUEST['playername'] );
endif;

// On défini l'étape en cours
$step = isset( $_REQUEST['step'] ) ? (int)$_REQUEST['step'] : 1;

// Déroulement de la partie
switch ( $step )
{
	// Tour d'initialisation
	case 2:

		if( isset( $_REQUEST['replay'] ) ):
			$joueur->reset_cards( 'joueur' );
			$croupier->reset_cards( 'croupier' );
			$deck->shuffle_deck();
			$joueur->reset_gamestate();
			$joueur->reset_bet();
		endif;
	
		// Le joueur tire 2 cartes 
		while( $joueur->count_cards() < 2 ):
			$new_card = $deck->draw_card();
			$joueur->add_card( $new_card , 'joueur' );
		endwhile;		
			
		// Tirage de la première carte du croupier
		while( $croupier->count_cards() < 1 ):
			$new_card = $deck->draw_card();
			$croupier->add_card( $new_card , 'croupier' );
		endwhile;

		// Si le joueur à 21 points, il gagne automatiquement et récupère immédiatement 1,5 fois sa mise
		outcome_checker( $joueur , 'joueur' , $joueur );

		break;
		
	// Tour de jeu
	case 3:
	
		if( $joueur->gamestate() == 0 ):

			// Le joueur souhaite doubler sa mise
			if( isset($_REQUEST['double_bet']) ):
				if( $joueur->double_bet() == false ):
					$error_message = "Vous ne pouvez pas doubler votre mise !";
				else:
					//$joueur->double_bet();
				endif;
			endif;
			
			// Le joueur demande une nouvelle carte
			if( isset($_REQUEST['new_card']) && $joueur->count_cards() < 3 ):
				$new_card = $deck->draw_card();
				$joueur->add_card( $new_card , 'joueur' );
			endif;
			
			// Si le joueur a 21 points, il gagne automatiquement et récupère immédiatement 1,5 fois sa mise | Si il a + il perds
			outcome_checker( $joueur , 'joueur' , $joueur );
		endif;
		$step = 2;
		 
		break;
		
	// Tour du croupier
	case 4:
		
		// Tant qu'il a moins de 17, il tire une carte
		while( $croupier->get_points() < 17 ):
			$new_card = $deck->draw_card();
			$croupier->add_card( $new_card , 'croupier' );
		endwhile;	

		// S'il a 17 mais avec un As en main, il tire une carte 
		if( $croupier->get_points() == 17 && $croupier->has_ace() == true ):
			$new_card = $deck->draw_card();
			$croupier->add_card( $new_card , 'croupier' );
		endif;

		// Plus de 17, il passe son tour
		if( $croupier->get_points() > 17):
			// pass
		endif;
		
		// Si le croupier a plus de 21 points, le joueur récupère immédiatement 1,5 fois sa mise 
		// Si le croupier a 21 points, le joueur perd sa mise 
		outcome_checker( $croupier, 'croupier' , $joueur );
		
		// Si le joueur a passé et que le croupier a passé, le joueur récupère sa mise
		if( $joueur->get_skip() == 1 && $croupier->get_skip() == 1 ):

		endif;

		
		// Après cette étape, soit on recommence l'étape 3 ou on recommence une partie (A FAIRE)
		
		break;
}

?>	
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="robots" content="noindex">
	<link href="https://fonts.googleapis.com/css?family=Luckiest+Guy" rel="stylesheet">
    <link href="./styles.css" rel="stylesheet">
	<script src="https://kit.fontawesome.com/0d2059c859.js" crossorigin="anonymous"></script>
	<script src="./index.js"></script>
	<title>Black Jack</title>
</head>

<body>
	<div class="wrapp">
		<h1>Black Jack</h1>
		<div id="money" class="menu-text">
			<svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 		     viewBox="0 0 221 221" style="enable-background:new 0 0 221 221;" xml:space="preserve">
	 		 <path fill="#F8B317" d="M186.5,189v-81.863c0-33.075-20.935-61.307-50.379-71.96L152.934,0H67.476l17.589,34.888
	 		    C55.178,45.302,33.5,73.746,33.5,107.137V189h-10v32h174v-32H186.5z M130.025,154.81c-3.246,2.882-7.261,4.884-11.93,5.946
	 		    l0.078,10.615l-15.283,0.039l-0.069-10.619c-4.323-1.043-7.482-3.125-11.231-6.228c-5.107-4.401-7.767-9.543-7.938-15.311
	 		    l-0.003-0.116l0.382-4.872l14.708-0.052l0.115,3.101c0.057,3.663,1.111,6.311,3.222,8.141c2.138,1.918,3.981,2.881,8.196,2.881
	 		    c4.174-0.034,7.408-1.036,9.595-2.98c2.086-1.838,3.097-4.417,3.097-7.887c0-2.727-0.918-4.722-2.888-6.279
	 		    c-2.307-1.809-6.488-3.434-12.421-4.825c-7.31-1.701-12.923-4.348-16.682-7.868c-4.034-3.828-6.09-8.64-6.122-14.311
	 		    c-0.042-6.497,2.492-11.903,7.53-16.066c2.879-2.393,6.249-4.092,10.033-5.061l-0.071-9.301l15.279-0.106l0.039,8.974
	 		    c4.204,0.87,6.996,2.602,10.163,5.182c4.841,4.049,7.47,9.579,7.835,16.462l0.118,3.311l-14.771,0.073l-0.435-2.727
	 		    c-0.468-3.488-1.562-6.057-3.259-7.546c-1.796-1.575-3.052-2.38-6.181-2.38h-0.207c-3.646,0-6.548,0.989-8.415,2.581
	 		    c-1.811,1.515-2.601,3.593-2.578,6.422c0,2.327,0.774,4.087,2.366,5.396c1.33,1.12,4.193,2.733,10.392,4.156
	 		    c8.186,1.924,14.456,4.802,18.645,8.556c4.436,4.014,6.682,9.092,6.682,15.1C138.059,144.318,135.37,150.243,130.025,154.81z"/>
	 		 </svg>
	 		 
			Cagnotte: <strong>CHF <?php echo $joueur->get_jetons() ?>.-</strong>
			<br>Mise: <strong>CHF  <?php echo $joueur->get_bet() ?>.-</strong>
		</div>

		<div class="top-left menu-text">
		<a href="?reset" class="reset"><i class="fas fa-power-off"></i></a>
		<?php echo '&nbsp; Bonjour ' . $joueur->get_nom() . ' !'; ?>
		</div>
		
		<?php 
		switch ( $step )
		{ 
			case 1: // CAS 1 
				
		?>
				<h2>Bienvenue à la table CREA !</h2>
				<form method="post" action="index.php">
					<input type="hidden" name="step" value="2" /> 

					<label for="name">Pseudonyme</label>
					<input type="text" name="playername" /> 
					<br><br>
					<label for="bet">Votre mise:</label>
					<select name="bet" id="bet">
					<?php 
						if ( $joueur->get_jetons() == 2 ) echo '<option value="2">CHF 2.-</option>';
						if ( $joueur->get_jetons() >= 5 ) echo '<option value="5">CHF 5.-</option>';
						if ( $joueur->get_jetons() >= 10 ) echo '<option value="10">CHF 10.-</option>';
						if ( $joueur->get_jetons() >= 25 ) echo '<option value="25">CHF 25.-</option>';
						if ( $joueur->get_jetons() >= 50 ) echo '<option value="50">CHF 50.-</option>';
						if ( $joueur->get_jetons() >= 100 ) echo '<option value="100">CHF 100.-</option>';
						if ( $joueur->get_jetons() >= 500 ) echo '<option value="500">CHF 500.-</option>';
					?>
						<option value="all">Totalité (tapis)</option>
					</select>
					<button type="submit">Valider</button>
				</form>

			<?php
				break;
			
			case 2:			
			?>

				<?php if( $joueur->gamestate() == 0 ): ?>
				<div class="player">
					<h2>Croupier <span><?php echo $croupier->get_points() ?></span></h2>
					<?php 
						foreach ( $croupier->get_cards() as $card ) //affichage des cartes du croupier
							echo '<div class="card card-' . $card['color'] . '">' . $card['value'] . '</div>';
						
					?>
				</div>
				<div class="player">
					<h2>Joueur <span><?php echo $joueur->get_points() ?></span></h2>
					<?php 
						foreach ( $joueur->get_cards() as $card ) //affichage des cartes du joueur
							echo '<div class="card card-' . $card['color'] . '">' . $card['value'] . '</div>';
						
					?>
				</div> 

				<?php if( !empty($error_message) ): ?>
					<h3><?php echo $error_message ?></h3>
				<?php endif; ?>

				<div class="actions">
					
					<?php if( count( $joueur->get_cards() ) !== 3 ): ?>
					<a href="?step=3&new_card">Nouvelle carte</a>
					<?php endif; ?>

					<?php if(  $joueur->get_bet() * 2  < $joueur->get_jetons() ): ?>
					<a href="?step=3&double_bet">Doubler la mise !</a>
					<?php endif; ?>
					
					<?php if( $joueur->gamestate() == 0 ): ?>
					<a href="#">Passer son tour</a>
					<?php endif; ?>

				</div>
				<?php endif; ?>

			<?php
				if( $joueur->gamestate() !== 0 ):
			?>

			<div class="result-container">
					
					<?php if( $joueur->gamestate() == 1 ): ?>
					<img src="https://tova.dev/static/bravo.png" style="width:100px;margin-bottom:20px">
					<?php elseif( $joueur->gamestate() == 2 ): ?>
					<img src="https://tova.dev/static/pabravo.png" style="width:100px;margin-bottom:20px">
					<?php endif; ?>

					<span>Vous avez <?php if( $joueur->gamestate() == 1 ): echo 'gagné'; elseif( $joueur->gamestate() == 2 ): echo 'perdu'; endif; ?> la partie </span>
					
					<div class="player">
					<h2>Croupier <span><?php echo $croupier->get_points() ?></span></h2>
					<?php 
						foreach ( $croupier->get_cards() as $card ) //affichage des cartes du croupier
							echo '<div class="card card-' . $card['color'] . '">' . $card['value'] . '</div>';
						
					?>
					</div>

					<div class="player">
						<h2>Joueur <span><?php echo $joueur->get_points() ?></span></h2>
						<?php 
							foreach ( $joueur->get_cards() as $card ) //affichage des cartes du joueur
								echo '<div class="card card-' . $card['color'] . '">' . $card['value'] . '</div>';
							
						?>
					</div>

					<div class="actions">			
						<a href="?step=2&replay">Rejouer</a>
					</div>
			</div>

			<?php
				endif;

				break;
	} 
			?>

		<!-- JS Animation Background -->
		<div class="anim">
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1100 800"><g fill="none" fill-rule="evenodd"><path stroke="#31B495" d="M781.02 488.77v69.78c0 1.08-.88 1.96-1.97 1.96l-135.12-.04c-1.09 0-2.6.62-3.38 1.39l-39.23 38.96a5.52 5.52 0 0 1-3.37 1.4h-75.38a1.97 1.97 0 0 1-1.97-1.97v-33.5"/><path stroke="#F4D21F" d="M674.88 355.57l45.54-45.24a5.42 5.42 0 0 0 1.39-3.35l-.06-10.38c0-1.08-.63-2.58-1.4-3.35l-43.38-43.07a1.94 1.94 0 0 1 0-2.77l82.83-82.25a5.52 5.52 0 0 1 3.37-1.4l44.94.1c1.08 0 2.6-.62 3.37-1.37L952.5 22.65"/><path stroke="#1AACA8" d="M507-76.68v265.47a4 4 0 0 0 4 3.99H566c1.08 0 1.97.88 1.97 1.96v147.5c0 1.08-.63 2.59-1.4 3.35l-47.9 47.4a5.45 5.45 0 0 0-1.4 3.34c0 2.25.64 3.76 1.4 4.53l53.82 53.26c.77.76 1.76 1.39 2.19 1.39.43 0 .79.88.79 1.96v70.17c0 1.07-.89 1.96-1.97 1.96l-85.81-.04c-1.09 0-2.6.62-3.38 1.39l-1.55 1.54a5.52 5.52 0 0 1-3.38 1.4h-9.29"/><path stroke="#1F8C43" d="M8 127.82v391.06a4.04 4.04 0 0 0 4 4.04L140.8 524"/><path stroke="#1AA5D0" d="M894.01 374l49.8-49.44a5.52 5.52 0 0 1 3.37-1.4h92.41c1.09 0 2.6.63 3.38 1.4l27.18 26.99"/><path stroke="#1AA5D0" d="M894.01 374l49.8-49.44a5.52 5.52 0 0 1 3.37-1.4h92.41c1.09 0 2.6.63 3.38 1.4l27.18 26.99"/><path stroke="#1F8C43" d="M755.16 213.9l70.82.04c1.08 0 2.6-.63 3.37-1.4l91.61-90.97a5.52 5.52 0 0 1 3.37-1.39h77.07l-71.29-72.13a5.45 5.45 0 0 1-1.4-3.35V16.87"/><path stroke="#9DCA40" d="M261.78-52.44l11.16 11.08c.77.77 1.4 2.28 1.4 3.35V-5L156.7 111.03l-85.4 84.8a5.45 5.45 0 0 0-1.4 3.35v100.67c0 1.08.89 1.96 1.97 1.96h50.4c1.09 0 1.98.88 1.98 1.96l.07 26.92c0 1.07.9 1.96 1.98 1.96l335.73.13c1.09 0 1.98.88 1.98 1.96v36.79l-42.99 43.78a5.52 5.52 0 0 1-3.37 1.4H385.2"/><path stroke="#DA5A98" d="M564.8 549.64v17.76c0 1.08-.64 2.59-1.4 3.35L382.28 750.6a5.52 5.52 0 0 1-3.38 1.39h-109.1c-1.09 0-1.97.88-1.97 1.96v23.37c0 1.07-.9 1.96-1.98 1.96h-83.54c-1.08 0-1.97.88-1.97 1.96v45.8c0 1.07.89 1.95 1.97 1.95h29.89c1.08 0 1.97.88 1.97 1.96v51.07c0 1.08.63 2.59 1.4 3.35l10.32 10.25c.77.76 2.29 1.39 3.37 1.39h111.77c1.09 0 1.34.62.57 1.39M482.82 656H630.9"/><path stroke="#E5683E" d="M440.53 245.87l-31.7 31.48a5.52 5.52 0 0 1-3.37 1.39h-62.37c-1.09 0-2.6.62-3.38 1.39l-2.68 3.66-264.59.02c-1.08 0-2.6-.63-3.37-1.4l-47.3-46.97a5.52 5.52 0 0 0-3.37-1.39h-57.47l-1.12-34.61c0-1.08-.63-2.59-1.4-3.35l-66.54-65.94"/><path stroke="#9F83B6" d="M705.31 221.73h7.83c1.09 0 2.6.63 3.37 1.4l45.8 45.6c.78.76 1.4 2.27 1.4 3.35v13.94c0 1.08.46 1.96 1.03 1.98.56 0 1.03.9 1.03 1.98v10.77l-.15 110.84c0 1.08-.89 1.96-1.98 1.96H628.32c-1.08 0-2.6-.63-3.37-1.4l-12.2-12.12a5.52 5.52 0 0 0-3.38-1.39h-46.18a2 2 0 0 0-2 1.96l-.17 26.74c0 1.08-.63 2.59-1.4 3.35l-8.82 8.76a5.52 5.52 0 0 1-3.37 1.39l-26.65-.06c-1.09 0-2.6.62-3.38 1.39l-48.1 47.78a5.52 5.52 0 0 1-3.38 1.39h-16.37l-79.45-.02c-1.09 0-2.6.63-3.36 1.39L220.71 639.06a5.47 5.47 0 0 1-3.35 1.4H31.06"/><path stroke="#BC6D21" d="M145.43 99.41L289.6 243.5c.77.76 2.29 1.39 3.37 1.39h146.76c1.09 0 2.6.62 3.38 1.39l31.93 31.71c.77.77 1.4 2.27 1.4 3.35V474.1c0 1.08-.63 2.59-1.4 3.35l-7.6 7.54a5.52 5.52 0 0 1-3.36 1.4h-20.62l-20.67 20.97-2.78 2.78L289.37 640a5.45 5.45 0 0 0-1.4 3.35l.16 177.85"/><path stroke="#DA1817" d="M318.82 380.62h94.88c1.09 0 2.6.63 3.38 1.39l14.97 14.87c.77.76 2.29 1.39 3.37 1.39h72.99c1.08 0 2.6.63 3.35 1.39l58.57 58.53c.77.77 2.27 1.4 3.35 1.4h103.37c1.08 0 1.97-.89 1.97-1.97v-14.7c0-1.09-.89-1.97-1.97-1.97l-6.7.02H630.1a1.97 1.97 0 0 1-1.97-1.96v-57c0-1.08-.63-2.59-1.4-3.35l-14.58-14.48a5.45 5.45 0 0 1-1.4-3.35v-17.3c0-1.07-.63-2.58-1.4-3.34L597 327.92a5.52 5.52 0 0 0-3.37-1.39h-17.4c-1.09 0-2.6-.62-3.38-1.39l-41.8-41.5a5.52 5.52 0 0 0-3.37-1.4h-41.34"/><path stroke="#9F9FA0"/><path stroke="#74BB63" d="M855.2 194.4h59.84c1.09 0 1.97.89 1.97 1.96v28.74c0 1.08.64 2.59 1.4 3.35l50.96 50.6c.77.76 1.4 2.27 1.4 3.35v101.47l105.2 104.27"/><path stroke="#DA5A98" d="M638.46 305.73L651 293.29c.77-.74.77-2 0-2.76l-31.35-31.13c-.76-.74-.76-2 0-2.76l18.53-18.4a5.52 5.52 0 0 1 3.37-1.39l160.41-.2 423.37 1.2c1.08 0 1.97.89 1.97 1.96v71.5"/><path stroke="#BC6D21" d="M438.61 486.03h-18.54c-1.08 0-2.6-.63-3.37-1.4l-74.94-74.41a5.52 5.52 0 0 0-3.37-1.4h-38.57c-1.08 0-2.6-.62-3.37-1.38l-47-46.68-36.58-.04-57 71.59a5.45 5.45 0 0 0-1.4 3.35v23.9"/><path stroke="#74BB63" d="M882.06 358.97l-46.92 46.6a5.52 5.52 0 0 1-3.38 1.39h-94.64c-1.09 0-2.6-.63-3.38-1.4l-30.6-30.33a5.52 5.52 0 0 0-3.36-1.4l-34.94.04c-1.08 0-2.6.63-3.37 1.4l-29.57 29.36a5.52 5.52 0 0 1-3.37 1.39l-14.55-14.35a5.63 5.63 0 0 0-3.42-1.4l-156.97-.25c-1.11 0-2.65.63-3.43 1.4l-27.85 27.61a5.52 5.52 0 0 1-3.38 1.4H-23.82l-88.65.2-12.44 12.35"/><path stroke="#2283BC" d="M292.9 643.74l59.56-59.12a5.52 5.52 0 0 1 3.37-1.39h23.93c1.08 0 2.6-.63 3.37-1.39l46.53-46.21a5.52 5.52 0 0 1 3.38-1.4h33.53l153.67-.01c1.08 0 1.97-.88 1.97-1.96V420.01c0-1.07-.63-2.58-1.4-3.35l-38.64-38.37a5.45 5.45 0 0 1-1.4-3.35v-51.52c0-1.08-.64-2.59-1.4-3.35L468.91 210.39a5.52 5.52 0 0 0-3.38-1.4l-180.49.2"/><path stroke="#DA5A98" d="M484.13 548.71h-37.09c-1.08 0-2.6.63-3.37 1.4l-69.02 68.54c-.77.76-.77 2 0 2.76l28.09 27.78c.77.76 2.29 1.39 3.37 1.39h62.41"/><path stroke="#31B495" d="M520.82 561.7v-4.74c0-1.08-.89-1.96-1.98-1.96h-13.21c-1.09 0-2.6-.62-3.37-1.39l-43.36-42.88a5.45 5.45 0 0 1-1.4-3.35v-190.4c0-1.08.63-2.6 1.4-3.36l20.89-20.74a5.45 5.45 0 0 0 1.4-3.35v-95.4c0-1.08-.63-2.58-1.4-3.35L292.4 4.7l-.6-40.88c0-1.08-.62-2.58-1.4-3.35L278.8-51.07"/><path stroke="#1EB2D8" d="M275.76 745h99.28c1.09 0 2.6-.63 3.38-1.4l174.33-172.75a5.52 5.52 0 0 1 3.38-1.4h46.75c1.08 0 2.6-.62 3.35-1.38l51.47-51.46a5.42 5.42 0 0 0 1.38-3.35V311.29c0-1.07-.63-2.58-1.4-3.35l-51.84-51.3a5.52 5.52 0 0 0-3.38-1.4h-17.95a1.97 1.97 0 0 1-1.97-1.95v-44.47c0-1.07-.89-1.96-1.97-1.96h-88.63a1.97 1.97 0 0 1-1.97-1.96v-19.2c0-1.07-.64-2.58-1.4-3.34L309.87 4.92"/><path stroke="#F4D21F" d="M1002.65 123.83H926.5c-1.08 0-2.6.62-3.37 1.39l-92.28 91.46a5.52 5.52 0 0 1-3.37 1.39l-131.87-.08c-1.09 0-2.6.63-3.37 1.37l-51.9 51.19c-.77.76-.77 2 0 2.76l21.22 21.1c.77.76 1.4 2.27 1.4 3.35v15.69"/><path stroke="#BE2F39" d="M672.51 437.64h54.25c1.08 0 2.6.63 3.37 1.4l49.04 48.7c.77.76 2.29 1.38 3.37 1.38h45.16c1.08 0 2.6-.62 3.37-1.39L914.39 405a5.52 5.52 0 0 1 3.37-1.4h42.22c1.08 0 2.6.63 3.37 1.4l100.8 100.1"/><path stroke="#E5683E" d="M672.51 434.31h55.63c1.08 0 2.6.63 3.37 1.4l49.14 48.8c.77.76 2.29 1.38 3.37 1.38l41.9-.04c1.08 0 2.6-.62 3.37-1.39l62.08-61.68a5.45 5.45 0 0 0 1.4-3.35l-.1-268.18c0-1.08-.63-2.59-1.4-3.35l-99.8-99.28a5.52 5.52 0 0 0-3.37-1.39H422.62c-1.08 0-2.6.63-3.37 1.4L260.28 206.3a5.52 5.52 0 0 1-3.38 1.39H127.3c-1.08 0-2.6.62-3.37 1.39l-36.71 36.45a5.45 5.45 0 0 0-1.4 3.35v185.1"/><path stroke="#1EB2D8" d="M410.1 713.73l17.53 17.42c.77.76 2.29 1.39 3.37 1.39h42.02c1.09 0 2.6-.63 3.37-1.4l26.02-25.83 123.2-.31"/><path/><path stroke="#2283BC" d="M307.34 907.08c.77-.77.52-1.4-.57-1.4H108.29a1.97 1.97 0 0 1-1.98-1.95V743.59c0-1.08.9-1.96 1.98-1.96h264.38c1.09 0 2.6-.63 3.38-1.4l23.75-23.58c.77-.76.77-2 0-2.76l-80.84-80.1c-.77-.76-.51-1.4.57-1.4h137.53c1.09 0 2.6-.62 3.38-1.38l53.63-53.26a5.52 5.52 0 0 1 3.37-1.4l88.57-.2c1.09 0 2.6-.62 3.38-1.38l55.6-55.22a5.45 5.45 0 0 0 1.4-3.35V409.93c0-1.08.9-1.96 1.98-1.96h29c1.08 0 2.6-.63 3.37-1.4l43.32-43.01a5.52 5.52 0 0 1 3.37-1.4h6.11c1.09 0 2.6-.62 3.38-1.38l53.12-52.76a5.52 5.52 0 0 1 3.37-1.39h13.6c1.08 0 2.6.63 3.37 1.4L892.79 370c.77.77 2.29 1.4 3.37 1.4h74.06c1.08 0 2.6.62 3.37 1.38l93.97 93.5"/><path stroke="#E6632A" d="M647.56 429.46v-33.62c0-1.08-.63-2.59-1.4-3.35l-31.45-31.22a5.52 5.52 0 0 0-3.37-1.4h-36.87c-1.08 0-2.6.63-3.37 1.4l-74.35 73.83a5.52 5.52 0 0 1-3.37 1.39H440.9a1.97 1.97 0 0 1-1.98-1.96v-71.5c0-1.08-.88-1.96-1.97-1.96H9.3c-1.08 0-2.6.63-3.37 1.4l-37.9 37.62a5.52 5.52 0 0 1-3.37 1.4h-57c-1.1 0-2.61.62-3.38 1.38l-13.2 13.1a5.52 5.52 0 0 1-3.37 1.4h-13.2"/><path stroke="#F4D21F" d="M219.9 357h144.49l76.54.13c1.08 0 1.97.88 1.97 1.96v71.7c0 1.08.89 1.96 1.97 1.96h46.36c1.08 0 2.6-.63 3.37-1.4l74.35-74a5.52 5.52 0 0 1 3.37-1.4h192.33c1.09 0 2.6-.62 3.37-1.38l43.58-43.28a5.52 5.52 0 0 1 3.37-1.39h10.6c1.08 0 2.6.63 3.37 1.4l62.65 62.2c.77.77 2.29 1.4 3.37 1.4h73.87c1.09 0 2.6.63 3.38 1.4l94.12 93.47 9.27.57c.84 0 2.17-.62 2.93-1.39l104.08-89.36a1.97 1.97 0 0 1 2.78 0l6.3 6.25"/><path stroke="#9DCA40" d="M599.92 564.19a6.6 6.6 0 0 0 4.04-1.67l47.94-47.6a6.5 6.5 0 0 0 1.67-4.01V313.84c0-1.3-.75-3.1-1.67-4.02l-47.94-47.6a6.6 6.6 0 0 0-4.04-1.66h-97.84a6.6 6.6 0 0 0-4.05 1.66l-47.93 47.6a6.5 6.5 0 0 0-1.68 4.02v197.07c0 1.29.75 3.1 1.68 4.01l47.93 47.6a6.6 6.6 0 0 0 4.05 1.67h97.84z"/><path stroke="#1EB2D8" d="M648.25 527.17v33.3c0 1.08-.63 2.58-1.4 3.35l-87.37 86.76c-.77.76-.51 1.39.57 1.39h70.82"/><path stroke="#BC6D21" d="M476.04 273.32v-18.86c0-1.08-.63-2.59-1.4-3.35l-30.9-30.68a5.52 5.52 0 0 0-3.37-1.4H274.62"/><path stroke="#9F83B6" d="M923.43 372.6V119.09c0-1.07-.64-2.58-1.4-3.34L757.4-47.74a5.52 5.52 0 0 0-3.37-1.39H351.57c-1.09 0-2.6.63-3.38 1.4L310.5-10.3"/><path stroke="#ED8E3F" d="M317-49.77L304.42-37.3a5.58 5.58 0 0 0-1.42 3.35l-.36 21.45a5.3 5.3 0 0 0 1.36 3.35L493.36 178.9c.77.76 1.4 2.27 1.4 3.35v18.41c0 1.08.89 1.96 1.97 1.96h87.86c1.09 0 1.98.88 1.98 1.96v34.67c0 1.08.88 1.96 1.97 1.96h23.3c1.08 0 2.6.63 3.37 1.4l46.16 45.83c.77.77 1.4 2.28 1.4 3.35v138.64l.07 84.4c0 1.08-.63 2.6-1.38 3.35l-53.63 53.27a5.52 5.52 0 0 1-3.37 1.39H557.9c-1.08 0-2.6.63-3.37 1.39L380.57 746.98a5.52 5.52 0 0 1-3.38 1.39H112.47c-1.09 0-1.97.88-1.97 1.96v93.24c0 1.08-.9 1.96-1.98 1.96h-224.54"/><path stroke="#DA5A98" d="M415.07 612.97l63.3-62.86a5.52 5.52 0 0 1 3.37-1.4h124.67c1.08 0 2.6-.6 3.37-1.37l28.23-27.83a5.35 5.35 0 0 0 1.4-3.33V478.2c0-1.07.89-1.96 1.97-1.96H694c1.09 0 1.97-.88 1.97-1.95v-52.11c0-1.08.64-2.59 1.4-3.35l29.57-29.37a5.45 5.45 0 0 0 1.4-3.35v-76c0-1.08.9-1.96 1.98-1.96h37.9a4 4 0 0 0 4-4v-29.3c0-1.08.63-2.59 1.4-3.35l35.35-35"/><path stroke="#1AA5D0" d="M893.1 374.7L847.5 420a5.52 5.52 0 0 1-3.37 1.38H618.66c-1.09 0-2.6-.62-3.37-1.39l-81.65-81.08a5.52 5.52 0 0 0-3.37-1.39H384.49c-1.08 0-2.6.63-3.37 1.4l-17.14 17.02"/><path stroke="#55B549" d="M288.52 640.2h-46.9c-1.09 0-1.98.88-1.98 1.95v26.65c0 1.07-.89 1.95-1.97 1.95h-89.32"/><path stroke="#D3C452" d="M281.34 229.6l9.65 9.59c.77.76 2.29 1.39 3.37 1.39l146.76-.2c1.09 0 2.6.63 3.38 1.37l115.95 114c.77.76.77 1.99 0 2.75l-37.2 37.05a1.96 1.96 0 0 0 0 2.78l49.62 49.28c.77.77 2.3 1.4 3.38 1.4h138.28c1.08 0 2.6.62 3.37 1.39l37.26 37c.77.76 2.3 1.4 3.38 1.4h21.7"/><path stroke="#9DCA40" d="M-116.02 841.87h216.77c1.09 0 1.97-.89 1.97-1.96v-99.83c0-1.08.9-1.96 1.98-1.96h266.24c1.08 0 2.6-.62 3.37-1.39l20.18-20.04c.77-.76.77-2.02 0-2.76l-78.7-78.2a5.45 5.45 0 0 1-1.4-3.35v-1.57c0-1.07.88-1.96 1.97-1.96l139.22.02c1.09 0 2.6-.62 3.38-1.39l53.7-53.48a4.86 4.86 0 0 1 2.8-1.39c.76 0 1.41-.88 1.41-1.96v-6.62"/><path stroke="#B00D7C" d="M317.92 257.82l73.16 72.65c.77.77 1.4 2.27 1.4 3.35v45.25c0 1.08.63 2.59 1.4 3.35l12.02 11.93c.77.77 2.28 1.4 3.37 1.4h9.86c1.09 0 2.6-.63 3.38-1.4l6.29-6.25a5.52 5.52 0 0 1 3.37-1.39h85.81c1.08 0 2.6-.62 3.37-1.39l63.1-62.66a5.52 5.52 0 0 1 3.38-1.4h161.56c1.08 0 1.97.89 1.97 1.96v178.66c0 1.07-.63 2.58-1.4 3.35l-11.42 11.34a5.52 5.52 0 0 1-3.38 1.39H529.03a1.97 1.97 0 0 1-1.98-1.96v-73.07c0-1.07-.88-1.96-1.97-1.96h-88.26a1.97 1.97 0 0 1-1.97-1.95V406.5c0-1.08-.89-1.96-1.97-1.96-1.99 0-3.5-.63-4.28-1.4l-7.44-7.38"/><path stroke="#DA5A98" d="M650.42-78.35v211.36c0 1.08.63 2.59 1.4 3.35l46.73 46.4c.77.77 1.4 2.28 1.4 3.36v35.79l-2.49-.14c-.75 0-1.97.63-2.74 1.4l-18.32 18.19a5.45 5.45 0 0 0-1.4 3.35v116.95c0 1.07.63 2.58 1.38 3.35l46.53 46.58a5.42 5.42 0 0 1 1.38 3.35l-.02 30.34c0 1.08-.63 2.59-1.4 3.35l-4.91 4.88a5.52 5.52 0 0 1-3.37 1.4H599.52c-1.08 0-1.97.87-1.97 1.95v36c0 1.08-.89 1.96-1.97 1.96h-92.71c-1.09 0-2.6.63-3.38 1.4l-19.58 19.45a5.52 5.52 0 0 1-3.38 1.39h-63.61"/><path stroke="#1EB2D8" d="M281.48 745v84.33c0 1.08-.89 1.96-1.97 1.96h-57.48c-1.09 0-1.98.88-1.98 1.96v10.36c0 1.08-.88 1.96-1.97 1.96H110.52"/><path stroke="#F5C739" d="M10.95 362.32l113.4 112.62c.78.77 2.3 1.4 3.38 1.4h36.12c1.08 0 2.6.62 3.37 1.38l205.45 204.03c.77.76 2.29 1.39 3.37 1.39l62.74.03h29.53c1.09 0 2.6.63 3.37 1.4l16.36 16.23c.77.77 2.29 1.4 3.37 1.4h134.34"/><path stroke="#31B495" d="M275.82 590.44l24.44-24.27a5.52 5.52 0 0 1 3.37-1.4h121.52c1.08 0 2.6.63 3.37 1.4l34.32 34.08c.77.77 2.3 1.4 3.38 1.4h54.36"/><path stroke="#AD7D20" d="M633.41 278.74l-21.36-21.22a5.45 5.45 0 0 1-1.4-3.35V-78.58"/><path stroke="#1F8C43" d="M754.4 192.02v20.11c0 1.08-.9 1.96-1.98 1.96h-94.49c-1.08 0-2.6.63-3.37 1.4l-50.28 49.93a5.45 5.45 0 0 0-1.4 3.35v56.41c0 1.08.63 2.59 1.4 3.35l10.63 10.56c.77.76 1.4 2.27 1.4 3.35v121.45c0 1.08-.89 1.96-1.97 1.96H429.6c-1.08 0-2.6-.62-3.37-1.39l-21.2-21.06-15.77 14.8a5.52 5.52 0 0 1-3.37 1.38H282.15c-1.08 0-2.6.63-3.37 1.37l-62.1 61.3a5.5 5.5 0 0 1-3.37 1.37h-69.85c-1.09 0-2.6.63-3.37 1.4l-68.22 67.73a5.52 5.52 0 0 1-3.37 1.4H34.1c-1.09 0-2.6.62-3.38 1.38l-61.64 61.22a5.45 5.45 0 0 0-1.4 3.35v98.02c0 1.08-.89 1.96-1.97 1.96h-30.76c-1.08 0-2.6.63-3.37 1.4l-48.29 47.95"/><path stroke="#74BB63" d="M184.55 422.03v34.09c0 1.07-.63 2.58-1.4 3.35l-56.48 55.88a5.52 5.52 0 0 1-3.37 1.4H-34.6"/><path stroke="#E5683E" d="M980.12 416.59l-15.05-14.95a5.52 5.52 0 0 0-3.38-1.4h-46.04c-1.08 0-2.6.63-3.37 1.4l-14.5 14.4c-.77.76-1.4.5-1.4-.57v-34.93c0-1.08-.63-2.58-1.4-3.35l-2.48-2.47"/><path stroke="#DA5A98" d="M826.77 238.25v54.43c0 1.08.63 2.59 1.4 3.35l86.38 85.78c.77.77 2.29 1.4 3.37 1.4h98.61c1.09 0 2.6-.63 3.36-1.4l22.6-22.8a5.47 5.47 0 0 1 3.36-1.39h106.38c1.08 0 1.97-.88 1.97-1.96l.04-95.24c0-1.08.89-1.96 1.97-1.96h39.02c1.09 0 1.97.88 1.97 1.96v48.1"/><path stroke="#E6632A" d="M12.87 361.05h-5c-1.1 0-2.61-.63-3.38-1.4l-17.72-17.58a5.52 5.52 0 0 0-3.37-1.4h-16.9c-1.09 0-2.6-.62-3.38-1.38l-55.64-55.26a5.52 5.52 0 0 0-3.38-1.4h-15.19"/><path stroke="#3EB373" d="M959.23 126.08l19.2 19.06c.76.76 2.28 1.39 3.36 1.39h177.42c1.09 0 1.97.88 1.97 1.96v100.84a3 3 0 0 0 3 3h36.42c1.08 0 1.97.88 1.97 1.96v54.65"/><path stroke="#2765B0" d="M33.17 798.75h242.12c1.08 0 1.97-.88 1.97-1.96V672.9c0-1.08-.89-1.96-1.97-1.96h-30.12a1.97 1.97 0 0 1-1.98-1.96v-26.76c0-1.07-.88-1.96-1.97-1.96h-20.87"/><path stroke="#EB9D12" d="M458.48 496.1h9.55c1.09 0 2.6-.63 3.37-1.4l48.23-47.83a5.52 5.52 0 0 1 3.38-1.39h24.26c1.08 0 2.6.63 3.37 1.39l23.26 23.1c.77.76 2.29 1.39 3.37 1.39h111.06c1.09 0 1.97-.88 1.97-1.96v-54.46c0-1.08-.63-2.59-1.4-3.33l-20.35-20.04-2.8-2.76-1.17-1.16a5.52 5.52 0 0 0-3.37-1.39h-11.66a1.97 1.97 0 0 1-1.97-1.96V310.6c0-1.08.88-1.96 1.97-1.96h77.38"/><path stroke="#9DCA40" d="M-34.94 402.19v111.19c0 1.07.63 2.58 1.4 3.35l49.06 48.71c.76.77 2.28 1.4 3.37 1.4h21.8c1.08 0 2.6.62 3.37 1.39l113 112.22c.78.77 2.3 1.4 3.38 1.4h170.6c1.08 0 1.97.87 1.97 1.95v60.41"/></g></svg>
		</div>

	</div>
		
	<script>
		// Some Javascript Animation code
		var pathEls = document.querySelectorAll('path');
		for (var i = 0; i < pathEls.length; i++) {
		var pathEl = pathEls[i];
		var offset = anime.setDashoffset(pathEl);
		pathEl.setAttribute('stroke-dashoffset', offset);
		anime({
			targets: pathEl,
			strokeDashoffset: [offset, 0],
			duration: anime.random(1000, 3000),
			delay: anime.random(0, 2000),
			loop: true,
			direction: 'alternate',
			easing: 'easeInOutSine',
			autoplay: true
		});
		}
	</script>

</body>
</html>