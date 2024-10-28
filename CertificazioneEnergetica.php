<?php
/*
	Plugin Name: Certificazione Energetica
	Plugin URI: http://www.matteotirelli.com/myblog/wordpress-2/ace-certificazione-energetica-wordpress-plugin/
	Description: Il plugin indispensabile per gestire la Certificazione Energetica ( ACE ) degli immobili.
	Author: Matteo Tirelli
	Author URI: http://www.matteotirelli.com
	Version: 1.0.2

	Copyright (c) 2011-2012 Matteo Tirelli (http://www.matteotirelli.com)
	CertificazioneEnergetica is released under the GNU General Public License (GPL)
	http://www.gnu.org/licenses/gpl-2.0.txt
*/



/**********************************************
 * METABOX
 **********************************************/

$box_CertificazioneEnergetica = array(
   "ace_post" => array(
      "name" => "ace_post",
      "tipo" => "select",
      "opzioni" => array( "Da assegare", "A+", "A", "B", "C", "D", "E", "F", "G", "ESENTE"),
      "title" => "ACE",
      "description" => ""
   ),
   "ipe_post" => array(
      "name" => "ipe_post",
      "tipo" => "input",
      "opzioni" => "",
      "title" => "IPE",
      "description" => ""
   ),
   "unita_ipe" => array(
      "name" => "unita_ipe",
      "tipo" => "select",	
      "opzioni" => array("kWh/m2 anno", "kWh/m3 anno"),
      "title" => "Unita di misura",
      "description" => ""
   )
);

function CertificazioneEnergetica_boxes() {
   global $post, $box_CertificazioneEnergetica;
   echo '<table style="width:99%">';
   foreach( $box_CertificazioneEnergetica as $meta_box) {
      CertificazioneEnergetica_generaForm($meta_box, $post);
   }
   echo '</table>';
}

function CertificazioneEnergetica_generaForm($meta_box, $post){
   echo '<tr><td class="left" style="width:25%;">';
   $meta_box_value = get_post_meta($post->ID, $meta_box['name'], true);
   if($meta_box_value == "")
      $meta_box_value = $meta_box['std'];
      echo '<input type="hidden" name="'.$meta_box['name'].'_noncename" id="'.$meta_box['name'].'_noncename" value="'.wp_create_nonce( plugin_basename(__FILE__) ).'" />';
      echo $meta_box['title'];
      echo '<br /><em>';
      echo '<label class="desc" style="font-size:10px;" for="'.$meta_box['name'].'_value">'.$meta_box['description'].'</label>';
      echo '</em></td>';
      echo '<td>';
      switch($meta_box['tipo']){
         case "select":
            echo '<select name="'.$meta_box['name'].'_value" >';
            foreach ($meta_box['opzioni'] as $valore){
               echo '<option value="'.$valore.'"';
               if($meta_box_value == $valore)
                  echo 'selected="selected"';
                  echo '>'.$valore.'</option>';
               }
               echo '</select>';
               break;
         case "date":
               echo '<input type="text" name="'.$meta_box['name'].'_value" value="'.$meta_box_value.'" size=25><a href="#" onClick="cal.select(document.forms[\'post\'].'.$meta_box['name'].'_value,\'anchor1\',\'yyyy/MM/dd\'); return false;" name="anchor1" id="anchor1">select</a>'; 
              break;
         case "radio":
            foreach ($meta_box['opzioni'] as $valore){
               echo '<br /><input type="radio" name="'.$meta_box['name'].'_value" value="'.$valore.'"';
               if($meta_box_value == $valore)
                  echo 'checked="checked"';			
                  echo '/> <label>'.$valore.'</label>';
               }
               break;
         case "checkbox":
               echo '<input type="checkbox" name="'.$meta_box['name'].'_value" value="si"'; 
               if($meta_box_value == "si")
                  echo 'checked="checked"';
               echo '/>';
               break;
         case "input":
            echo '<input type="text" name="'.$meta_box['name'].'_value" value="'.$meta_box_value.'" />';
            break;
         case "textarea":				
         default:				
            echo '<textarea style="width:99%" rows="2" name="'.$meta_box['name'].'_value">'.$meta_box_value.'</textarea>';
   }
   echo '</td></tr><tr></tr>';	
}

function CertificazioneEnergetica_create_meta_box() {
   global $theme_name;
   if ( function_exists('CertificazioneEnergetica_boxes') ) {
      add_meta_box('box_CertificazioneEnergetica ', 'Certificazione Energetica', 'CertificazioneEnergetica_boxes', 'post', 'side', 'default');
   }	
}

function CertificazioneEnergetica_save_postdata( $post_id ) {
   global $post,$box_CertificazioneEnergetica;
 
   foreach($box_CertificazioneEnergetica as $meta_box) {
      if ( !wp_verify_nonce( $_POST[$meta_box['name'].'_noncename'], plugin_basename(__FILE__) )) {
         return $post_id;
      }
      if ( 'page' == $_POST['post_type'] ) {
         if ( !current_user_can( 'edit_page', $post_id ))
            return $post_id;
      } else {
         if ( !current_user_can( 'edit_post', $post_id ))
            return $post_id;
      }
 
      $data = $_POST[$meta_box['name'].'_value'];
      if(get_post_meta($post_id, $meta_box['name']) == "")
         add_post_meta($post_id, $meta_box['name'], $data, true);
      elseif($data != get_post_meta($post_id, $meta_box['name'], true))
         update_post_meta($post_id, $meta_box['name'], $data);
      elseif($data == "")
         delete_post_meta($post_id, $meta_box['name'], get_post_meta($post_id, $meta_box['name'], true));
   }
}

add_action('admin_menu', 'CertificazioneEnergetica_create_meta_box');
add_action('save_post', 'CertificazioneEnergetica_save_postdata');



/**********************************************
 * PANNELLO DI CONFIGURAZIONE
 **********************************************/

// Set default option
function CertificazioneEnergetica_set_option(){
	$CertificazioneEnergetica_default_ad = '<div class="widget">
 <table border="0" cellpadding="0" cellspacing="0" class="ace">
              <tr>
                <td width="110" rowspan="2" id="aceCellLogo"><img src="'.plugins_url( 'images/logo_certificazione.png' , __FILE__ ).'" width="110" height="76" alt="Certificazione energetica"></td>
                <td width="157" id="aceCellCentrale">ACE:</td>
                <td width="183" id="aceCell">[ACE]</td>
              </tr>
              <tr>
                <td id="aceCellCentrale">Prestazione energetica (IPE):</td>
                <td id="aceCell">[IPE] [UDM]</td>
              </tr>
            </table>
</div>';
	$CertificazioneEnergetica_default_values = array(
		"CertificazioneEnergetica_add_ad_code" => "Si",
		"CertificazioneEnergetica_ad_code" => $CertificazioneEnergetica_default_ad,
		"CertificazioneEnergetica_show_all" => "No",
		"CertificazioneEnergetica_add_credit" => "Si",
		);
		
	add_option("CertificazioneEnergetica_opts", $CertificazioneEnergetica_default_values);

}

// Admin Panel
function CertificazioneEnergetica_add_pages() {
	add_options_page('ACE Certificazione Energetica', 'ACE Certificazione Energetica', 9, __FILE__, 'CertificazioneEnergetica_options_page');
}

function CertificazioneEnergetica_show_info_msg($msg) {
	echo '<div id="message" class="updated fade"><p>' . $msg . '</p></div>';
}

function CertificazioneEnergetica_options_page() {
	if (isset($_POST['info_update'])) {
	
	$options = array(
		"CertificazioneEnergetica_add_ad_code" => $_POST["CertificazioneEnergetica_add_ad_code"],
		"CertificazioneEnergetica_ad_code" => $_POST["CertificazioneEnergetica_ad_code"],
		"CertificazioneEnergetica_show_all" => $_POST["CertificazioneEnergetica_show_all"],
		"CertificazioneEnergetica_add_credit" => $_POST["CertificazioneEnergetica_add_credit"],
		);

	update_option("CertificazioneEnergetica_opts", $options);
	CertificazioneEnergetica_show_info_msg("Opzioni di Certificazione Energetica salvate.");

	} elseif (isset($_POST["info_reset"])) {

		delete_option("CertificazioneEnergetica_opts");
		CertificazioneEnergetica_show_info_msg("Opzioni di Certificazione Energetica rimosse dal database di WordPress.");

	} else {

		$options = get_option("CertificazioneEnergetica_opts");

	}
	
	

// PAGINA DI CONFIGURAZIONE
// ########################
echo '<div class="wrap">';
	
	// ---
	// Right sidebar
	// ---
	
	echo '<div class="metabox-holder has-right-sidebar">';
	echo '<div class="inner-sidebar">';
	echo '<div class="meta-box-sortabless ui-sortable">';

	
// Box donazioni
   echo '
	<div class="postbox">
		<h3 class="hndle">Donazioni</h3>
		<div class="inside" style="text-align:justify;">
			<p>	Se apprezzi questo plugin e ritieni che sia utile, sarei lieto volessi fare una piccola donazione utilizzando il pulsante qui a fianco. Questa piccola donazione sar&agrave; un piccolo aiuto per continuare a sviluppare il plugin e fornire supporto.</p>
			<div align="center">
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
					<input type="hidden" name="cmd" value="_s-xclick">
					<input type="hidden" name="hosted_button_id" value="GCMQH6H6DCP32">
					<input type="image" src="https://www.paypalobjects.com/it_IT/IT/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - Il sistema di pagamento online più facile e sicuro!">
					<img alt="" border="0" src="https://www.paypalobjects.com/it_IT/i/scr/pixel.gif" width="1" height="1">
				</form>
			</div>
		</div>
	</div>
   ';

// Box supporto
   echo '
	<div class="postbox">
		<h3 class="hndle">Aiuto e supporto</h3>
		<div class="inside" style="text-align:justify;">
			<p> Se riscontri qualche difficolt&agrave;, dubbio, suggerimento o errori da segnalare, non esitare a segnalarlo alla pagina del plugin <a href="http://www.matteotirelli.com/myblog/wordpress-2/ace-certificazione-energetica-wordpress-plugin/">ACE - Certificazione Energetica</a>.</p>
		</div>
	</div>
   ';


// Box ripristino
   echo'
	<div class="postbox">
		<h3 class="hndle">Reset Plugin</h3>
		<div class="inside" style="text-align:justify;">
			<form name="formapfreset" method="post" action="' . $_SERVER['REQUEST_URI'] . '">
				<p>Premendo il pulsante "Resetta opzioni", le informazioni e le opzioni di Certificazione Energetica verranno rimosse dal  database di WordPress e nella pagina di configurazione verranno ripristinati i valori di base.</p>
				<p>Potresti volerlo utilizzare per tornare alla configurazione iniziale dello specchietto ACE.<br><br> Sebbene non sia necessario, potresti volerlo utilizzare prima di disinstallare il plugin per rimuoverne ogni traccia.</p>
				<p class="submit">
					<input type="submit" name="info_reset" value="Resetta opzioni" />
				</p>
			</form>
		</div>
	</div>';
	
	// chiudo sidebar	
	echo '</div>'; // / meta-box-sortabless ui-sortable
	echo '</div>'; // / inner-sidebar
	
	
	// ---
	// Main content area
	// ---
	
	echo '<div class="has-sidebar sm-padded">';
	echo '<div id="post-body-content" class="has-sidebar-content">';
	echo '<div class="meta-box-sortabless">';

// Introduzione box
   echo '
	<div class="postbox">
		<h3 class="hndle">ACE - Certificazione Energetica</h3>
		<div class="inside" style="text-align:justify;">
		
			<p>ACE - Certificazione Energetica plugin &egrave; il plugin indispensabile per un siti di agenzia immobiliare o un portale di annunci immobiliari.</p>
			<p>CARATTERISTICHE:
			<ul style="list-style:circle inside;">
			<li>aggiunge ad ogni articolo (inteso come annuncio immobiliare) i dati relativi alla certificazione energetica obbligatori per gli annunci a partire dal 1 gennaio 2012.
			<li>aggiunge nella pagina di scrittura/modifica di un articolo un metabox in cui inserire i dati della certificazione energetica di ogni singolo annuncio.
			<li>aggiunge una colonna di riepilogo nella pagina edit.php per visualizzare pi&ugrave; rapidamente quali annunci sono correttamente impostati e quali no.
			</ul>
			</p>
		</div>
	</div>';
	
	
// Box di configurazione
   echo'
	<div class="postbox">
		<h3 class="hndle">CONFIGURAZIONE ACE PLUGIN</h3>
		<div class="inside" style="text-align:justify;">

		<form name="formapf" method="post" action="' . $_SERVER['REQUEST_URI'] . '">
			<fieldset class="options">
				<legend><h2>Opzioni principali:</h2> <br />
												
					<p><b>ATTIVAZIONE: Aggiungi i dati della Certificazione Energetica alla fine dei tuoi post: <font color="red">'. $options["CertificazioneEnergetica_add_ad_code"] . '</font></b>&nbsp;
					<select name="CertificazioneEnergetica_add_ad_code" id="CertificazioneEnergetica_add_ad_code" value="' . $options["CertificazioneEnergetica_add_ad_code"] .'" selected="'. $options["CertificazioneEnergetica_add_ad_code"] .'">
						<option value="'. $options["CertificazioneEnergetica_add_ad_code"] .'">
						<option value="Si">Si
						<option value="No">No
					</select>
					<br/><br/>
					<b>Codice dello specchietto ACE:</b><br/>
					<textarea name="CertificazioneEnergetica_ad_code" id="CertificazioneEnergetica_ad_code" cols="20" rows="10" style="width: 80%; font-size: 14px;" class="code">' . stripslashes($options["CertificazioneEnergetica_ad_code"]) . '</textarea></p>
				
					<p>Puoi modificare liberamente il codice HTML dello specchietto ACE.<br><br>Utilizza i seguenti <b>shortcode</b> per inserire i dati dove desideri:<br>
					- <b>[ACE]</b> per la classe della Certificazione<br>
					- <b>[IPE]</b> per l\'indice di prestazione energetica<br>
					- <b>[UDM]</b> per l\'unit&agrave; di misura<br> 
					</p>
					<p>Per maggiori informazioni o richieste di aiuto consulta la <a href="http://www.matteotirelli.com/myblog/wordpress-2/ace-certificazione-energetica-wordpress-plugin/">pagina del plugin</a></p>
				
				</legend>
			</fieldset>
			
			<fieldset class="options">
				<legend><h2>Opzioni Aggiuntive:</h2><br />
				<p><b>Mostra ACE ovunque:</b> <font color="red"><b>'. $options["CertificazioneEnergetica_show_all"] . '</b></font>&nbsp;
							<select name="CertificazioneEnergetica_show_all" id="CertificazioneEnergetica_show_all" value="' . $options["CertificazioneEnergetica_show_all"] .'">
							<option value="'. $options["CertificazioneEnergetica_show_all"] .'">
							<option value="Si">Si
							<option value="No">No
						</select><br />
				La certificazione energetica verr&agrave; mostrata esclusivamente negli articoli a piena pagina per default. Cambia questo parametro in "Si" per forzare il plugin a mostrare la certificazione energetica ovunque sia mostrato l\'articolo (inclusa la pagina index).
				</p>
				<p><b>Mostra riconoscimento:</b> <font color="red"><b>'. $options["CertificazioneEnergetica_add_credit"] . '</b></font>&nbsp;
							<select name="CertificazioneEnergetica_add_credit" id="CertificazioneEnergetica_add_credit" value="' . $options["CertificazioneEnergetica_add_credit"] .'">
							<option value="'. $options["CertificazioneEnergetica_add_credit"] .'">
							<option value="Si">Si
							<option value="No">No
						</select>
				</p>
				</legend>
			</fieldset>
			<p>Fate riferimento alla pagina <a href="http://www.matteotirelli.com/myblog/wordpress-2/ace-certificazione-energetica-wordpress-plugin/">ACE - Certificazione Energetica Plugin</a> per esempi di configurazione.</p>
			<p class="submit">
				<input type="submit" name="info_update" value="Salva configurazione &raquo;" />
			</p>

		</form>
		</div>
	</div>
	';


echo '</div>'; // / meta-box-sortabless
echo '</div>'; // / has-sidebar-content
echo '</div>'; // / has-sidebar sm-padded

echo '</div>'; // / metabox-holder has-right-sidebar
echo '</div>'; // /wrap
}
// ######################################
	
function CertificazioneEnergetica_get_option($option_name) {
	$option_name = stripslashes($option_name);
	$option_name = trim($option_name);
	return $option_name;
}

function CertificazioneEnergetica_ad_code() {
	global $posts;
	$options = get_option("CertificazioneEnergetica_opts");
	$CertificazioneEnergetica_add_ad_code = $options["CertificazioneEnergetica_add_ad_code"];
	//add ace code
	if ($CertificazioneEnergetica_add_ad_code != 'No'){
		$CertificazioneEnergetica_cusfld_ad_code = get_post_meta($posts[0]->ID, 'CertificazioneEnergetica_ad_code' , true);
		if ($CertificazioneEnergetica_cusfld_ad_code == '0'){
			$CertificazioneEnergetica_ad_code = '';
		}elseif (!empty($CertificazioneEnergetica_cusfld_ad_code)){
			$CertificazioneEnergetica_ad_code = '<p>'. $CertificazioneEnergetica_cusfld_ad_code . '</p>';
		}else{
			$CertificazioneEnergetica_ad_code = '<p>'. $options["CertificazioneEnergetica_ad_code"] . '</p>';
		}
		if (!empty($CertificazioneEnergetica_ad_code)) $CertificazioneEnergetica_ad_code = CertificazioneEnergetica_get_option($CertificazioneEnergetica_ad_code);
		
	}
	return $CertificazioneEnergetica_ad_code;
}

function CertificazioneEnergetica($text) {
	global $posts;
	//set default option
	CertificazioneEnergetica_set_option();
	//get ace options.
	$options = get_option("CertificazioneEnergetica_opts");
	$CertificazioneEnergetica_show_all = $options["CertificazioneEnergetica_show_all"];
	if (is_single() || ($CertificazioneEnergetica_show_all == 'Si')){
		
		$text .= CertificazioneEnergetica_ad_code();
		
		//add optional text
		$CertificazioneEnergetica_optional_txt = $options["CertificazioneEnergetica_optional_txt"];
		$CertificazioneEnergetica_cusfld_option_txt = get_post_meta($posts[0]->ID, 'CertificazioneEnergetica_option_txt' , true);
		if ($CertificazioneEnergetica_cusfld_option_txt != '0'){
			if (empty($CertificazioneEnergetica_cusfld_option_txt)){
				if (!empty($CertificazioneEnergetica_optional_txt)) $text .= CertificazioneEnergetica_get_option($CertificazioneEnergetica_optional_txt);
			}else{
				$text .= $CertificazioneEnergetica_cusfld_option_txt;
			}
		}
	}
	
	//aggiungi crediti
	$CertificazioneEnergetica_add_credit = $options["CertificazioneEnergetica_add_credit"];
	if ($CertificazioneEnergetica_add_credit != 'No'){
		$text .= '<p><font color="#B4B4B4" size="-2">Certificazione energetica generata con <a href="http://www.matteotirelli.com/myblog/wordpress-2/ace-certificazione-energetica-wordpress-plugin/" style="color: #B4B4B4; text-decoration:underline;">ACE - Certificazione Energetica Plugin</a> for wordpress.</font></p>';
	}
	return $text;
}

add_action('admin_menu', 'CertificazioneEnergetica_add_pages');
add_action('the_content', 'CertificazioneEnergetica',0);


/**********************************************
 * AGGIUNGO GLI SHORTCODE
 **********************************************/
 
function ACE_shortcode() { 
	global $post;
	$ace_sc = get_post_meta($post->ID, 'ace_post', true);
    return $ace_sc; 
}
add_shortcode('ACE', 'ACE_shortcode');

function IPE_shortcode() { 
	global $post;
	$ipe_sc = get_post_meta($post->ID, 'ipe_post', true);
    return $ipe_sc; 
}
add_shortcode('IPE', 'IPE_shortcode');

function UDM_shortcode() { 
	global $post;
	$udm_sc = get_post_meta($post->ID, 'unita_ipe', true);
    return $udm_sc; 
}
add_shortcode('UDM', 'UDM_shortcode');

/**********************************************
 * AGGIUNGO COLONNA IN EDIT.PHP
 **********************************************/

// Register the column
function CertificazioneEnergetica_ace_column_register( $columns ) {
	$columns['ace_post'] = __( 'Ace', 'certificazione-energetica' );
	return $columns;
}
add_filter( 'manage_edit-post_columns', 'CertificazioneEnergetica_ace_column_register' );

// Display the column content
function CertificazioneEnergetica_ace_column_display( $column_name, $post_id ) {
	if ( 'ace_post' != $column_name )
		return;
 
	$ace = get_post_meta($post_id, 'ace_post', true);
	if ( !$ace )
		$ace = '<em>' . __( 'undefined', 'certificazione-energetica' ) . '</em>';
	$ipe = get_post_meta($post_id, 'ipe_post', true);
	if ( !$ipe )
		$ipe = '<em>' . __( 'undefined', 'certificazione-energetica' ) . '</em>';
 
	echo $ace.' / '.$ipe;
}
add_action( 'manage_posts_custom_column', 'CertificazioneEnergetica_ace_column_display', 10, 2 );

// Register the column as sortable
function CertificazioneEnergetica_ace_column_register_sortable( $columns ) {
	$columns['ace_post'] = 'ace_post'; 
	return $columns;
}
add_filter( 'manage_edit-post_sortable_columns', 'CertificazioneEnergetica_ace_column_register_sortable' );