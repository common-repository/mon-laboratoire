<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed');

use MonLabo\Lib\{App, Lib, Options};

/**
 * Generate help box for shortcode [members_list]
 * @return string HTML code
 */
function MonLabo_help_shortcode_members_list(): string {
	$to_display = '';
	$options = Options::getInstance();
	if ( $options->uses['members_and_groups'] ) {
		$to_display .= '<div style="float:right;"><p><strong>Rendu n°1</strong> (liste de tous les membres du laboratoire):</p>
		<p><a href="' . plugins_url( 'images/members_list.png', __FILE__ ) . '"><img width="300" src="' . plugins_url( 'images/members_list.png', __FILE__ ) . '" alt="exemple" /></a></p></div>
		<p><strong>Où l’insérer&nbsp;?</strong> Sur une page d’équipe où sur la page de la liste des membres du laboratoire.</p>
		<p><strong>Contenu généré</strong> : Liste des membres du labo où d’une équipe.</p>
		<p><strong>Exemples d’utilisation</strong>:</p>
		<ul>
		<li><em>[members_list]</em> : Affiche la liste de tous les membres de l’équipe de la page en cours ou à défaut tous les membres du laboratoire. Ces membres sont séparés par catégories (Direction, Chercheurs permanents, Ingénieurs et techniciens, Posts doctorants et Étudiants).</li>
		<li><em>[members_list uniquelist="YES"]</em> : Affiche la liste de tous les membres du laboratoire par ordre alphabétique sans séparation de catégories.</li>
		<li><em>[members_list team="3"]</em> : Affiche la liste des membres de l’équipe n°3. Ces membres sont séparés en deux catégories : Chef(s) d’équipe(s) et Membres.</li>
		</ul>
		<p><strong>Options facultatives spécifiques à [members_list]</strong>:</p>
		<ul>
		<li><em>uniquelist="YES"</em> : Si mis à YES, ne distingue plus les chefs/cheffes d’équipe, les directeurs/directrices ni les catégories (<em>NO</em> par défaut).</li>
		<li><em>display_direction="YES|NO"</em> : Ajoute une catégorie <em>direction</em> séparant les directeurs/directrices (pour les unités) ou les chefs/cheffes (pour les équipes) des autres membres (<em>YES</em> par défaut).</li>
		<li><em>person="x"</em> : force l’affichage des personnes d’ID x (peut être une liste séparée par des virgules).</li>
		</ul>
		<p><strong>Options facultatives communes à [members_list], [members_table], [members_chart], [former_members_list], [former_members_table] et [former_members_chart]</strong>:</p>
		<div style="float:right;"><p><strong>Rendu n°2 </strong> (liste des membres d’une équipe):</p>
		<p><a href="' . plugins_url( 'images/members_list2.png', __FILE__ ) . '"><img width="300" src="' . plugins_url( 'images/members_list2.png', __FILE__ ) . '" alt="exemple" /></a></p></div>
		<ul>
		<li><em>team="x"</em> : N’affiche que les membres de l’équipe n°x (paramètre rempli automatiquement sur une page d’équipe)</li>
		<li><em>unit="x"</em> : N’affiche que les membres de l’unité n°x (peut être une liste séparée par des virgules)</li>
		<li><em>categories="x"</em> : N’affiche que les membres de la catégorie x (laisser vide pour choisir toutes les catégories, sinon choisir parmi <em>' . Lib::secured_implode( ",", App::get_MonLabo_persons_categories() ) . '</em>)</li>
		</ul>
		<p><strong>Options à items multiples</strong>:</p>
		<ul>
		<li>Les options <em>team, unit, categories</em> peuvent être des choix multiples en séparant les items par une virgule.</li>
		</ul>
		<div style="clear:both"></div>
		';
		return $to_display;
	}

	$to_display .= '<p>Désactivé</p>';
	return $to_display;
}

/**
 * Generate help box for shortcode [members_table]
 * @return string HTML code
 */
function MonLabo_help_shortcode_members_table(): string {
	$to_display = '';
	$options = Options::getInstance();
	if ( $options->uses['members_and_groups'] ) {
		$to_display .= '<div style="float:right;"><p><strong>Rendu n°1</strong> (liste de tous les membres du laboratoire):</p>
		<p><a href="' . plugins_url( 'images/members_table.png', __FILE__ ) . '"><img width="300" src="' . plugins_url( 'images/members_table.png', __FILE__ ) . '" alt="exemple" /></a></p></div>
		<p><strong>Où l’insérer&nbsp;?</strong> Sur une page d’équipe où sur la page de la liste des membres du laboratoire.</p>
		<p><strong>Contenu généré</strong> : Table des membres du labo où d’une équipe.</p>
		<p><strong>Exemples d’utilisation</strong>:</p>
		<ul>
		<li><em>[members_table]</em> : Affiche la liste de tous les membres de l’équipe de la page en cours ou à défaut tous les membres de la structure. Ces membres sont séparés par catégories (Chercheurs permanents, Ingénieurs et techniciens, Posts doctorants et Étudiants).</li>
		<li><em>[members_table team="3" presentation="compact"]</em> : Affiche la liste compacte des membres de l’équipe n°3. Ces membres sont séparés par catégories (Chercheurs permanents, Ingénieurs et techniciens, Posts doctorants et Étudiants).</li>
		</ul>
		<p><strong>Options facultatives spécifiques à [members_table] </strong>:</p>
		<ul>
		<li><em>presentation="normal|compact"</em> : Tableau complet ou résumé (<em>normal</em> par défaut).</li>
		<li><em>uniquelist="YES"</em> : Si mis à YES, ne distingue plus les chefs/cheffes d’équipe, les directeurs/directrices ni les catégories (<em>NO</em> par défaut).</li>
		<li><em>display_direction="YES|NO"</em> : Ajoute une catégorie <em>direction</em> séparant les directeurs/directrices (pour les unités) ou les chefs/cheffes (pour les équipes) des autres membres (<em>YES</em> par défaut).</li>
		</ul>
		<p><strong>Options facultatives communes à [members_list], [members_table], [members_chart], [former_members_list], [former_members_table] et [former_members_chart]</strong>:</p>
		<div style="float:right;"><p><strong>Rendu n°2 </strong> (liste compacte des membres d’une équipe):</p>
		<p><a href="' . plugins_url( 'images/members_table2.png', __FILE__ ) . '"><img width="300" src="' . plugins_url( 'images/members_table2.png', __FILE__ ) . '" alt="exemple" /></a></p></div>
		<ul>
		<li><em>team="x"</em> : N’affiche que les membres de l’équipe n°x (paramètre rempli automatiquement sur une page d’équipe)</li>
		<li><em>unit="x"</em> : N’affiche que les membres de l’unité n°x</li>
		<li><em>categories="x"</em> : N’affiche que les membres de la catégorie x (laisser vide pour choisir toutes les catégories, sinon choisir parmi <em>' . Lib::secured_implode( ",", App::get_MonLabo_persons_categories() ).'</em>)</li>
		</ul>
		<p><strong>Options à items multiples</strong>:</p>
		<ul>
		<li>Les options <em>team, unit, categories</em> peuvent être des choix multiples en séparant les items par une virgule.</li>
		</ul>
		<div style="clear:both"></div>
		';
		return $to_display;
	}
	$to_display .= '<p>Désactivé</p>';
	return $to_display;
}

/**
 * Generate help box for shortcode [members_chart]
 * @return string HTML code
 */
function MonLabo_help_shortcode_members_chart(): string {
	$to_display = '';
	$options = Options::getInstance();
	if ( $options->uses['members_and_groups'] ) {
		$to_display .= '<div style="float:right;">
		<p><strong>Rendu</strong> (liste de tous les membres du laboratoire):</p>
		<p><a href="' . plugins_url( 'images/members_chart.png', __FILE__ ) . '"><img width="300" src="' . plugins_url( 'images/members_chart.png', __FILE__ ) . '" alt="exemple" /></a></p>
		</div>
		<p><strong>Où l’insérer&nbsp;?</strong> Sur la page de la liste des membres du laboratoire.</p>
		<p><strong>Contenu généré</strong> : Organigramme des membres du labo.</p>
		<p><strong>Exemples d’utilisation</strong>:</p>
		<ul>
		<li><em>[members_chart]</em> : Affiche la liste de tous les membres du laboratoire. Ces membres sont séparés par catégories (Direction, Chercheurs statutaires, Ingénieurs et techniciens, Posts doctorants et Étudiants) en ligne et par équipe en colonne.</li>
		</ul>
		<p><strong>Options facultatives spécifiques à [members_chart]</strong>:</p>
		<ul>
		<li><em>display_direction="YES|NO"</em> : Ajoute une catégorie <em>direction</em> séparant les directeurs/directrices (pour les unités) ou les chefs/cheffes (pour les équipes) des autres membres (<em>YES</em> par défaut).</li>
		</ul>
		<p><strong>Options facultatives communes à [members_list], [members_table], [members_chart], [former_members_list], [former_members_table] et [former_members_chart]</strong>:</p>
		<ul>
		<li><em>team="x"</em> : N’affiche que les membres de l’équipe n°x (paramètre rempli automatiquement sur une page d’équipe)</li>
		<li><em>unit="x"</em> : N’affiche que les membres de l’unité n°x</li>
		<li><em>categories="x"</em> : N’affiche que les membres de la catégorie x (laisser vide pour choisir toutes les catégories, sinon choisir parmi <em>' . Lib::secured_implode( ",", App::get_MonLabo_persons_categories() ) . '</em>)</li>
		</ul>
		<p><strong>Options à items multiples</strong>:</p>
		<ul>
		<li>Les options <em>team, unit, categories</em> peuvent être des choix multiples en séparant les items par une virgule.</li>
		</ul>
		<div style="clear:both"></div>
		';
		return $to_display;
	}
	$to_display .= '<p>Désactivé</p>';
	return $to_display;
}

/**
 * Generate help box for shortcode [former_members_list]
 * @return string HTML code
 */
function MonLabo_help_shortcode_former_members_list(): string {
	$to_display = '';
	$options = Options::getInstance();
	if ( $options->uses['members_and_groups'] ) {
		$to_display .= '<div style="float:right;"><p><strong>Rendu</strong> (liste de tous les anciens membres permanents du laboratoire):</p>
		<p><a href="' . plugins_url( 'images/alumni_list2.png', __FILE__ ) . '"><img width="300" src="' . plugins_url( 'images/alumni_list2.png', __FILE__ ) . '" alt="exemple" /></a></p></div>
		<p><strong>Où l’insérer&nbsp;?</strong>  Sur une page d’équipe ou une page quelconque.</p>
		<p><strong>Contenu généré</strong> : Liste des anciens membres du labo.</p>
		<p><strong>Exemples d’utilisation</strong>:</p>
		<ul>
		<li><em>[former_members_list]</em> : Affiche la liste de tous les anciens membres du laboratoire.</li>
		<li><em>[former_members_list title="Former PhDs and Postdocs" categories="postdocs,students"]</em> : Affiche la liste de tous les anciens membres du laboratoire des catégories Doctorants et postdoctorants avec en titre Former PhDs and Postdocs.</li>
		</ul>
		<p><strong>Options facultatives spécifiques à [former_members_list] </strong>:</p>
		<ul>
		<li><em>title="y"</em> : Affiche le titre y avant la liste.</li>
		<li><em>years="y"</em> : Affiche les anciens membres avec les années de départ correspondantes (plage d’années séparées par un tiret - ou liste d’années séparées par une virgule).</li>
		<li><em>person="x"</em> : force l’affichage des personnes d’ID x (peut être une liste séparée par des virgules).</li>
		</ul>
		<p><strong>Options facultatives communes à [members_list], [members_table], [members_chart], [former_members_list], [former_members_table] et [former_members_chart]</strong>:</p>
		<ul>
		<li><em>team="x"</em> : N’affiche que les anciens membres de l’équipe n°x (paramètre rempli automatiquement sur une page d’équipe)</li>
		<li><em>unit="x"</em> : N’affiche que les anciens membres de l’unité n°x</li>
		<li><em>categories="x"</em> : N’affiche que les anciens membres de la catégorie x (laisser vide pour choisir toutes les catégories, sinon choisir parmi <em>' . Lib::secured_implode( ",", App::get_MonLabo_persons_categories() ) . '</em>)</li>
		</ul>
		<p><strong>Options à items multiples</strong>:</p>
		<ul>
		<li>Les options <em>team, unit, categories</em> peuvent être des choix multiples en séparant les items par une virgule.</li>
		</ul>
		';
		return $to_display;
	}
	$to_display .= '<p>Désactivé</p>';
	return $to_display;
}

/**
 * Generate help box for shortcode [former_members_table]
 * @return string HTML code
 */
function MonLabo_help_shortcode_former_members_table(): string {
	$to_display = '';
	$options = Options::getInstance();
	if ( $options->uses['members_and_groups'] ) {
		$to_display .= '
		<div style="float:right;"><p><strong>Rendu</strong>:</p>
		<p><a href="' . plugins_url( 'images/alumni_table.png', __FILE__ ) . '"><img width="300" src="' . plugins_url( 'images/alumni_table.png', __FILE__ ) . '" alt="exemple" /></a></p></div>
		<p><strong>Où l’insérer&nbsp;?</strong> Pas d’endroit spécifique nécessaire.</p>
		<p><strong>Contenu généré</strong> : Table des anciens membres du labo.</p>
		<p><strong>Exemples d’utilisation</strong>:</p>
		<ul>
		<li><em>[former_members_table]</em> : Affiche la table de tous les anciens membres du laboratoire.</li>
		<li><em>[former_members_table title="Former PhDs and Postdocs" categories="postdocs,students"]</em> : Affiche la table de tous les anciens membres du laboratoire des catégories Doctorants et postdoctorants avec en titre Former PhDs and Postdocs.</li>
		</ul>
		<p><strong>Options facultatives spécifiques à [former_members_table]</strong>:</p>
		<ul>
		<li><em>title="y"</em> : Affiche le titre y avant la liste.</li>
		<li><em>years="y"</em> : Affiche les anciens membres avec les années de départ correspondantes (plage d’années séparées par un tiret - ou liste d’années séparées par une virgule).</li>
		</ul>
		<p><strong>Options facultatives communes à [members_list], [members_table], [members_chart], [former_members_list], [former_members_table] et [former_members_chart]</strong>:</p>
		<ul>
		<li><em>team="x"</em> : N’affiche que les anciens membres de l’équipe n°x (paramètre rempli automatiquement sur une page d’équipe)</li>
		<li><em>unit="x"</em> : N’affiche que les anciens membres de l’unité n°x</li>
		<li><em>categories="x"</em> : N’affiche que les anciens membres de la catégorie x (laisser vide pour choisir toutes les catégories, sinon choisir parmi <em>' . Lib::secured_implode( ",", App::get_MonLabo_persons_categories() ) . '</em>)</li>
		</ul>
		<p><strong>Options à items multiples</strong>:</p>
		<ul>
		<li>Les options <em>team, unit, categories</em> peuvent être des choix multiples en séparant les items par une virgule.</li>
		</ul>
		';
		return $to_display;
	}
	$to_display .= '<p>Désactivé</p>';
	return $to_display;
}

/**
 * Generate help box for shortcode [former_members_chart]
 * @return string HTML code
 */
function MonLabo_help_shortcode_former_members_chart(): string {
	$to_display = '';
	$options = Options::getInstance();
	if ( $options->uses['members_and_groups'] ) {
		$to_display .= '<div style="float:right;">
		<p><strong>Rendu</strong> (liste de tous les anciens membres du laboratoire):</p>
		<p><a href="' . plugins_url( 'images/alumni_chart.png', __FILE__ ) . '"><img width="300"  src="' . plugins_url( 'images/alumni_chart.png', __FILE__ ) . '" alt="exemple" /></a></p>
		</div>
		<p><strong>Où l’insérer&nbsp;?</strong> Sur la page de la liste des membres du laboratoire.</p>
		<p><strong>Contenu généré</strong> : Organigramme des anciens membres du labo.</p>
		<p><strong>Exemples d’utilisation</strong>:</p>
		<ul>
		<li><em>[members_chart]</em> : Affiche la liste de tous les anciens membres du laboratoire. Ces membres sont séparés par catégories (Direction, Chercheurs statutaires, Ingénieurs et techniciens, Posts doctorants et Étudiants) en ligne et par équipe en colonne.</li>
		</ul>
		<p><strong>Options facultatives spécifiques à [former_members_chart]</strong>:</p>
		<ul>
		<li><em>years="y"</em> (facultatif) : Affiche les anciens membres avec les années de départ correspondantes (plage d’années séparées par un tiret - ou liste d’années séparées par une virgule).</li>
		</ul>
		<p><strong>Options facultatives communes à [members_list], [members_table], [members_chart], [former_members_list], [former_members_table] et [former_members_chart]</strong>:</p>
		<ul>
		<li><em>team="x"</em> : N’affiche que les anciens membres de l’équipe n°x (paramètre rempli automatiquement sur une page d’équipe)</li>
		<li><em>unit="x"</em> : N’affiche que les anciens membres de l’unité n°x</li>
		<li><em>categories="x"</em> : N’affiche que les anciens membres de la catégorie x (laisser vide pour choisir toutes les catégories, sinon choisir parmi <em>' . Lib::secured_implode( ",", App::get_MonLabo_persons_categories() ) . '</em>)</li>
		</ul>
		<p><strong>Options à items multiples</strong>:</p>
		<ul>
		<li>Les options <em>team, unit, categories</em> peuvent être des choix multiples en séparant les items par une virgule.</li>
		</ul>
		<div style="clear:both"></div>
		';
		return $to_display;
	}
	$to_display .= '<p>Désactivé</p>';
	return $to_display;
}

/**
 * Generate help box for shortcode [publications_list]
 * @return string HTML code
 */
function MonLabo_help_shortcode_publications_list(): string {
	$to_display = '';
	$options = Options::getInstance();
	if ( 'aucun' === $options->publication_server_type ) {
		$to_display .= '<p>Désactivé</p>';
		return $to_display;
	}
	$options4 = get_option( 'MonLabo_settings_group4' );

	$to_display .= '<div style="float:right;"><p><strong>Rendus</strong>:</p>
			<p><a href="' . plugins_url( 'images/publications_list2.png', __FILE__ ) . '"><img width="300" src="' . plugins_url( 'images/publications_list2.png', __FILE__ ) . '" alt="exemple" /></a></p>
			<p><a href="' . plugins_url( 'images/publications_list.png', __FILE__ ) . '"><img width="300" src="' . plugins_url( 'images/publications_list.png', __FILE__ ) . '" alt="exemple" /></a></p></div>';
	$to_display .= '<p><strong>Où l’insérer&nbsp;?</strong><br /> - Sur une page identifiée (équipe, unité ou page d’un personnel)<br /> - ou sur une page quelconque mais en étant contraint d’utiliser des "options complémentaire" pour identifier manuellement les publications.</p>
	<p><strong>Contenu généré</strong> : Insère automatiquement les publications de l’utilisateur ou de l’équipe issues d’une base de donnée extérieure qui peut être :<br />
	&nbsp;- soit <a href="http://hal.science/" target="_blank" rel="noopener noreferrer">HAL</a><br />
	&nbsp;- soit la <a href="' . $options4['MonLabo_DescartesPubmed_api_url'] . '" target="_blank" rel="noopener noreferrer">base de donnée du serveur Descartes Publi (Biomédicale)</a> (activable sur la <a href="admin.php?page=MonLabo_config&amp;tab=tab_configpublications">page d’option de publications</a>).</p>
	';
		$to_display .= '<p><strong>Exemples d’utilisation</strong>:</p>';
		$to_display .= '
		<ul>
			<li><em>[publications_list]</em> => Génère une liste de publications.</li>
			<li><em>[publications_list title="Mes publications à moi"]</em> => Ajoute ou change le titre initial.</li>';
		$to_display .= '
			<li><em>[publications_list years="2012-9999"]</em> => Récupère les dernières publications jusqu’en 2012.</li>
		</ul>
		';
	$to_display .= '
		<p><strong>Configuration préalable</strong>:</p>
		<p>Pour pouvoir interroger cette base de données extérieure, il faut au préalable indiquer dans MonLabo les identifiants des personnels, des équipes et éventuellement de la structure de la base de donnée extérieure :<br />
		Pour HAL (cf. <a href="https://aurehal.archives-ouvertes.fr/structure/index"> module de consultation de la liste des structures/équipes.</a>):<br />
		&nbsp;- champs "ID HAL de l’auteur" dans la <a href="admin.php?page=MonLabo_edit_members_and_groups&amp;tab=tab_person&amp;lang=all">liste des personnels</a> (rubrique "autres")<br />
		&nbsp;- champs "Identifiant HAL de l’équipe" dans la <a href="admin.php?page=MonLabo_edit_members_and_groups&amp;tab=tab_team&amp;lang=all">liste des équipes</a> (rubrique "autres")<br />
		&nbsp;- champs "Identifiant HAL de la structure" dans la <a href="admin.php?page=MonLabo_config&amp;tab=tab_person&amp;lang=all">Coordonnées</a><br />
		Pour la base de donnée Descartes Publi:<br />
		&nbsp;- champs "ID d’auteur Descartes Publi" dans la <a href="admin.php?page=MonLabo_edit_members_and_groups&amp;tab=tab_person&amp;lang=all">liste des personnels</a> (rubrique "autres")<br />
		&nbsp;- champs "ID d’equipe Descartes Publi" dans la <a href="admin.php?page=MonLabo_edit_members_and_groups&amp;tab=tab_team&amp;lang=all">liste des équipes</a> (rubrique "autres")
		</p>
		<p>Si Les données ne sont pas renseignées pour le serveur Descartes Publi, les publications sont issues de HAL.</p>
		';
	$to_display .= '<p><strong>Options facultatives basiques</strong>:</p>';
	$to_display .= '<ul>
					<li><em>title="Mon titre personnel"</em> : Insère ou change le titre au début de la liste de publications.</li>';
	$to_display .= '  <li><em>years="A"</em> : Récupère les publications de l’année A (il est possible de pouvoir indiquer une plage d’années en séparant deux valeurs par le carractère "-").</li>';
	$to_display .= '</ul>';
	$to_display .= '<p><strong>Options factultatives complémentaires</strong>:</p>';
	$to_display .= '<ul>';
	$to_display .= '  <li><em>persons="X,Y,Z"</em> : Force la récupération des publications pour les personnes n°X,Y et Z de l’extension MonLabo (utiliser le carractère "*" pour demander les publications de toutes les personnes entrées dans MonLabo). Si cette option est utilisée, les options <em>teams</em> et <em>units</em> sont ignorées.</li>';
	$to_display .= '  <li><em>teams="X,Y,Z"</em> : Force la récupération des publications pour les équipes n°X,Y et Z de l’extension MonLabo (utiliser le carractère "*" pour demander les publications de toutes les équipes entrées dans MonLabo).</li>';
	$to_display .= '  <li><em>units="X,Y,Z"</em> : Force la récupération des publications pour les unités n°X,Y et Z de l’extension MonLabo (utiliser le carractère "*" pour demander les publications de toutes les unités entrées dans MonLabo).</li>';
/*		$to_display .= '  <li><em>equipes_="X,Y,Z"</em> : Force la récupération des publications pour les équipes n°X,Y et Z de l’extension MonLabo. Utiliser le carractère "*" pour demander toutes les publications de toutes les équipes entrées dans MonLabo.</li>';
(choix du n° pour <a href="'.$options4['MonLabo_DescartesPubmed_api_url'].'?html_teamslist">Descartes Publi</a> ou <a href="https://aurehal.archives-ouvertes.fr/structure/index">HAL</a>)*/
	$to_display .= '  <li><em>lang="[fr|en]"</em> : Affiche les publications dans la langue indiquée (par défaut c\'est la langue de l’interface de WordPress).</li>';
	$to_display .= '  <li><em>limit="L"</em> : Limite le nombre de publications affichées au nombre L.</li>';
	$to_display .= '  <li><em>offset="F"</em> : <i>(ne marche pas avec HAL)</i> Décale l’affichage de F publications et affiche les publications restantes.</li>';
	$to_display .= '  <li><em>base="[descartespubli|hal]"</em> : force l’utilisation de la base <i>DescartesPubli</i> ou <i>hal</i> si les deux bases sont autorisées.</li>';
	$to_display .= '  <li><em>debug="true"</em> : Permet d’afficher, pour déboguage, l’adresse de la requête qui va récupérer les publications.</li>';
	$to_display .= '</ul>';
	$to_display .= '<p><strong>Options facultatives expertes pour HAL</strong>:<a href="https://hal.science/"><img width="61" height="30" class="wp-image-8 alignright wp-post-image" src="' . plugins_url( 'images/logoHAL.png', __FILE__ ) . '" alt="logo HAL" /></a></p>';
	$to_display .= '<ul>';
	$to_display .= '  <li><em>hal_struct="A;B;C"</em>, écrase les paramètres <em>persons</em>, <em>teams</em> et <em>unit</em>) : Récupère les publications des structures au numéro HAL A, B et C (cf. <a href="https://aurehal.archives-ouvertes.fr/structure/index">module HAL de consultation de la liste des structures/équipes</a>).</li>';
	$to_display .= '  <li><em>hal_idhal="A;B;C"</em>, écrase les paramètres <em>persons</em>, <em>teams</em> et <em>unit</em>) : Récupère les publications des auteurs qui ont pour IdHal A, B, C (cf. <a href="https://aurehal.archives-ouvertes.fr/person/index">module HAL de consultation des auteurs</a>).</li>';
	$to_display .= '  <li><em>hal_typepub="Type 1 de publication,Type 2 de publication"</em> : Récupère les publications du type indiqué (exemples de valeurs possibles:';
	$to_display .= '	<em>ART, COMM, POSTER, OUV, COUV, DOUV, PATENT, REPORT, THESE...</em>). Par défaut tous les types sont selectionnés (<a href="https://api.archives-ouvertes.fr/ref/doctype">liste des types disponibles</a>).</li>';
	$to_display .= '</ul>';
	if ( 'hal' !== $options->publication_server_type ) {
		$to_display .= '<p><strong>Options facultatives expertes pour Descartes Publi</strong> : <img width="61" height="34" class="wp-image-8 alignright wp-post-image" src="' . plugins_url('images/DescartesPubli.logo.png', __FILE__) . '" alt="logo DescartesPubli" /></p>';
		$to_display .= '<ul>';
		$to_display .= '<li><em>descartes_alias="Unnom P"</em> (écrase les paramètres <em>persons</em>, <em>teams</em> et <em>unit</em>) : Récupère les publications comprenant comme auteur l’alias indiqué (Souvent Nom et initiale du prénom).</li>';
		$to_display .= '<li><em>descartes_auteurid="Z"</em> (écrase les paramètres <em>persons</em>, <em>teams</em> et <em>unit</em>) : Récupère les publications de l’auteur au numéro DescartesPubli n°Z (choix du n° <a href="' . $options4['MonLabo_DescartesPubmed_api_url'] . '?html_userslist">ici</a>).</li>';
		$to_display .= '<li><em>descartes_unite="X"</em> (écrase les paramètres <em>persons</em>, <em>teams</em> et <em>unit</em>) : Récupère les publications de l’unité au numéro DescartesPubli n°X. (choix du n° <a href="' . $options4['MonLabo_DescartesPubmed_api_url'] . '?html_unitslist">ici</a>)</li>';
		$to_display .= '<li><em>descartes_equipe="Y"</em> (écrase les paramètres <em>persons</em>, <em>teams</em> et <em>unit</em>) : Récupère les publications de l’équipe au numéro DescartesPubli n°Y (choix du n° <a href="' . $options4['MonLabo_DescartesPubmed_api_url'] . '?html_teamslist">ici</a>). Utiliser le carractère "*" pour demander toutes les publications des équipes entrées dans MonLabo.</li>';
		$to_display .= '<li><em>descartes_typepub="Type de publication"</em> : Récupère les publications du type indiqué (choix parmis les valeurs possibles:';
		$to_display .= '	<em>*, pub_journaux, pub_livres, pub_chapitres, pub_theses, pub_rapports, pub_presse, pub_communications, pub_brevets</em>). Par défaut le type choisi est pub_journaux.</li>';
		$to_display .= '<li><em>descartes_nohighlight</em> (option sans paramètre) : Ne met pas en gras les alias des membre de l’unité ou de l’équipe sélectionnée. Par défaut, cette option sans paramètre est absente.</li>';
		$to_display .= '<li><em>descartes_orga_types="[aucun|par_titre|par_publication]"</em> : Séléctionne la façon d’afficher les changement de types de publications.  Par défaut vaut <em>par_titre</em>.</li>';
		$to_display .= '<li><em>descartes_format="[html_default|html_hal]"</em> : Séléctionne la façon d’afficher les publications. Par défaut vaut <em>html_default</em>.</li>';
		$to_display .= '</ul>';
	}
	return $to_display;
}

/**
 * Generate help box for shortcode [teams_list]
 * @return string HTML code
 */
function MonLabo_help_shortcode_teams_list(): string {
	$to_display = '';
	$options = Options::getInstance();
	if ( $options->uses['members_and_groups'] ) {
		$to_display .= '
		<div style="float:right;"><p><strong>Rendu</strong>:</p>
		<p><a href="' . plugins_url( 'images/teams_list.png', __FILE__ ) . '"><img width="300" src="' . plugins_url( 'images/teams_list.png', __FILE__ ) . '" alt="exemple" /></a></p></div>
		<p><strong>Où l’insérer&nbsp;?</strong> Pas d’endroit spécifique nécessaire.</p>
		<p><strong>Contenu généré</strong> : Liste des équipes avec leur logos et leur thématiques.</p>
		<p><strong>Exemples d’utilisation</strong>:</p>
		<ul>
		<li><em>[teams_list]</em> :  Affiche la liste de toutes les équipes.</li>
		<li><em>[teams_list group="3,4"]</em> : Affiche la liste de toutes les équipes attachées au groupes d’équipes n°3 et 4.</li>
		<li><em>[teams_list group="3" teams_publications_page="Mes-publications-d-equipe.php"]</em> : Affiche la liste de toutes les équipes attachées au groupe n°3 et ajoute un lien <em>"Team publication"</em> pointant vers la page Mes-publications-d-equipe.php</li>
		</ul>
		<p><strong>Options facultatives</strong> :</p>
		<ul>
		<li><em>group="x"</em> : N’affiche que la liste des équipes  du groupe d’équipe au numéro indiqué (x peut être une liste séparée par des vigules)</li>
		<li><em>unit="x"</em> : N’affiche que les équipes de l’unité n°x (x peut être une liste séparée par des vigules) (paramètre rempli automatiquement sur une page d’unité)</li>
		<li><em>team="x"</em> : force l’affichage des équipes d’ID x (peut être une liste séparée par des virgules)</li>
		<li><em>teams_publications_page="url"</em> : Adresse de renvoi pour l’affichage optionnel des publications. Ajoute à chaque équipe un lien <em>"Team publication"</em> pointant vers l’url et ayant comme paramètre <em>"?equipe=N"</em> (N est le numéro de l’équipe dans la base Descartes Publi).</li>
		</ul>
		';
		return $to_display;
	}
	$to_display .= '<p>Désactivé</p>';
	return $to_display;
}

/**
 * Generate help box for shortcode [perso_panel]
 * @return string HTML code
 */
function MonLabo_help_shortcode_perso_panel(): string {
	$to_display = '';
	$options = Options::getInstance();
	if ( $options->uses['members_and_groups'] ) {
		$to_display .= '
		<div style="float:right;"><p><strong>Rendu</strong>:</p>
		<p><a href="' . plugins_url( 'images/perso_panel.png', __FILE__ ) . '"><img width="300" src="' . plugins_url( 'images/perso_panel.png', __FILE__ ) . '" alt="exemple" /></a></p></div>
		<p><strong>Où l’insérer&nbsp;?</strong> Au début de chaque page perso d’un utilisateur.</p>
		<p><strong>Contenu généré</strong> : Insère un encart avec les coordonnées de l’utilisateur. Les coordonnées sont récupérées depuis la liste des personnels dont le champs "ID page web" correspond à la page perso. L’image de l’utilisateur est l’"image à la une" de la page perso où est insérée cette balise.</p>
		<p><strong>Exemple d’utilisation</strong>:</p>
		<ul><li><em>[perso_panel]</em></li></ul>
		<p><strong>Options facultatives </strong> :</p>
		<ul>
		<li><em>person="x"</em> : force l’affichage du perso_panel de l’utilisateur d’ID x.</li>
		</ul>
		';
		return $to_display;
	}
	$to_display .= '<p>Désactivé</p>';
	return $to_display;
}

/**
 * Generate help box for shortcode [team_panel]
 * @return string HTML code
 */
function MonLabo_help_shortcode_team_panel(): string {
	$to_display = '';
	$options = Options::getInstance();
	if ( $options->uses['members_and_groups'] ) {
		$to_display .= '
		<div style="float:right;"><p><strong>Rendu</strong>:</p>
		<p><a href="' . plugins_url( 'images/team_panel.png', __FILE__ ) . '"><img width="300" src="' . plugins_url( 'images/team_panel.png', __FILE__ ) . '" alt="exemple" /></a></p></div>
		<p><strong>Où l’insérer&nbsp;?</strong> Au début de chaque page d’équipe.</p>
		<p><strong>Contenu généré</strong> : Insère un encart avec le nom de l’équipe, les chefs d’équipe et les thématiques. Les informations sont récupérées depuis la liste des équipes dont le champs "ID page web" correspond à la page d’équipe.</p>
		<p><strong>Exemple d’utilisation</strong>:</p>
		<p><strong>Options facultatives</strong> :</p>
		<ul>
		<li><em>team="x"</em>: force l’affichage du team_panel de l’équipe d’ID x.</li>
		</ul>
		<ul><li><em>[team_panel]</em></li></ul>
		';
		return $to_display;
	}
	$to_display .= '<p>Désactivé</p>';
	return $to_display;
}

///////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////
/**
 * Display help menu
 * @return void
 */
function MonLabo_help_render() {
	$inactive1_pre = '';
	$inactive1_post = '';
	$inactive1_final = '';

	$options = Options::getInstance();
	if ( !$options->uses['members_and_groups'] ) {
		$inactive1_pre .= '<s>';
		$inactive1_post .= '</s>';
		$inactive1_final .= ' ' . __( '(disabled)', "mon-laboratoire" );
	}
	$helplogo = '<span class="dashicons dashicons-editor-help" style="opacity: 0.5;">&nbsp;</span>';
	$members_list_info      	= boostrap_info_modal( $helplogo . '[members_list]',      	MonLabo_help_shortcode_members_list()      		);
	$members_table_info     	= boostrap_info_modal( $helplogo . '[members_table]',     	MonLabo_help_shortcode_members_table()     		);
	$members_chart_info     	= boostrap_info_modal( $helplogo . '[members_chart]',     	MonLabo_help_shortcode_members_chart()     		);
	$former_members_list_info	= boostrap_info_modal( $helplogo . '[former_members_list]', MonLabo_help_shortcode_former_members_list(), '[former_members_list] <small><small>ou [alumni_list]</small></small>');
	$former_members_table_info	= boostrap_info_modal( $helplogo . '[former_members_table]',MonLabo_help_shortcode_former_members_table(),'[former_members_table] <small><small>ou [alumni_table]</small></small>');
	$former_members_chart_info  = boostrap_info_modal( $helplogo . '[former_members_chart]',MonLabo_help_shortcode_former_members_chart(),'[former_members_chart] <small><small>ou [alumni_chart]</small></small>');
	$team_list_info         	= boostrap_info_modal( $helplogo . '[teams_list]',        	MonLabo_help_shortcode_teams_list()        		);
	$publications_list_info 	= boostrap_info_modal( $helplogo . '[publications_list]', 	MonLabo_help_shortcode_publications_list() 		);
	$perso_panel_info       	= boostrap_info_modal( $helplogo . '[perso_panel]',       	MonLabo_help_shortcode_perso_panel()       		);
	$team_panel_info        	= boostrap_info_modal( $helplogo . '[team_panel]',        	MonLabo_help_shortcode_team_panel()        		);

	echo( '<div class="wrap MonLabo_admin">' );
	echo( '<h1>'. __( 'Mini documentation', 'mon-laboratoire' ). ' :</h1>' );
	echo( '<h4>'. __( 'This WordPress plugin allows, on a unified interface, to manage the pages of teams and staff of a research laboratory.<br /> It simplifies the update of members and teams, their information and their publication list (extracted from HAL or an in-house database such as Paris Descartes database).', 'mon-laboratoire' ) . '</h4>' );
	$video_link = 'https://www.canal-u.tv/chaines/casuhal/afficher-une-liste-de-publications-dans-wordpress-les-plugins-hal-et-monlaboratoire?t=1137';
	echo('<div style="float:right;"><a href="' . $video_link . '"><img alt="Video thumbnail" src="' . plugins_url( 'images/casuhal-video.png', __FILE__ ) . '" width="250" height="135"></a></div>' );
	echo( '<h2>'. __( 'Presentation of functionalities in video', 'mon-laboratoire' ). ' :</h2>' );
	echo( '<p>' );
	printf( __( '<a href="%s">You can find here</a> a video presentation (in french) of functionalites at congress "Journées Casuhal 2022".', 'mon-laboratoire' ), $video_link );
	echo ( '</p>' );
	echo( '<h2>'. __( 'Shortcodes', 'mon-laboratoire' ). ' :</h2>' );
	echo( '<p>'. __( 'This plugin adds the below shortcodes. These "shortcodes" are to be inserted in the pages to automatically generate contents.', 'mon-laboratoire' ) . '</p>' );
	echo( '<ol>' );
	echo( '<li>' );
	printf(

		__( 'Display of staff...<ul><li>...%1$s that are active: by list %2$s, by table %3$s, by flowchart %4$s </li><li>... %5$s who are former members: by list %6$s, by table %7$s, by organization chart %8$s </li></ul>'	, 'mon-laboratoire'  )
		, $inactive1_pre
		, $inactive1_post . $members_list_info .  $inactive1_pre
		, $inactive1_post . $members_table_info . $inactive1_pre
	 	, $inactive1_post . $members_chart_info . $inactive1_final
	 	, $inactive1_pre
		, $inactive1_post . $former_members_list_info . $inactive1_pre
		, $inactive1_post . $former_members_table_info . $inactive1_pre
		, $inactive1_post . $former_members_chart_info . $inactive1_final
	);
	echo( '</li>' );
	printf(
		'<li> ' . __( '%s Displaying teams by list %s' , 'mon-laboratoire' ) . ' </li>'
		, $inactive1_pre
		, $inactive1_post . $team_list_info . $inactive1_final
	);

	$inactive2_pre = '';
	$inactive2_post = '';
	$inactive2_final = '';
	if ( 'aucun' === $options->publication_server_type ) {
		$inactive2_pre .= '<s>';
		$inactive2_post .= '</s>';
		$inactive2_final .= ' ' . __( '(disabled)', "mon-laboratoire" );
	}
	printf(
		'<li> ' . $inactive2_pre . __( 'Displaying publications %s', 'mon-laboratoire' ) . ' </li>'
		, $inactive2_post . $publications_list_info . $inactive2_final
	);

	printf(
		'<li> ' . __( '%s Displaying the header of personal pages %s or the header of team pages %s' , 'mon-laboratoire' ) . ' </li>'
		, $inactive1_pre
		, $inactive1_post . $perso_panel_info . $inactive1_pre
		, $inactive1_post . $team_panel_info . $inactive1_final
	);
	echo( '</ol>' );

	$MonLabo_menu_info = get_plugin_data( plugin_dir_path( __FILE__ ) .'../mon-laboratoire.php' );
	$user_data = get_userdata( get_current_user_id() );
	$user_email = '';
	if ( ! empty( $user_data ) ) {
		$user_email = $user_data->data->user_email; //@phan-suppress-current-line PhanRedefinedClassReference
	}
	echo ( '<h1>' . __( 'Help us by reporting that you are a user', 'mon-laboratoire' ) . ' :</h1>' );
	printf( __( 'You can allow the developers to publish on the <a href="%s">plugin site</a> that you are a user of MyLabo. This will do us a great service to show that this plugin is being used.', 'mon-laboratoire' ) . '<br />', $MonLabo_menu_info["PluginURI"] );
	echo( '<form method="get" action="http://monlabo.org/add_to_registred_users.php">' );
	echo( '<div class="input-group"><label>' . __( 'Email', 'mon-laboratoire' ) . ' ( ' . __( 'optional', 'mon-laboratoire' ) . ' )</label>' );
	echo( '<input type=\'text\' name=\'email\' value="' . $user_email . '" />' );
	echo( '<input type=\'hidden\' name=\'url\' value="' . site_url() . '" />' );
	echo( '<input type=\'hidden\' name=\'version\' value="' . $MonLabo_menu_info['Version'] . '" />' );
	echo( ' &nbsp;&nbsp;<input type="submit" name="submit" id="submit" class="button button-primary" value="' . __( 'Tell the authors that you are using the plugin on', 'mon-laboratoire' ) . ' ' . site_url() . '"  /></div></form>' );

	echo ( '<h1>' . __( 'Get informed', 'mon-laboratoire' ) . ' :</h1>' );
	_e( 'You can also subscribe to the following mailing lists', 'mon-laboratoire' );
	echo(  ' :<ul>' );
	echo( "<li><div class='input-group'><label>" . __( 'Announcements MonLabo', 'mon-laboratoire' ) . '</label>  (' . __( 'ex: new versions', 'mon-laboratoire' ) . ") " . boostrap_button_link( __( 'Subscribe now', 'mon-laboratoire' ), "https://listes.services.cnrs.fr/wws/subscribe/annonces_monlabo?previous_action=info", "button-primary" ) . "</div></li>" );

	echo( "<li><div class='input-group'><label>" . __( 'MonLabo mailing list', 'mon-laboratoire' ) . '</label> (' . __( 'discussions of the plugin users', 'mon-laboratoire' ) . ") " . boostrap_button_link( __( 'Subscribe now', 'mon-laboratoire' ), "https://listes.services.cnrs.fr/wws/subscribe/monlabo?previous_action=info", "button-primary" ) . "</div></li>" );
	echo( '</ul></div>' );
}
 ?>
