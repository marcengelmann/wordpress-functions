<?php
/**
 * Blossom Wedding functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Blossom_Wedding
 */

//define theme version
$blossom_wedding_theme_data = wp_get_theme();
if( ! defined( 'BLOSSOM_WEDDING_THEME_VERSION' ) ) define( 'BLOSSOM_WEDDING_THEME_VERSION', $blossom_wedding_theme_data->get( 'Version' ) );
if( ! defined( 'BLOSSOM_WEDDING_THEME_NAME' ) ) define( 'BLOSSOM_WEDDING_THEME_NAME', $blossom_wedding_theme_data->get( 'Name' ) );

/**
 * Custom Functions.
 */
require get_template_directory() . '/inc/custom-functions.php';

/**
 * Standalone Functions.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Template Functions.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Custom functions for selective refresh.
 */
require get_template_directory() . '/inc/partials.php';

/**
 * Fontawesome
 */
require get_template_directory() . '/inc/fontawesome.php';

/**
 * Custom Controls
 */
require get_template_directory() . '/inc/custom-controls/custom-control.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer/customizer.php';

/**
 * Widgets
 */
require get_template_directory() . '/inc/widgets.php';

/**
 * Metabox
 */
require get_template_directory() . '/inc/metabox.php';

/**
 * Typography Functions
 */
require get_template_directory() . '/inc/typography.php';

/**
 * Dynamic Styles
 */
require get_template_directory() . '/css/style.php';

/**
 * Plugin Recommendation
*/
require get_template_directory() . '/inc/tgmpa/recommended-plugins.php';

/**
 * Getting Started
*/
require get_template_directory() . '/inc/getting-started/getting-started.php';

/**
 * Toolkit Filters
*/
if( blossom_wedding_is_bttk_activated() ) {
	require get_template_directory() . '/inc/toolkit-functions.php';
}

/**
 * Add theme compatibility function for woocommerce if active
*/
if( blossom_wedding_is_woocommerce_activated() ){
    require get_template_directory() . '/inc/woocommerce-functions.php';    
}

/** 
 * CUSTOM BY MARC ENGELMANN ---------------------------------------------
 */

function show_loggedin_function( $atts ) {

	$user_id = 'user_'.get_current_user_id();
	global $current_user, $user_login;
    get_currentuserinfo();
	
	$sex_title = ((get_field('geschlecht_gast_1',$user_id)==='männlich')?'r':'');
	$dear_people = 'Liebe'.$sex_title.' '.$current_user->first_name;
	
	foreach(range(2,5) as $guest_id) {
		
		if(get_field('anzahl',$user_id) >= $guest_id) {
			$sex_title_guest = ((get_field('geschlecht_gast_'.$guest_id,$user_id)==='männlich')?'r':'');
			$dear_people = $dear_people.', liebe'.$sex_title_guest.' '.get_field('vorname_'.$guest_id,$user_id);
			
		}		
	}
	
	return $dear_people;
}

function switch_by_amount_function( $atts ) {
	$user_id = 'user_'.get_current_user_id();
	return (get_field('anzahl',$user_id)==='1' ? $atts['single'] : $atts['multi']);	
}

function park_status_function($atts) {
	$user_id = 'user_'.get_current_user_id();
	return "Es wird ".(get_field('parkticket',$user_id)==='ja' ? "ein" : "kein")." Parkticket benötigt.";	
}

function guest_list_function($atts){
	
	global $current_user, $user_login;
    get_currentuserinfo();
	
	$user_id = 'user_'.get_current_user_id();
	
	$content;
	
	$name = $current_user->first_name;//' '.$current_user->last_name;
	$meal = '';
	switch(get_field('zusage_1',$user_id)) {
		case '- nicht ausgewählt -':
			$confirmed = 'hat noch nicht zugesagt.';
			break;
		case 'Zusage':
			$confirmed = 'hat zugesagt';
			$meal = get_field('essenauswahl_1',$user_id)==='- nicht ausgewählt -'?' und muss das Essen noch auswählen.':' und isst '.get_field('essenauswahl_1',$user_id).'.';
			break;
		case 'Absage':
			$confirmed = 'hat abgesagt.';
			break;
	}
	$content = $content.'<li>'.$name.' '.$confirmed.$meal.'</li>';
	
	foreach(range(2,5) as $guest_id){
		if($guest_id <= get_field('anzahl',$user_id)) {
		    $name = get_field('vorname_'.$guest_id,$user_id);//.' '.get_field('name_'.$guest_id,$user_id);
			$meal = '';
			switch(get_field('zusage_'.$guest_id,$user_id)) {
				case '- nicht ausgewählt -':
					$confirmed = 'hat noch nicht zugesagt.';
					break;
				case 'Zusage':
					$confirmed = 'hat zugesagt';
					$meal = get_field('essenauswahl_'.$guest_id,$user_id)==='- nicht ausgewählt -'?' und muss das Essen noch auswählen.':' und isst '.get_field('essenauswahl_'.$guest_id,$user_id).'.';
					break;
				case 'Absage':
					$confirmed = 'hat abgesagt.';
					break;
			}
		    $content = $content.'<li>'.$name.' '.$confirmed.$meal.'</li>';
		}
	}
	
	return $content;
}

function admin_show_all_data_function_mobile($atts) {
	if(!current_user_can('administrator')) {
		return;
	}
	
	$content = '<table style="margin-bottom:60px;"><thead style="font-weight:bold;">'.create_table_row(['Name', 'Zusage', 'Essen' ],'','').'</thead>';
	
	$zusagen = 0;
	$absagen = 0;
	$unentschieden = 0;
	$standesamt_ja = 0;
	$standesamt_nein = 0;
	$standesamt_geplant = 0;
	$standesamt_absage = 0;
	$vegetarisch = 0;
	$vegan = 0;
	$fisch = 0;
	$fleisch = 0;
	$kindermenu = 0;
	$essen_ka = 0;
	
	$users = get_users();
	$counter = 1;
	$content .='<tbody>';
	
	$total_users = count_users();
	$user_counter = 0;
	
	foreach($users as $user){
		
		$user_counter ++;
		
		$user_id_label = 'user_'.$user->id;	
		
		$last_login = get_the_author_meta('last_login',$user->id);
		if($last_login === '') {
			$the_login_date = 'nie';
		} else {
			$the_login_date = 'vor '.human_time_diff($last_login);
		}
    	
		
		
		$zusage = get_field('zusage_1', $user_id_label);
		if($zusage === 'Zusage') {
			$zusagen++;
		} else if($zusage === 'Absage') {
			$absagen++;
		} else {
			$unentschieden++;
		}
		$style = ' style="'.(get_field('anzahl',$user_id_label) == 1 ? ' border-bottom: 1px dashed gray; ' : '').'"';
		
		if(get_field('standesamt', $user_id_label) === 'ja') {
			if($zusage === 'Zusage') {
				$standesamt = 'ja';
				$standesamt_ja++;
			} else if($zusage === 'Absage') {
				$standesamt = 'nein (Absage)';
				$standesamt_absage++;	
				
			} else {
				$standesamt = 'geplant';
				$standesamt_geplant++;
			}
		} else {
			$standesamt = 'nein';
			$standesamt_nein++;
		}
		
		if($zusage === 'Zusage') {
			switch(get_field('essenauswahl_1', $user_id_label)) {
				case 'Vegetarisch':
					$vegetarisch++;
					break;
				case 'Fisch':
					$fisch++;
					break;
				case 'Fleisch':
					$fleisch++;
					break;
				case 'Vegan':
					$vegan++;
					break;
				case '- nicht ausgewählt -':
					$essen_ka++;
					break;
				case 'Kindermenü':
					$kindermenu++;
					break;
			}
			$essen = get_field('essenauswahl_1', $user_id_label);
		} else {
					$essen = '';
		}
		
			
		$content .= create_table_row([$user->first_name.' '.$user->last_name, $zusage, $essen], $style,$zusage);
    	
		$counter ++;
		
		foreach(range(2,5) as $guest_id) {
			
			if(get_field('anzahl',$user_id_label) >= $guest_id) {
				
				$zusage = get_field('zusage_'.$guest_id, $user_id_label);
				if($zusage === 'Zusage') {
					$zusagen++;
				} else if($zusage === 'Absage') {
					$absagen++;
				} else {
					$unentschieden++;
				}
				
				if(get_field('standesamt', $user_id_label) ===  'ja') {
					if($zusage === 'Zusage') {
						$standesamt = 'ja';
						$standesamt_ja++;
					} else if($zusage === 'Absage') {
						$standesamt = 'nein (Absage)';
						$standesamt_absage++;

					} else {
						$standesamt = 'geplant';
						$standesamt_geplant++;
					}
				} else {
					$standesamt = 'nein';
					$standesamt_nein++;
				}
				
				if($zusage === 'Zusage') {
					switch(get_field('essenauswahl_'.$guest_id, $user_id_label)) {
						case 'Vegetarisch':
							$vegetarisch++;
							break;
						case 'Fisch':
							$fisch++;
							break;
						case 'Fleisch':
							$fleisch++;
							break;
						case '- nicht ausgewählt -':
							$essen_ka++;
							break;
						case 'Vegan':
							$vegan++;
							break;
						case 'Kindermenü':
							$kindermenu++;
							break;
					}
					$essen = get_field('essenauswahl_'.$guest_id, $user_id_label);
				} else {
					$essen = '';
				}
				
				$style = ' style="'.(get_field('anzahl',$user_id_label) == $guest_id ? 'border-bottom: 1px dashed gray; ' : '').'"';
				
				if($total_users['total_users'] == $user_counter) {
					$style = '';
				}
				
				$content .= create_table_row([get_field('vorname_'.$guest_id, $user_id_label).' '.get_field('name_'.$guest_id, $user_id_label), $zusage, $essen], $style,$zusage);
		
			$counter ++;
			
			}
		}
    }
	
	$content .='</tbody></table>';
	$content = '<ul style="text-align:center;"><li>Von '.($counter-1).' eingeladenen Gästen haben <b style="font-size:1.1em">'.$zusagen.'</b> zugesagt, '.$absagen.' abgesagt und <b style="font-size:1.1em">'.$unentschieden.'</b> haben sich noch nicht entschieden.</li><li>Von den '.$zusagen.' Zusagen sind '.$essen_ka.' unentschlossen, <b style="font-size:1.1em">'.$vegetarisch.'</b> haben sich für Vegetarisch, <b style="font-size:1.1em">'.$fisch.'</b> für Fisch, <b style="font-size:1.1em">'.$fleisch.'</b> für Fleisch, <b style="font-size:1.1em">'.$vegan.'</b> für Vegan und '.$kindermenu.' für das Kindermenü entschieden.</li></ul><br/><br/>'.$content;
	return $content;
}
	

function admin_show_all_data_function($atts) {
		
	if(!current_user_can('administrator')) {
		return;
	}
	
	$content = '<table style="margin-bottom:60px;"><thead style="font-weight:bold;">'.create_table_row(['#', 'Anrede', 'Vorname', 'Name', 'Zusage', 'Standesamt', 'Essen','Parken',  'Tischnummer',"Sitzplatz",'Letzter Login'],'','').'</thead>';
	
	$zusagen = 0;
	$absagen = 0;
	$unentschieden = 0;
	$standesamt_ja = 0;
	$standesamt_nein = 0;
	$standesamt_geplant = 0;
	$standesamt_absage = 0;
	$vegetarisch = 0;
	$vegan = 0;
	$fisch = 0;
	$fleisch = 0;
	$kindermenu = 0;
	$essen_ka = 0;
	$parken_counter = 0;
	
	$seat_array = array(
    	array("","","","","","","",""),
		array("","","","","","","",""),
		array("","","","","","","",""),
		array("","","","","","","",""),
		array("","","","","","","",""),
		array("","","","","","","",""),
		array("","","","","","","",""),
		array("","","","","","","",""),
		array("","","","","","","",""),
		array("","","","","","","",""),
		array("","","","","","","","")
	);
	
	$users = get_users();
	$counter = 1;
	$content .='<tbody>';
	
	$total_users = count_users();
	$user_counter = 0;
	
	foreach($users as $user){
		
		$user_counter ++;
		
		$user_id_label = 'user_'.$user->id;	
		
		$last_login = get_the_author_meta('last_login',$user->id);
		if($last_login === '') {
			$the_login_date = 'nie';
		} else {
			$the_login_date = 'vor '.human_time_diff($last_login);
		}
    	
		
		
		$zusage = get_field('zusage_1', $user_id_label);
		if($zusage === 'Zusage') {
			$zusagen++;
		} else if($zusage === 'Absage') {
			$absagen++;
		} else {
			$unentschieden++;
		}
		$style = ' style="'.(get_field('anzahl',$user_id_label) == 1 ? ' border-bottom: 1px dashed gray; ' : '').'"';
		
		if(get_field('standesamt', $user_id_label) === 'ja') {
			if($zusage === 'Zusage') {
				$standesamt = 'ja';
				$standesamt_ja++;
			} else if($zusage === 'Absage') {
				$standesamt = 'nein (Absage)';
				$standesamt_absage++;	
				
			} else {
				$standesamt = 'geplant';
				$standesamt_geplant++;
			}
		} else {
			$standesamt = 'nein';
			$standesamt_nein++;
		}
		
		if($zusage === 'Zusage') {
			switch(get_field('essenauswahl_1', $user_id_label)) {
				case 'Vegetarisch':
					$vegetarisch++;
					break;
				case 'Fisch':
					$fisch++;
					break;
				case 'Fleisch':
					$fleisch++;
					break;
				case 'Vegan':
					$vegan++;
					break;
				case '- nicht ausgewählt -':
					$essen_ka++;
					break;
				case 'Kindermenü':
					$kindermenu++;
					break;
			}
			$essen = get_field('essenauswahl_1', $user_id_label);
		} else {
					$essen = '';
		}
		
		$parken_option = get_field('parkticket', $user_id_label);
		$parken_label = "-";
		
		if($parken_option === "ja") {
			$parken_label = "ja";
			$parken_counter ++;
		}
		
		$table_number = get_field('table_number', $user_id_label);
		$seat = get_field('sitzplatz_gast_1', $user_id_label);
		
		$seat_array[$table_number-1][$seat-1] .= $user->first_name;

		$content .= create_table_row([$counter, get_field('geschlecht_gast_1', $user_id_label) === 'männlich' ? 'Herr' : 'Frau', $user->first_name, $user->last_name, $zusage, $standesamt, $essen,$parken_label,$table_number,$seat, $the_login_date], $style,$zusage);
    	
		$counter ++;
		
		foreach(range(2,5) as $guest_id) {
			
			if(get_field('anzahl',$user_id_label) >= $guest_id) {
				
				$zusage = get_field('zusage_'.$guest_id, $user_id_label);
				if($zusage === 'Zusage') {
					$zusagen++;
				} else if($zusage === 'Absage') {
					$absagen++;
				} else {
					$unentschieden++;
				}
				
				if(get_field('standesamt', $user_id_label) ===  'ja') {
					if($zusage === 'Zusage') {
						$standesamt = 'ja';
						$standesamt_ja++;
					} else if($zusage === 'Absage') {
						$standesamt = 'nein (Absage)';
						$standesamt_absage++;

					} else {
						$standesamt = 'geplant';
						$standesamt_geplant++;
					}
				} else {
					$standesamt = 'nein';
					$standesamt_nein++;
				}
				
				if($zusage === 'Zusage') {
					switch(get_field('essenauswahl_'.$guest_id, $user_id_label)) {
						case 'Vegetarisch':
							$vegetarisch++;
							break;
						case 'Fisch':
							$fisch++;
							break;
						case 'Fleisch':
							$fleisch++;
							break;
						case '- nicht ausgewählt -':
							$essen_ka++;
							break;
						case 'Vegan':
							$vegan++;
							break;
						case 'Kindermenü':
							$kindermenu++;
							break;
					}
					$essen = get_field('essenauswahl_'.$guest_id, $user_id_label);
				} else {
					$essen = '';
				}
				
				$style = ' style="'.(get_field('anzahl',$user_id_label) == $guest_id ? 'border-bottom: 1px dashed gray; ' : '').'"';
				
				if($total_users['total_users'] == $user_counter) {
					$style = '';
				}
				
				$seat = get_field('sitzplatz_gast_'.$guest_id, $user_id_label);
				$seat_array[$table_number-1][$seat-1] .= get_field('vorname_'.$guest_id, $user_id_label);
				
				$content .= create_table_row([$counter, (get_field('geschlecht_gast_'.$guest_id,$user_id_label) === 'männlich' ? 'Herr' : 'Frau'), get_field('vorname_'.$guest_id, $user_id_label), get_field('name_'.$guest_id, $user_id_label), $zusage, $standesamt ,$essen,"-",$table_number,$seat,'-'], $style,$zusage);
		
			$counter ++;
			
			}
		}
    }
	
	$content .='</tbody></table>';
	$content = '<ul style="text-align:center;"><li>Wir haben '.($counter-1).' Gäste eingeladen, davon haben <b style="font-size:1.1em">'.$zusagen.'</b> zugesagt, '.$absagen.' abgesagt und <b style="font-size:1.1em">'.$unentschieden.'</b> haben sich noch nicht entschieden.</li><li>Für das Standesamt haben von den eingeplanten <b style="font-size:1.1em">'.($standesamt_geplant+$standesamt_ja+$standesamt_absage).'</b> Gästen <b style="font-size:1.1em">'.$standesamt_ja.'</b> zugesagt, '.$standesamt_absage.' abgesagt und '.$standesamt_geplant.' haben allgemein noch nicht zugesagt.</li><li>Von den '.$zusagen.' Zusagen sind '.$essen_ka.' unentschlossen, <b style="font-size:1.1em">'.$vegetarisch.'</b> haben sich für Vegetarisch, <b style="font-size:1.1em">'.$fisch.'</b> für Fisch, <b style="font-size:1.1em">'.$fleisch.'</b> für Fleisch, <b style="font-size:1.1em">'.$vegan.'</b> für Vegan und '.$kindermenu.' für das Kindermenü entschieden.</li><li>Für <b style="font-size:1.1em">'.$parken_counter.'</b> PKW unserer Gäste wird ein Parkticket benötigt.</li></ul><br/><br/>'.$content;
	
	$seat_room = array(
    	array("","","","","","","","","","","","","",""),
		array("","","","","","","","","","","","","",""),
		array("","","","","","","","","","","","","",""),
		array("","","","","","","","","","","","","",""),
		array("","","","","","","","","","","","","",""),
		array("","","","","","","","","","","","","",""),
		array("","","","","","","","","","","","","",""),
		array("","","","","","","","","","","","","",""),
		array("","","","","","","","","","","","","",""),
		array("","","","","","","","","","","","","",""),
		array("","","","","","","","","","","","","",""),
		array("","","","","","","","","","","","","",""),
		array("","","","","","","","","","","","","",""),
		array("","","","","","","","","","","","","","") 	
	);
	
	foreach(range(1,11) as $table_number) {
		

		if($table_number == 1) {
			$horizontal_begin = 5;
			$vertical_begin = 2;
		
		}  elseif($table_number == 2) {
			$horizontal_begin = 0;
			$vertical_begin = 0;
		
		} elseif($table_number == 3) {
			$horizontal_begin = 0;
			$vertical_begin = 4;
		
		} elseif($table_number == 4) {
			$horizontal_begin = 10;
			$vertical_begin = 0;
		
		} elseif($table_number == 5) {
			$horizontal_begin = 0;
			$vertical_begin = 8;
		
		} elseif($table_number == 6) {
			$horizontal_begin = 0;
			$vertical_begin = 12;
		
		} elseif($table_number == 7) {
			$horizontal_begin = 5;
			$vertical_begin = 6;
		
		} elseif($table_number == 8) {
			$horizontal_begin = 10;
			$vertical_begin = 4;
		
		} elseif($table_number == 9) {
			$horizontal_begin = 10;
			$vertical_begin = 8;
		
		} elseif($table_number == 10) {
			$horizontal_begin = 5;
			$vertical_begin = 10;
			
		} elseif($table_number == 11) {
			$horizontal_begin = 10;
			$vertical_begin = 12;
		} else {
			$horizontal_begin = 0;
			$vertical_begin = 0;
		}
		
		$table_number -= 1;
				
		$horizontal_counter = 0;
		$vertical_counter = 0;
		
		foreach(range(0,7) as $seat_number) {
			
			$seat_room[$vertical_begin+$vertical_counter][$horizontal_begin+$horizontal_counter] = $seat_array[$table_number][$seat_number];
			
			$horizontal_counter ++;
			
			if ($horizontal_counter == 4) {
				$horizontal_counter = 0;
				$vertical_counter ++;
			}
		}
	}
	
	$content .= '<table style="margin-bottom:60px;"><tbody>';
	
	foreach(range(0,13) as $horizontal) {
				
		$content.= create_table_row_table($seat_room[$horizontal]);
	}
	
	$content.= '</tbody></table>';
	
	return $content;
	
}

/**
 * Create a row of a table.
 */
function create_table_row_table($string_array) {
		
	$content = '<tr'.$tr_style.'>';
	
	foreach($string_array as $string) {
			$content .= '<td>'.$string.'</td>';
	}

	$content .= '</tr>';
	
	return $content;
}

/**
 * Create a row of a table.
 */
function create_table_row($string_array, $tr_style='', $zusage) {
		
	$content = '<tr'.$tr_style.'>';
	
	foreach($string_array as $string) {
		
		if(empty($string) || $string === '- nicht ausgewählt -') {
			$string = '<i>tbd</i>';
		}
		
		if($zusage=='Absage') {
			$content .= '<td style="color:#CCC">'.$string.'</td>';
		} else {
			$content .= '<td>'.$string.'</td>';
		}
	}
	
	$content .= '</tr>';
	
	return $content;
}


add_filter('widget_text', 'do_shortcode');
add_shortcode('admin_show_all_data','admin_show_all_data_function');
add_shortcode('admin_show_all_data_mobile','admin_show_all_data_function_mobile');
add_shortcode( 'show_loggedin_as', 'show_loggedin_function' );
add_shortcode( 'switch_by_amount', 'switch_by_amount_function' );
add_shortcode('guest_list', 'guest_list_function');
add_shortcode('park_status','park_status_function');

function show_meal_function( $atts ) {

	$user_id = 'user_'.get_current_user_id();
	global $current_user, $user_login;
      	get_currentuserinfo();
	add_filter('widget_text', 'do_shortcode');
	if ($user_login) 
		return get_field('essenauswahl',$user_id); //$current_user->first_name;
	else
		return 'ERROR!';
	
}

add_shortcode( 'show_meal', 'show_meal_function' );

add_filter('the_title', 'do_shortcode');
add_filter('widget_title', 'do_shortcode');

add_action('after_setup_theme', 'remove_admin_bar');

function remove_admin_bar() {
  	show_admin_bar(false);
}

// removes admin color scheme options

remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );

//Removes the leftover 'Visual Editor', 'Keyboard Shortcuts' and 'Toolbar' options.

add_action( 'admin_head', function () {
	
	if(!current_user_can('administrator')) {

		ob_start( function( $subject ) {

			$subject = preg_replace( '#<h[0-9]>'.__("Persönliche Optionen").'</h[0-9]>.+?/table>#s', '', $subject, 1 );
			return $subject;
		});

		ob_start( function( $subject ) {

			$subject = preg_replace( '#<h[0-9]>'.__("Über Dich").'</h[0-9]>.+?/table>#s', '', $subject, 1 );
			return $subject;
		});
		
		ob_start( function( $subject ) {

			$subject = preg_replace( '#<h[0-9]>'.__("Kontaktinfo").'</h[0-9]>#s', '', $subject, 1 );
			return $subject;
		});
		
			
		ob_start( function( $subject ) {

			$subject = preg_replace( '#<h[0-9]>'.__("Informationen für die Hochzeit").'</h[0-9]>#s', '', $subject, 1 );
			return $subject;
		});	
	
			
			
		ob_start( function( $subject ) {

			$subject = preg_replace( '#<h[0-9]>'.__("Benutzerkonten-Verwaltung").'</h[0-9]>.+?/table>#s', '', $subject, 1 );
			return $subject;
		});

		ob_start( function( $subject ) {

			$subject = preg_replace('#<tr class="user-url-wrap(.*?)</tr>#s', '', $subject, 1);
			return $subject;
		});
		
		ob_start( function( $subject ) {

			$subject = preg_replace('#<tr class="user-user-login-wrap(.*?)</tr>#s', '', $subject, 1);
			return $subject;
		});
		
		ob_start( function( $subject ) {

			$subject = preg_replace('#<tr class="user-display-name-wrap(.*?)</tr>#s', '', $subject, 1);
			return $subject;
		});
			
		
		echo '<style>tr.user-nickname-wrap{ display: none; }</style>';
		echo '<style>tr.user-email-wrap{ display: none; }</style>';
		echo '<style>tr[data-name="anzahl"] { display: none; }</style>';
		echo '<style>tr[data-name="geschlecht_gast_1"] { display: none; }</style>';
		echo '<style>tr[data-name="geschlecht_gast_2"] { display: none; }</style>';
		echo '<style>tr[data-name="geschlecht_gast_3"] { display: none; }</style>';
		echo '<style>tr[data-name="geschlecht_gast_4"] { display: none; }</style>';
		echo '<style>tr[data-name="geschlecht_gast_5"] { display: none; }</style>';
		
		echo '<style>tr[data-name="table_number"] { display: none; }</style>';
		
		echo '<style>tr[data-name="sitzplatz_gast_1"] { display: none; }</style>';
		echo '<style>tr[data-name="sitzplatz_gast_2"] { display: none; }</style>';
		echo '<style>tr[data-name="sitzplatz_gast_3"] { display: none; }</style>';
		echo '<style>tr[data-name="sitzplatz_gast_4"] { display: none; }</style>';
		echo '<style>tr[data-name="sitzplatz_gast_5"] { display: none; }</style>';
		
		//echo '<style>tr[data-name="zusage_1"] { display: none; }</style>';
		//echo '<style>tr[data-name="zusage_2"] { display: none; }</style>';
		//echo '<style>tr[data-name="zusage_3"] { display: none; }</style>';
		//echo '<style>tr[data-name="zusage_4"] { display: none; }</style>';
		//echo '<style>tr[data-name="zusage_5"] { display: none; }</style>';
		
		echo '<style>tr[data-name="standesamt"] { display: none; }</style>';
		echo '<style>div[id="wpfooter"] { display: none; }</style>';
		
		?>
		<script type="text/javascript"> jQuery(document).ready( function($){ $(".user-first-name-wrap #first_name").prop('readonly', true);}); </script>
		<script type="text/javascript"> jQuery(document).ready( function($){ $(".user-last-name-wrap #last_name").prop('readonly', true);}); </script>
		<?php
	} 		
});

add_action( 'admin_footer', function(){

	ob_end_flush();
}); 

function my_acf_prepare_field( $field ) {
	
	$user_id = 'user_'.get_current_user_id();

    // Lock-in the value "Example".
    if( $field['key'] === 'field_61238dcf99874' ) {
    	//$field['readonly'] = true;
	//	$field['value'] = 3;
    };
	
	if(!current_user_can('administrator')) {
		if(strpos($field['label'], 'Name') !== false || strpos($field['label'], 'Vorname')!== false|| strpos($field['label'], 'Anzahl')!== false|| strpos($field['name'], 'geschlecht')!== false || strpos($field['label'], 'Standesamt')!== false) {
			$field['readonly'] = true;
		}
	}
	
	if (strpos($field['label'], 'Gast 1') !== false) {
			if(strpos($field['label'], 'Name') !== false) {
				return $field;
			}

			$field['label'] = str_replace('Gast 1', wp_get_current_user()->user_firstname, $field['label']);
		}
	
	foreach(range(2,5) as $guest_id) {
		if (strpos($field['label'], 'Gast '.$guest_id) !== false) {
			
			if(strpos($field['label'], 'Name') !== false) {
				return $field;
			}
			
			if(strpos($field['label'], 'Vorname') !== false) {
				return $field;
			}

			$field['label'] = str_replace('Gast '.$guest_id, get_field('vorname_'.$guest_id,$user_id), $field['label']);
		}
	}
	
	
    return $field;
}

function user_last_login( $user_login, $user ) {
    update_user_meta( $user->ID, 'last_login', time() );
}
add_action( 'wp_login', 'user_last_login', 10, 2 );

// Apply to all fields.
add_filter('acf/prepare_field', 'my_acf_prepare_field');

add_filter( 'pre_get_document_title', 'cyb_change_page_title' );

function cyb_change_page_title () {

    return "Hochzeit";

}

function load_google_fonts() {
   wp_register_style('googleFonts', 'https://fonts.googleapis.com/css2?family=Nothing+You+Could+Do&display=swap');
 wp_enqueue_style( 'googleFonts');
 }
 add_action('wp_print_styles', 'load_google_fonts');

// Hide the 'Back to {sitename}' link on the login screen.
function my_forcelogin_hide_backtoblog() {
  echo '<style type="text/css">#backtoblog{display:none;}#nav{display:none;} body.login div#login h1 a {display:none;}</style>';
  echo '<style type="text/css">.language-switcher{display:none;}</style>';
	
}
add_action( 'login_enqueue_scripts', 'my_forcelogin_hide_backtoblog' );

add_filter( 'wp_is_application_passwords_available', '__return_false' );

add_action('profile_update', 'redirect_me');
function redirect_me(){
  wp_redirect(home_url('/'));
  exit;
}
