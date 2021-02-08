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
		$_SESSION['deck'][] = array( 'value' => 'Valet', 'color' => $color, 'points' => 10 );
		$_SESSION['deck'][] = array( 'value' => 'Dame', 'color' => $color, 'points' => 10 );
		$_SESSION['deck'][] = array( 'value' => 'Roi', 'color' => $color, 'points' => 10 );
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
			$this->cardlist[] = array( 'value' => 'Valet', 'color' => $color, 'points' => 10 );
			$this->cardlist[] = array( 'value' => 'Dame', 'color' => $color, 'points' => 10 );
			$this->cardlist[] = array( 'value' => 'Roi', 'color' => $color, 'points' => 10 );
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
	<title>Black Jack</title>
</head>

<body>
	<div class="wrapp">
		<h1>Black Jack</h1>
		<div id="money">
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

		<div class="top-left">
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
	</div>
		
</body>
</html>