/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009-2015
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre Affero GPL 3 <https://www.gnu.org/licenses/agpl-3.0.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU Affero General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Publique Générale GNU Affero pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Publique Générale GNU Affero avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

var nb_caracteres_max = 1000;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Permettre l'utilisation de caractères spéciaux
// ////////////////////////////////////////////////////////////////////////////////////////////////////

var tab_entite_nom   = new Array('&sup2;','&sup3;','&times;','&divide;','&minus;','&pi;','&rarr;','&radic;','&infin;','&asymp;','&ne;','&le;','&ge;');
var tab_entite_val   = new Array('²'     ,'³'     ,'×'      ,'÷'       ,'–'      ,'π'   ,'→'     ,'√'      ,'∞'      ,'≈'      ,'≠'   ,'≤'   ,'≥'   );
var imax             = tab_entite_nom.length;
function entity_convert(string)
{
  for(i=0;i<imax;i++)
  {
    var reg = new RegExp(tab_entite_nom[i],"g");
    string = string.replace(reg,tab_entite_val[i]);
  }
  return string;
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Pour mémoriser les liens des ressources et les contenus des commentaires.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

var tab_lien = new Array();
var tab_comm = new Array();

// jQuery !
$(document).ready
(
  function()
  {

    // initialisation
    var memo_text_delete = '';
    var memo_objet       = null;
    var matiere_id = 0;
    var matiere_nom = '';
    var element_nom = '';
    var objet = false;
    var images = new Array();
    images[1]  = '';
    images[1] += '<q class="n1_edit" data-action="edit" title="Éditer ce domaine."></q>';
    images[1] += '<q class="n1_add"  data-action="add"  title="Ajouter un domaine à la suite."></q>';
    images[1] += '<q class="n1_move" data-action="move" title="Déplacer ce domaine (et renuméroter)."></q>';
    images[1] += '<q class="n1_del"  data-action="del"  title="Supprimer ce domaine ainsi que tout son contenu."></q>';
    images[1] += '<q class="n2_add"  data-action="add"  title="Ajouter un thème au début de ce domaine (et renuméroter)."></q>';
    images[2]  = '';
    images[2] += '<q class="n2_edit" data-action="edit" title="Éditer ce thème."></q>';
    images[2] += '<q class="n2_add"  data-action="add"  title="Ajouter un thème à la suite (et renuméroter)."></q>';
    images[2] += '<q class="n2_move" data-action="move" title="Déplacer ce thème (et renuméroter)."></q>';
    images[2] += '<q class="n2_del"  data-action="del"  title="Supprimer ce thème ainsi que tout son contenu (et renuméroter)."></q>';
    images[2] += '<q class="n3_add"  data-action="add"  title="Ajouter un item au début de ce thème (et renuméroter)."></q>';
    images[3]  = '';
    images[3] += '<q class="n3_edit" data-action="edit" title="Éditer cet item."></q>';
    images[3] += '<q class="n3_add"  data-action="add"  title="Ajouter un item à la suite (et renuméroter)."></q>';
    images[3] += '<q class="n3_move" data-action="move" title="Déplacer cet item (et renuméroter)."></q>';
    images[3] += '<q class="n3_fus"  data-action="fus"  title="Fusionner avec un autre item (et renuméroter)."></q>';
    images[3] += '<q class="n3_del"  data-action="del"  title="Supprimer cet item (et renuméroter)."></q>';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Charger le form zone_elaboration_referentiel en ajax
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_choix_referentiel q.modifier').click
    (
      function()
      {
        id = $(this).parent().attr('id');
        matiere_id  = id.substring(3);
        matiere_nom = $(this).parent().prev().prev().text();
        afficher_masquer_images_action('hide');
        new_label = '<label for="'+id+'" class="loader">Demande envoyée...</label>';
        $(this).after(new_label);
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=Voir'+'&matiere='+matiere_id,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $.fancybox( '<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+'</label>' , {'centerOnScroll':true} );
              $('label[for='+id+']').remove();
              afficher_masquer_images_action('show');
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==false)
              {
                $.fancybox( '<label class="alerte">'+responseJSON['value']+'</label>' , {'centerOnScroll':true} );
              }
              else
              {
                $('#zone_choix_referentiel').hide();
                initialiser_action_groupe();
                eval( responseJSON['script'] );
                $('#zone_elaboration_referentiel').html('<p><span class="tab"></span>Tout déployer / contracter :<q class="deployer_m2"></q><q class="deployer_n1"></q><q class="deployer_n2"></q><q class="deployer_n3"></q><br /><span class="tab"></span><button id="fermer_zone_elaboration_referentiel" type="button" class="retourner">Retour à la liste des matières</button></p>'+'<h2>'+matiere_nom+'</h2>'+responseJSON['html']);
              }
              $('label[for='+id+']').remove();
              afficher_masquer_images_action('show');
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Indiquer le nombre de caractères restant autorisés dans le textarea
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_elaboration_referentiel').on
    (
      'keyup',
      '#f_comm',
      function()
      {
        afficher_textarea_reste( $(this) , nb_caracteres_max );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour fermer la zone compet
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_elaboration_referentiel').on
    (
      'click',
      '#fermer_zone_elaboration_referentiel',
      function()
      {
        $('#zone_elaboration_referentiel').html("");
        afficher_masquer_images_action('show'); // au cas où on serait en train d'éditer qq chose
        $('#zone_choix_referentiel').show('fast');
        return false;
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur l'image pour Ajouter un domaine, ou un thème, ou un item
// Clic sur l'image pour Éditer un domaine, ou un thème, ou un item
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function afficher_form_edition( action, objet )
    {
      // On récupère le contexte de la demande : n1 ou n2 ou n3
      var contexte = objet.attr('class').substring(0,2);
      var conteneur = (contexte=='n3') ? 'b' : 'span' ;
      afficher_masquer_images_action('hide');
      // On créé le formulaire à valider
      var new_html      = (action=='edit') ? '<div id="referentiel_edit">' : '<li class="li_'+contexte+'">' ;
      var obj_conteneur = (action=='edit') ? objet.parent().children(conteneur) : false ;
      switch(contexte)
      {
        case 'n1' :  // domaine
          if(action=='edit')
          {
            // on récupère la référence
            var ref = obj_conteneur.children('b:eq(0)').text();
            // on récupère le code léttré
            var code = obj_conteneur.children('b:eq(2)').text();
            // on récupère le nom
            var nom = obj_conteneur.children('b:eq(4)').text();
          }
          else
          {
            var code = ref = nom = '';
          }
          // on complète le formulaire
          new_html += '<i class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pour remplacer les références automatiques." /> Ref.</i> <input id="f_ref" name="f_ref" size="2" maxlength="3" type="text" value="'+escapeQuote(ref)+'" /> (facultatif)<br />';
          new_html += '<i class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Lettre unique pour les références automatiques" />Code</i> <input id="f_code" name="f_code" size="1" maxlength="1" type="text" value="'+code+'" /><br />';
          new_html += '<i class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Nom du domaine" /> Nom</i> <input id="f_nom" name="f_nom" size="'+Math.min(10+nom.length,118)+'" maxlength="128" type="text" value="'+escapeQuote(nom)+'" /><br />';
          var texte = 'ce domaine';
          break;
        case 'n2' :  // thème
          if(action=='edit')
          {
            // on récupère la référence
            var ref = obj_conteneur.children('b:eq(0)').text();
            // on récupère le nom
            var nom = obj_conteneur.children('b:eq(2)').text();
          }
          else
          {
            var ref = nom = '';
          }
          // on complète le formulaire
          new_html += '<i class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pour remplacer les références automatiques." /> Ref.</i> <input id="f_ref" name="f_ref" size="2" maxlength="3" type="text" value="'+escapeQuote(ref)+'" /> (facultatif)<br />';
          new_html += '<i class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Nom du thème" /> Nom</i> <input id="f_nom" name="f_nom" size="'+Math.min(10+nom.length,128)+'" maxlength="128" type="text" value="'+escapeQuote(nom)+'" /><br />';
          var texte = 'ce thème';
          break;
        case 'n3' :  // item
          if(action=='edit')
          {
            // On récupère le coefficient
            var adresse = obj_conteneur.children('img:eq(0)').attr('src');
            var coef    = parseInt( adresse.substr(adresse.length-6,2) , 10 );
            // On récupère l'autorisation de demande
            var adresse = obj_conteneur.children('img:eq(1)').attr('src');
            var cart    = adresse.substr(adresse.length-7,3);
            var check1  = (cart=='oui') ? ' checked' : '' ;
            var check0  = (cart=='non') ? ' checked' : '' ;
            // On récupère le socle 2016
            var s2016_id  = obj_conteneur.children('img:eq(2)').data('id');
            if(!s2016_id)
            {
              var s2016_txt = 'Hors-socle.';
            }
            else
            {
              var s2016_txt = '';
              var tab_id = s2016_id.toString().split(',');
              for(i in tab_id)
              {
                s2016_txt += tab_socle[tab_id[i]]+' | ';
              }
              s2016_txt = s2016_txt.substring(0,s2016_txt.length-3);
            }
            // On récupère le commentaire
            var commentaire = tab_comm[ objet.parent().attr('id').substring(3) ]; // n3_*
            // on récupère la référence
            var ref   = obj_conteneur.children('b:eq(0)').text();
            // on récupère l'abréviation
            var abrev = obj_conteneur.children('b:eq(2)').text();
            // on récupère le nom
            var nom_texte    = obj_conteneur.children('b:eq(4)').text();
            var nom_longueur = Math.min(10+nom_texte.length,128);
          }
          else
          {
            var coef   = 1;
            var check1 = ' checked';
            var check0 = '';
            var s2016_id  = '';
            var s2016_txt = 'Hors-socle.';
            var commentaire = '';
            var ref   = '';
            var abrev = '';
            var nom_texte    = '';
            var nom_longueur = 125;
          }
          var nb_lignes = Math.max( parseInt(commentaire.length/75,10) , 2 );
          // On assemble
          // ‟ et ” remplacés par &#8223; et &#8221; car ne s'affiche pas correctement sur le serveur de Bordeaux ; corresponce trouvée avec http://hapax.qc.ca/conversion.fr.html
          new_html += '<i class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pour remplacer les références automatiques." /> Ref.</i><input id="f_ref" name="f_ref" size="2" maxlength="3" type="text" value="'+escapeQuote(ref)+'" /> (facultatif)<br />';
          new_html += '<i class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Abréviation éclairant sur l\'item pour les tableaux PDF à double entrée." /> Abrev.</i><input id="f_abrev" name="f_abrev" size="12" maxlength="15" type="text" value="'+escapeQuote(abrev)+'" /> (facultatif)<br />';
          new_html += '<i class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Nom de l\'item." /> Nom</i><input id="f_nom" name="f_nom" size="'+nom_longueur+'" maxlength="256" type="text" value="'+escapeQuote(nom_texte)+'" /><br />';
          new_html += '<i class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Commentaire éventuel.<br />Par exemple pour renseigner des &#8223;échelles descriptives&#8221;.<br />Exploité uniquement en infobulle HTML (pas de sortie PDF)." /> Comm.</i><textarea id="f_comm" name="f_comm" rows="'+nb_lignes+'" cols="100">'+escapeQuote(commentaire)+'</textarea><label id="f_comm_reste"></label><br />';
          new_html += '<i class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Appartenance éventuelle au socle commun 2016." /> S.2016</i><input id="f_intitule2016" name="f_intitule2016" size="90" maxlength="90" type="text" value="'+s2016_txt+'" readonly /><input id="f_socle2016" name="f_socle2016" type="hidden" value="'+s2016_id+'" /><q class="choisir_socle" title="Sélectionner un item du socle commun 2016."></q><br />';
          new_html += '<i class="tab"><img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Coefficient (nombre entier entre 0 et 20 ; 1 par défaut)." /> Coef.</i><input id="f_coef" name="f_coef" type="number" min="0" max="20" value="'+coef+'" /><br />';
          new_html += '<i class="tab">Demande</i> <input id="f_cart1" name="f_cart" type="radio" value="1"'+check1+' /><label for="f_cart1"><img src="./_img/etat/cart_oui.png" title="Demande possible." /></label> <input id="f_cart0" name="f_cart" type="radio" value="0"'+check0+' /><label for="f_cart0"><img src="./_img/etat/cart_non.png" title="Demande interdite." /></label><br />';
          var texte = 'cet item';
          break;
        default :
          var texte = '???';
      }
      // Fin du formulaire
      var q_action = (action=='edit') ? 'editer' : 'ajouter' ;
      var title = (action=='edit') ? 'la modification' : 'l\'ajout' ;
      new_html += '<i class="tab"></i><q class="valider" data-action="'+q_action+'" title="Valider '+title+' de '+texte+'."></q><q class="annuler" data-action="'+q_action+'" title="Annuler '+title+' de '+texte+'."></q> <label id="ajax_msg">&nbsp;</label>';
      new_html += (action=='edit') ? '</div>' : '</li>' ;
      // On insère le formulaire dans la page
      if(action=='edit')
      {
        objet.before(new_html).parent().children(conteneur).hide();
      }
      else if(objet.parent().attr('id').substring(0,2)==contexte)
      {
        // A ajouter à la suite d'un autre élément de même contexte
        objet.parent().after(new_html);
      }
      else
      {
        // A ajouter au début d'un contexte supérieur
        objet.next().show().prepend(new_html);
      }
      // focus
      if(contexte=='n1')
      {
        $('#f_code').focus();
      }
      else
      {
        if(contexte=='n3')
        {
          afficher_textarea_reste( $('#f_comm') , nb_caracteres_max );
        }
        $('#f_nom').focus();
      }
    }

    $('#zone_elaboration_referentiel').on
    (
      'click',
      'q[data-action=add]',
      function()
      {
        afficher_form_edition( 'add', $(this) );
      }
    );

    $('#zone_elaboration_referentiel').on
    (
      'click',
      'q[data-action=edit]',
      function()
      {
        afficher_form_edition( 'edit', $(this) );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur l'image pour Supprimer un domaine (avec son contenu), ou un thème (avec son contenu), ou un item
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_elaboration_referentiel').on
    (
      'click',
      'q[data-action=del]',
      function()
      {
        // On récupère le contexte de la demande : n1 ou n2 ou n3
        var contexte = $(this).attr('class').substring(0,2);
        afficher_masquer_images_action('hide');
        // On créé le formulaire à valider
        switch(contexte)
        {
          case 'n1' :  // domaine
            var conteneur = 'span';
            element_nom = $(this).parent().children(conteneur).children('b:eq(4)').text();
            var alerte = 'Tout le contenu de ce domaine ainsi que tous les résultats des items concernés seront perdus !';
            var texte1 = 'ce domaine';
            var texte2 = 'le domaine'+' &laquo;&nbsp;'+matiere_nom+'&nbsp;||&nbsp;'+element_nom+'&nbsp;&raquo;';
            break;
          case 'n2' :  // thème
            var conteneur = 'span';
            element_nom = $(this).parent().children(conteneur).children('b:eq(2)').text();
            var alerte = 'Tout le contenu de ce thème ainsi que les résultats des items concernés seront perdus (et les thèmes suivants seront renumérotés) !';
            var texte1 = 'ce thème';
            var texte2 = 'le thème'+' &laquo;&nbsp;'+matiere_nom+'&nbsp;||&nbsp;'+element_nom+'&nbsp;&raquo;';
            break;
          case 'n3' :  // item
            var conteneur = 'b';
            element_nom = $(this).parent().children(conteneur).children('b:eq(4)').text();
            var alerte = 'Tous les résultats associés seront perdus et les items suivants seront renumérotés !';
            var texte1 = 'cet item';
            var texte2 = 'l\'item sélectionné';
            break;
          default :
            var alerte = '???';
            var texte1 = '???';
            var texte2 = '???';
        }
        memo_text_delete = texte2;
        var new_html = '<div id="form_del" class="danger">'+alerte;  // un div.danger est utilisé au lieu du span.danger car un clic sur un span enroule/déroule le contenu
        new_html += '<q class="valider" data-action="supprimer" title="Valider la suppression de '+texte1+'."></q><q class="annuler" data-action="supprimer" title="Annuler la suppression de '+texte1+'."></q> <label id="ajax_msg">&nbsp;</label>';
        new_html += '</div>';
        // On insère le formulaire dans la page
        $(this).before(new_html).parent().children(conteneur).hide();
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur l'image pour Fusionner deux items
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_elaboration_referentiel').on
    (
      'click',
      'q[data-action=fus]',
      function()
      {
        afficher_masquer_images_action('hide');
        // On ajoute les boutons à cocher
        var id = $(this).parent().attr('id');
        $('#zone_elaboration_referentiel li.li_n3').each( function(){ if($(this).attr('id')!=id){$(this).children('b').after('<q class="n3_fus2" data-action="fus2" title="Valider l\'absorption de l\'item choisi en 1er par celui-ci."></q>');} } );
        var new_img = '<q class="annuler" data-action="fusionner" title="Annuler la fusion de cet item."></q><label id="ajax_msg">&nbsp;</label>';
        // On insère le formulaire dans la page
        $(this).after(new_img);
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur l'image pour Déplacer un domaine (avec son contenu), ou un thème (avec son contenu), ou un item
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_elaboration_referentiel').on
    (
      'click',
      'q[data-action=move]',
      function()
      {
        // On récupère le contexte de la demande : n1 ou n2 ou n3
        var contexte = $(this).attr('class').substring(0,2);
        afficher_masquer_images_action('hide');
        // On ajoute les boutons à cocher
        var id = $(this).parent().attr('id');
        switch(contexte)
        {
          case 'n1' :  // domaine
            $('#zone_elaboration_referentiel li.li_m2').each( function(){ $(this).children('span').after('<q class="n1_move2" data-action="move2" title="Valider le déplacement du domaine au début de ce niveau."></q>'); } );
            $('#zone_elaboration_referentiel li.li_n1').each( function(){ if($(this).attr('id')!=id){$(this).children('span').after('<q class="n1_move2" data-action="move2" title="Valider le déplacement du domaine à la suite de celui-ci."></q>');} } );
            break;
          case 'n2' :  // thème
            $('#zone_elaboration_referentiel li.li_n1').each( function(){ $(this).children('span').after('<q class="n2_move2" data-action="move2" title="Valider le déplacement du thème au début de ce domaine (et renuméroter)."></q>'); } );
            $('#zone_elaboration_referentiel li.li_n2').each( function(){ if($(this).attr('id')!=id){$(this).children('span').after('<q class="n2_move2" data-action="move2" title="Valider le déplacement du thème à la suite de celui-ci."></q>');} } );
            break;
          case 'n3' :  // item
            $('#zone_elaboration_referentiel li.li_n2').each( function(){ $(this).children('span').after('<q class="n3_move2" data-action="move2" title="Valider le déplacement de l\'item au début de ce thème (et renuméroter)."></q>'); } );
            $('#zone_elaboration_referentiel li.li_n3').each( function(){ if($(this).attr('id')!=id){$(this).children('b').after('<q class="n3_move2" data-action="move2" title="Valider le déplacement de l\'item à la suite de celui-ci."></q>');} } );
            break;
        }
        // On créé le formulaire à valider
        switch(contexte)
        {
          case 'n1' :  // domaine
            var texte = 'ce domaine';
            break;
          case 'n2' :  // thème
            var texte = 'ce thème';
            break;
          case 'n3' :  // item
            var texte = 'cet item';
            break;
          default :
            var texte = '???';
        }
        var new_img = '<q class="annuler" data-action="deplacer" title="Annuler le déplacement de '+texte+'."></q><label id="ajax_msg">&nbsp;</label>';
        // On insère le formulaire dans la page
        $(this).after(new_img);
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur l'image pour afficher les items du socle 2016
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_elaboration_referentiel').on
    (
      'click',
      'q.choisir_socle',
      function()
      {
        // récupérer le nom de l'item et le reporter
        var item_nom = escapeHtml( entity_convert( $('#f_nom').val() ) );
        $('#zone_socle2016_composante span.f_nom').html(item_nom);
        // récupérer la relation au socle commun et la cocher
        cocher_socle2016_composantes( $('#f_socle2016').val() );
        // montrer le cadre
        $.fancybox( { 'href':'#zone_socle2016_composante' , onStart:function(){$('#zone_socle2016_composante').css("display","block");} , onClosed:function(){$('#zone_socle2016_composante').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
        objet = 'choisir_socle2016';
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour confirmer les relations au socle 2016 d'un item
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#choisir_socle2016_valider').click
    (
      function()
      {
        // récupérer les relations au socle (id + nom du premier si plusieurs)
        var socle_id = '';
        var socle_nom = '';
        $("#zone_socle2016_composante input[type=checkbox]:checked").each
        (
          function()
          {
            socle_id += $(this).val() + ',';
            socle_nom += tab_socle[$(this).val()] + ' | ';
          }
        );
        if(!socle_id)
        {
          socle_nom = 'Hors-socle.';
        }
        else
        {
          socle_id = socle_id.substring(0,socle_id.length-1);
          socle_nom = socle_nom.substring(0,socle_nom.length-3);
        }
        // L'envoyer dans le formulaire
        $('#f_socle2016').val(socle_id);
        $('#f_intitule2016').val(socle_nom);
        // masquer le cadre
        $.fancybox.close();
        objet = 'editer';
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour Annuler le choix dans le socle 2016
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#choisir_socle2016_annuler').click
    (
      function()
      {
        $.fancybox.close();
        objet = 'editer';
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur l'image pour valider l'ajout d'un domaine, ou d'un thème, ou d'un item
// Clic sur l'image pour valider l'édition d'un domaine, ou d'un thème, ou d'un item
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function traiter_form_edition( action, objet_parent )
    {
      // On récupère le contexte de la demande : n1 ou n2 ou n3
      var contexte = (action=='edit') ? objet_parent.parent().attr('id').substring(0,2) : objet_parent.attr('class').substring(3,5);
      // On récupère le code lettré de l'élément (domaine uniquement)
      if(contexte=='n1')
      {
        var code = $('#f_code').val();
        if(code=='')
        {
          $('#ajax_msg').attr('class','erreur').html("Code lettré manquant !");
          $('#f_code').focus();
          return false;
        }
        if('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'.indexOf(code)==-1)
        {
          $('#ajax_msg').attr('class','erreur').html("Le code doit être une lettre ou un chiffre !");
          $('#f_code').focus();
          return false;
        }
      }
      else
      {
        var code = '';
      }
      // On récupère la référence de l'élément (facultatif)
      var ref = entity_convert($('#f_ref').val());
      // On récupère le nom de l'élément
      var nom = entity_convert($('#f_nom').val());
      if(nom=='')
      {
        $('#ajax_msg').attr('class','erreur').html("Nom manquant !");
        $('#f_nom').focus();
        return false;
      }
      // On récupère l'abréviation, le coefficient, l'autorisation de demande, les liens au socle 2016, le lien de ressources, le commentaire de l'élément (item uniquement)
      if(contexte=='n3')
      {
        var abrev = $('#f_abrev').val();
        var coef  = parseInt( $('#f_coef').val() , 10 );
        var cart  = $("input[name=f_cart]:checked").val();
        var comm  = $('#f_comm').val();
        var socle2016 = $('#f_socle2016').val();
        if( (isNaN(coef)) || (coef<0) || (coef>20) )
        {
          $('#ajax_msg').attr('class','erreur').html("Le coefficient doit être un nombre entier entre 0 et 20 !");
          $('#f_coef').focus();
          return false;
        }
        if(isNaN(cart))  // normalement impossible, sauf si par exemple on triche avec la barre d'outils Web Developer...
        {
          $('#ajax_msg').attr('class','erreur').html("Cocher si l'élève peut ou non demander une évaluation !");
          return false;
        }
      }
      else
      {
        var abrev = '';
        var coef  = 1;
        var cart  = 0;
        var comm  = '';
        var socle2016 = '';
      }
      // Si édition, on récupère l'id de l'élément        concerné (niveau ou domaine ou theme)
      // Si ajout  , on récupère l'id de l'élément parent concerné (niveau ou domaine ou theme)
      if(action=='edit')
      {
        var get_texte = 'element';
        var get_value = objet_parent.parent().attr('id').substring(3);
      }
      else
      {
        var get_texte = 'parent';
        var get_value = objet_parent.parent().parent().attr('id').substring(3);
      }
      // Si ajout,
      // - [1] on calcule le n° d'ordre de l'élément à partir de la recherche du nb d'éléments précédents pour l'élément parent concerné
      // - [2] on récupère la liste des éléments suivants dont il faudra augmenter l'ordre
      if(action=='edit')
      {
        var ordre = 0;
        tab_id = new Array();
      }
      else
      {
        // [1]
        var li = objet_parent;
        var ordre = (contexte=='n3') ? 0 : 1;
        while(li.prev().length)
        {
          li = li.prev();
          ordre++;
        }
        // [2]
        li = objet_parent;
        tab_id = new Array();
        while(li.next().length)
        {
          li = li.next();
          tab_id.push(li.attr('id').substring(3));
        }
      }
      // Envoi des infos en ajax pour le traitement de la demande
      $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action='+action+'&contexte='+contexte+'&matiere='+matiere_id+'&'+get_texte+'='+get_value+'&ordre='+ordre+'&tab_id='+tab_id+'&code='+code+'&coef='+coef+'&cart='+cart+'&socle2016='+socle2016+'&ref='+encodeURIComponent(ref)+'&nom='+encodeURIComponent(nom)+'&abrev='+encodeURIComponent(abrev)+'&matiere_nom='+encodeURIComponent(matiere_nom)+'&comm='+encodeURIComponent(comm),
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==false)
            {
              $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
            }
            else
            {
              var separateur = ' &#9553; '; // Car ' ║ ' ne s'affiche pas correctement sur le serveur de Bordeaux ; corresponce trouvée avec http://hapax.qc.ca/conversion.fr.html
              switch(contexte)
              {
                case 'n1' :  // domaine
                  var conteneur = 'span';
                  var sep_ref = (ref) ? separateur : '' ;
                  var texte = '<b>'+escapeHtml(ref)+'</b>' + '<b>'+sep_ref+'</b>' + '<b>'+code+'</b>' + '<b>'+separateur+'</b>' + '<b>'+escapeHtml(nom)+'</b>';
                  if(action=='add')
                  { 
                    texte = '<span>' + texte + '</span>' + images[contexte.charAt(1)] + '<ul class="ul_n2"></ul>';
                  }
                  break;
                case 'n2' :  // thème
                  var conteneur = 'span';
                  var sep_ref = (ref) ? separateur : '' ;
                  var texte = '<b>'+escapeHtml(ref)+'</b>' + '<b>'+sep_ref+'</b>' + '<b>'+escapeHtml(nom)+'</b>';
                  if(action=='add')
                  {
                    texte = '<span>' + texte + '</span>' + images[contexte.charAt(1)] + '<ul class="ul_n3"></ul>';
                  }
                  break;
                case 'n3' :  // item
                  var conteneur = 'b';
                  coef_image    = (coef<10) ? '0'+coef : coef ;
                  coef_texte    = '<img src="./_img/coef/'+coef_image+'.gif" alt="" title="Coefficient '+coef+'." />';
                  cart_image    = (cart>0) ? 'oui' : 'non' ;
                  cart_title    = (cart>0) ? 'Demande possible.' : 'Demande interdite.' ;
                  cart_texte    = '<img src="./_img/etat/cart_'+cart_image+'.png" title="'+cart_title+'" />';
                  s2016_image   = (socle2016) ? 'oui' : 'non' ;
                  if( !socle2016 || socle2016.indexOf(',')==-1)
                  {
                    s2016_title   = $('#f_intitule2016').val();
                  }
                  else
                  {
                    s2016_title = '';
                    var tab_id = socle2016.toString().split(',');
                    for(i in tab_id)
                    {
                      s2016_title += tab_socle[tab_id[i]]+'<br />';
                    }
                    s2016_title = s2016_title.substring(0,s2016_title.length-6);
                  }
                  s2016_texte   = '<img src="./_img/etat/socle_'+s2016_image+'.png" alt="" title="'+s2016_title+'" data-id="'+socle2016+'" />';
                  lien_image    = ( (action=='edit') && (tab_lien[get_value]) ) ? 'oui' : 'non' ;
                  lien_title    = ( (action=='edit') && (tab_lien[get_value]) ) ? tab_lien[get_value] : 'Absence de ressource.' ;
                  lien_texte    = '<img src="./_img/etat/link_'+lien_image+'.png" alt="" title="'+lien_title+'" />';
                  comm_image    = (comm) ? 'oui' : 'non' ;
                  comm_title    = (comm) ? comm : 'Sans commentaire.' ;
                  comm_texte    = '<img alt="" src="./_img/etat/comm_'+comm_image+'.png" width="16" height="16" title="'+addBR(escapeHtml(comm_title))+'" />';
                  var sep_ref   = (ref) ? separateur : '' ;
                  var sep_abrev = (abrev) ? separateur : '' ;
                  var texte = coef_texte + cart_texte + s2016_texte + lien_texte + comm_texte + '<b>'+escapeHtml(ref)+'</b>' + '<b>'+sep_ref+'</b>' + '<b>'+escapeHtml(abrev)+'</b>' + '<b>'+sep_abrev+'</b>' + '<b>'+escapeHtml(nom)+'</b>';
                  if(action=='add')
                  {
                    texte = '<b>' + texte + '</b>' + images[contexte.charAt(1)];
                    tab_lien[contexte+'_'+responseJSON['value']] = '';
                    tab_comm[responseJSON['value']] = comm;
                  }
                  else
                  {
                    tab_comm[get_value] = comm;
                  }
                  break;
                default :
                  var conteneur = '???';
                  var texte = '???';
              }
              // On met à jour la page
              if(action=='add')
              {
                objet_parent.attr('id',contexte+'_'+responseJSON['value']).html(texte);
              }
              else
              {
                objet_parent.parent().children(conteneur).html(texte).show();
                objet_parent.remove();
              }
              afficher_masquer_images_action('show');
            }
          }
        }
      );
    }

    $('#zone_elaboration_referentiel').on
    (
      'click',
      'q.valider[data-action=ajouter]',
      function()
      {
        traiter_form_edition( 'add', $(this).parent() );
      }
    );

    $('#zone_elaboration_referentiel').on
    (
      'click',
      'q.valider[data-action=editer]',
      function()
      {
        traiter_form_edition( 'edit', $(this).parent() );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur l'image pour confirmer la suppression d'un domaine (avec son contenu), ou d'un thème (avec son contenu), ou d'un item
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_elaboration_referentiel').on
    (
      'click',
      'q.valider[data-action=supprimer]',
      function()
      {
        memo_objet = $(this);
        $.prompt(prompt_etapes);
      }
    );

    var prompt_etapes = {
      etape_2: {
        title   : 'Demande de confirmation (2/3)',
        html    : "Tous les résultats des élèves qui en dépendent seront perdus !<br />Souhaitez-vous vraiment supprimer cet élément de référentiel ?",
        buttons : {
          "Non, c'est une erreur !" : false ,
          "Oui, je confirme !" : true
        },
        submit  : function(event, value, message, formVals) {
          if(value) {
            event.preventDefault();
            $('#referentiel_infos_prompt').html(memo_text_delete);
            $.prompt.goToState('etape_3');
            return false;
          }
          else {
            $('q.annuler').click();
          }
        }
      },
      etape_3: {
        title   : 'Demande de confirmation (3/3)',
        html    : "Attention : dernière demande de confirmation !!!<br />Êtes-vous bien certain de vouloir supprimer "+'<span id="referentiel_infos_prompt"></span>'+" ?<br />Est-ce définitivement votre dernier mot ???",
        buttons : {
          "Oui, j'insiste !" : true ,
          "Non, surtout pas !" : false
        },
        submit  : function(event, value, message, formVals) {
          if(value) {
            envoyer_action_confirmee();
            return true;
          }
          else {
            $('q.annuler').click();
          }
        }
      }
    };

    function envoyer_action_confirmee()
    {
      // On récupère le contexte de la demande : n1 ou n2 ou n3
      contexte = memo_objet.parent().parent().attr('id').substring(0,2);
      // On récupère l'id de l'élément concerné (domaine ou theme ou item)
      element_id = memo_objet.parent().parent().attr('id').substring(3);
      // On récupère la liste des éléments suivants dont il faudra diminuer l'ordre
      li = memo_objet.parent().parent();
      tab_id = new Array();
      while(li.next().length)
      {
        li = li.next();
        tab_id.push(li.attr('id').substring(3));
      }
      // Envoi des infos en ajax pour le traitement de la demande
      $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=del'+'&contexte='+contexte+'&matiere='+matiere_id+'&element='+element_id+'&tab_id='+tab_id+'&matiere_nom='+encodeURIComponent(matiere_nom)+'&nom='+encodeURIComponent(element_nom),
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==true)
            {
              $('#ajax_msg').parent().parent().remove();
              afficher_masquer_images_action('show');
            }
            else
            {
              $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
            }
          }
        }
      );
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur l'image pour confirmer la fusion d'un item avec un second qui l'absorbe
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_elaboration_referentiel').on
    (
      'click',
      'q[data-action=fus2]',
      function()
      {
        //
        // Element de départ
        //
        var obj_li = $('q.annuler[data-action=fusionner]').parent();
        var obj_b  = obj_li.children('b');
        var li_id_depart = obj_li.attr('id');
        var element_id  = li_id_depart.substring(3);
        var element_nom = obj_b.find('b').eq(4).text();
        // On récupère la liste des éléments suivants dont il faudra diminuer l'ordre
        tab_id = new Array();
        while(obj_li.next().length)
        {
          obj_li = obj_li.next();
          tab_id.push(obj_li.attr('id').substring(3));
        }
        //
        // Element d'arrivée
        //
        var obj_li = $(this).parent();
        var obj_b  = obj_li.children('b');
        var li_id_arrivee = obj_li.attr('id');
        var element2_id  = li_id_arrivee.substring(3);
        var element2_nom = obj_b.find('b').eq(4).text();
        // Envoi des infos en ajax pour le traitement de la demande
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=fus'+'&matiere='+matiere_id+'&element='+element_id+'&tab_id='+tab_id+'&element2='+element2_id+'&matiere_nom='+encodeURIComponent(matiere_nom)+'&nom='+encodeURIComponent(element_nom)+'&nom2='+encodeURIComponent(element2_nom),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                var lien = responseJSON['value'];
                var lien_image  = (lien) ? 'oui' : 'non' ;
                var lien_title  = (lien) ? lien : 'Absence de ressource.' ;
                $('#n3_'+element2_id).children('b').find('img').eq(4).attr('src','./_img/etat/link_'+lien_image+'.png').attr('title',lien_title);
                $('#ajax_msg').parent().remove();
                $('q[data-action=fus2]').remove();
                afficher_masquer_images_action('show');
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur l'image pour confirmer le déplacement d'un domaine, ou d'un thème, ou d'un item
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_elaboration_referentiel').on
    (
      'click',
      'q[data-action=move2]',
      function()
      {
        //
        // Element de départ
        //
        li = $('q.annuler[data-action=deplacer]').parent();
        li_id_depart = li.attr('id');
        // On récupère le contexte de la demande : n1 ou n2 ou n3
        // On récupère l'id de l'élément concerné (domaine ou theme ou item)
        contexte = li_id_depart.substring(0,2);
        element_id = li_id_depart.substring(3);
        switch(contexte)
        {
          case 'n1' :  // domaine
            element_nom = li.children('span').children('b:eq(4)').text();
            break;
          case 'n2' :  // thème
            element_nom = li.children('span').children('b:eq(2)').text();
            break;
          case 'n3' :  // item
            element_nom = li.children('b').children('b:eq(4)').text();
            break;
          default :
            element_nom = '';
        }
        // On récupère la liste des éléments suivants dont il faudra diminuer l'ordre
        tab_id = new Array();
        while(li.next().length)
        {
          li = li.next();
          tab_id.push(li.attr('id').substring(3));
        }
        //
        // Element d'arrivée
        //
        li_id_arrivee = $(this).parent().attr('id');
        contexte2 = li_id_arrivee.substring(0,2);
        if(contexte2==contexte)  // Si on demande à l'insérer après un élément de même niveau
        {
          // On récupère l'id de l'élément parent concerné (niveau ou domaine ou theme)
          parent_id = $(this).parent().parent().parent().attr('id').substring(3);
          // On calcule le n° d'ordre de l'élément à partir de la recherche du nb d'éléments précédents pour l'élément parent concerné
          li = $(this).parent();
          ordre = (contexte=='n3') ? 1 : 2;
          while(li.prev().length)
          {
            li = li.prev();
            test_id = li.attr('id').substring(3);
            if(test_id!=element_id)  // sans compter éventuellement celui qui va être déplacé...
            {
              ordre++;
            }
          }
          // On récupère la liste des éléments suivants dont il faudra augmenter l'ordre
          li = $(this).parent();
          tab_id2 = new Array();
          while(li.next().length)
          {
            li = li.next();
            test_id = li.attr('id').substring(3);
            if(test_id!=element_id)  // sans compter éventuellement celui qui va être déplacé...
            {
              tab_id2.push(test_id);
            }
          }
        }
        else  // Si on demande à l'insérer au début d'un élément de niveau supérieur
        {
          // On récupère l'id de l'élément parent concerné (niveau ou domaine ou theme)
          parent_id = $(this).parent().attr('id').substring(3);
          // On calcule le n° d'ordre de l'élément à partir de la recherche du nb d'éléments précédents pour l'élément parent concerné
          ordre = (contexte=='n3') ? 0 : 1;
          // On récupère la liste des éléments suivants dont il faudra augmenter l'ordre
          tab_id2 = new Array();
          $(this).parent().children('ul').children('li').each
          (
            function()
            {
              test_id = $(this).attr('id').substring(3);
              if(test_id!=element_id)  // sans compter éventuellement celui qui va être déplacé...
              {
                tab_id2.push(test_id);
              }
            }
          );
        }
        // Envoi des infos en ajax pour le traitement de la demande
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=move'+'&contexte='+contexte+'&matiere='+matiere_id+'&element='+element_id+'&tab_id='+tab_id+'&parent='+parent_id+'&ordre='+ordre+'&tab_id2='+tab_id2+'&matiere_nom='+encodeURIComponent(matiere_nom)+'&nom='+encodeURIComponent(element_nom),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                if(contexte2==contexte)  // Si on demande à l'insérer après un élément de même niveau
                {
                  $('#'+li_id_arrivee).after( $('#'+li_id_depart) );
                }
                else  // Si on demande à l'insérer au début d'un élément de niveau supérieur
                {
                  $('#'+li_id_arrivee).children('ul').prepend( $('#'+li_id_depart) );
                }
                $('q.annuler[data-action=deplacer]').remove();
                $('#ajax_msg').remove();
                $('q[data-action=move2]').remove();
                afficher_masquer_images_action('show');
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur l'image pour Annuler un ajout
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_elaboration_referentiel').on
    (
      'click',
      'q.annuler[data-action=ajouter]',
      function()
      {
        $(this).parent().remove();
        afficher_masquer_images_action('show');
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur l'image pour Annuler un renommage
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_elaboration_referentiel').on
    (
      'click',
      'q.annuler[data-action=editer]',
      function()
      {
        $(this).parent().parent().children().show();
        $(this).parent().remove();
        afficher_masquer_images_action('show');
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur l'image pour Annuler une suppression
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_elaboration_referentiel').on
    (
      'click',
      'q.annuler[data-action=supprimer]',
      function()
      {
        $(this).parent().parent().children().show();
        $(this).parent().remove();
        afficher_masquer_images_action('show');
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur l'image pour Annuler une fusion
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_elaboration_referentiel').on
    (
      'click',
      'q.annuler[data-action=fusionner]',
      function()
      {
        $(this).remove();
        $('#ajax_msg').remove();
        $('q[data-action=fus2]').remove();
        afficher_masquer_images_action('show');
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur l'image pour Annuler un déplacement
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_elaboration_referentiel').on
    (
      'click',
      'q.annuler[data-action=deplacer]',
      function()
      {
        $(this).remove();
        $('#ajax_msg').remove();
        $('q[data-action=move2]').remove();
        afficher_masquer_images_action('show');
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Intercepter la touche entrée ou escape pour valider ou annuler les modifications
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $(document).on
    (
      'keyup',
      'input',
      function(e)
      {
        if(e.which==13)  // touche entrée
        {
          if(objet=='choisir_socle2016') {$('#choisir_socle2016_valider').click();}
          else {$('#zone_elaboration_referentiel q.valider').click();}
        }
        else if(e.which==27)  // touche escape
        {
          if(objet=='choisir_socle2016') {$('#choisir_socle2016_annuler').click();}
          else {$('#zone_elaboration_referentiel q.annuler').click();}
        }
        return false;
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Gestion des manipulations complémentaires
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function lister_options_select( granulosite , select_id , matiere_id_a_eviter )
    {
      var id_matieres = (matiere_id_a_eviter) ? listing_id_matieres_autorisees.replace(','+matiere_id_a_eviter+',',',') : listing_id_matieres_autorisees ;
      id_matieres = id_matieres.substring(1,id_matieres.length-1);
      $('#ajax_msg_groupe').attr('class','loader').html("En cours&hellip;");
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=lister_options'+'&granulosite='+granulosite+'&id_matieres='+id_matieres,
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg_groupe').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==true)
            {
              $('#ajax_msg_groupe').removeAttr('class').html('');
              $('#'+select_id).html(responseJSON['value']).show(0);
            }
            else
            {
              $('#ajax_msg_groupe').attr('class','alerte').html(responseJSON['value']);
            }
          }
        }
      );
    }

    var modifier_action_groupe = function()
    {
      var action_groupe = $('#select_action_groupe_choix option:selected').val();
      $('#bouton_valider_groupe').prop('disabled',true);
      $('#groupe_modifier_avertissement , #select_action_groupe_modifier_objet , #select_action_groupe_modifier_id , #select_action_groupe_modifier_coef , #select_action_groupe_modifier_cart , #select_action_groupe_modifier_socle_mode, #select_action_groupe_modifier_socle_val , #select_action_groupe_deplacer_id_initial , #select_action_deplacer_explication , #select_action_groupe_deplacer_id_final').css("display","none"); // hide(0) ne donne rien si appelé par initialiser_action_groupe()...
      if(!action_groupe)
      {
        $('#ajax_msg_groupe').removeAttr('class').html('');
      }
      else if( (action_groupe=='modifier_coefficient') || (action_groupe=='modifier_panier') || (action_groupe=='modifier_socle2016') )
      {
        $('#select_action_groupe_modifier_objet option:first').prop('selected',true);
        $('#select_action_groupe_modifier_objet').show(0);
        $('#ajax_msg_groupe').removeAttr('class').html('');
      }
      else if( (action_groupe=='deplacer_domaine') || (action_groupe=='deplacer_theme') )
      {
        $('#groupe_modifier_avertissement').show(0);
        $('#select_action_groupe_deplacer_id_initial').html('<option value="">&nbsp;</option>');
        lister_options_select( action_groupe.substring(9) , 'select_action_groupe_deplacer_id_initial' , 0 );
      }
    };

    $("#select_action_groupe_choix").change( modifier_action_groupe );

    function initialiser_action_groupe()
    {
      $('#select_action_groupe_choix option:first').prop('selected',true);
      modifier_action_groupe();
    }

    $("#select_action_groupe_modifier_objet").change
    (
      function()
      {
        var modifier_objet = $('#select_action_groupe_modifier_objet option:selected').val();
        $('#bouton_valider_groupe').prop('disabled',true);
        $('#select_action_groupe_modifier_id , #select_action_groupe_modifier_coef , #select_action_groupe_modifier_cart , #select_action_groupe_modifier_socle_mode, #select_action_groupe_modifier_socle_val').hide(0);
        if(!modifier_objet)
        {
          $('#ajax_msg_groupe').removeAttr('class').html('');
        }
        else
        {
          $('#select_action_groupe_modifier_id').html('<option value="">&nbsp;</option>');
          lister_options_select( modifier_objet , 'select_action_groupe_modifier_id' , 0 );
        }
      }
    );

    $("#select_action_groupe_modifier_id").change
    (
      function()
      {
        var action_groupe = $('#select_action_groupe_choix option:selected').val();
        var modifier_id = $('#select_action_groupe_modifier_id option:selected').val();
        $('#bouton_valider_groupe').prop('disabled',true);
        if(!modifier_id)
        {
          $('#select_action_groupe_modifier_coef , #select_action_groupe_modifier_cart , #select_action_groupe_modifier_socle_mode, #select_action_groupe_modifier_socle_val').hide(0);
          $('#ajax_msg_groupe').removeAttr('class').html('');
        }
        else
        {
          if(action_groupe=='modifier_coefficient')
          {
            $('#select_action_groupe_modifier_cart , #select_action_groupe_modifier_socle_mode, #select_action_groupe_modifier_socle_val').hide(0);
            $('#select_action_groupe_modifier_coef option:first').prop('selected',true);
            $('#select_action_groupe_modifier_coef').show(0);
          }
          else if(action_groupe=='modifier_panier')
          {
            $('#select_action_groupe_modifier_coef , #select_action_groupe_modifier_socle_mode, #select_action_groupe_modifier_socle_val').hide(0);
            $('#select_action_groupe_modifier_cart option:first').prop('selected',true);
            $('#select_action_groupe_modifier_cart').show(0);
          }
          else if(action_groupe=='modifier_socle2016')
          {
            $('#select_action_groupe_modifier_coef , #select_action_groupe_modifier_cart, #select_action_groupe_modifier_socle_val').hide(0);
            $('#select_action_groupe_modifier_socle_mode option:first').prop('selected',true);
            $('#select_action_groupe_modifier_socle_mode').show(0);
          }
        }
      }
    );

    $("#select_action_groupe_deplacer_id_initial").change
    (
      function()
      {
        var action_groupe = $('#select_action_groupe_choix option:selected').val();
        var deplacer_id_initial = $('#select_action_groupe_deplacer_id_initial option:selected').val();
        $('#bouton_valider_groupe').prop('disabled',true);
        if(!deplacer_id_initial)
        {
          $('#select_action_deplacer_explication , #select_action_groupe_deplacer_id_final').hide(0);
          $('#ajax_msg_groupe').removeAttr('class').html('');
        }
        else
        {
          var tab_ids = deplacer_id_initial.split('_');
          var matiere_id_a_eviter = tab_ids[0];
          var option_a_desactiver = (action_groupe=='deplacer_domaine') ? 'deplacer_theme' : 'deplacer_domaine' ;
          var option_a_activer    = (action_groupe=='deplacer_theme')   ? 'deplacer_theme' : 'deplacer_domaine' ;
          var granulosite         = (action_groupe=='deplacer_domaine') ? 'referentiel'    : 'domaine' ;
          $('#select_action_deplacer_explication option[value='+option_a_desactiver+']').prop('disabled',true);
          $('#select_action_deplacer_explication option[value='+option_a_activer+']').prop('disabled',false).prop('selected',true);
          $('#select_action_deplacer_explication').show(0);
          $('#select_action_groupe_deplacer_id_final').html('<option value="">&nbsp;</option>');
          lister_options_select( granulosite , 'select_action_groupe_deplacer_id_final' , matiere_id_a_eviter );
        }
      }
    );

    $("#select_action_groupe_modifier_socle_mode").change
    (
      function()
      {
        if(!$('#select_action_groupe_modifier_socle_mode option:selected').val())
        {
          $('#select_action_groupe_modifier_socle_val').hide(0);
        }
        else
        {
          $('#select_action_groupe_modifier_socle_val option:first').prop('selected',true);
          $('#select_action_groupe_modifier_socle_val').show(0);
        }
      }
    );

    $("#select_action_groupe_modifier_coef").change
    (
      function()
      {
        var modifier_coef = $('#select_action_groupe_modifier_coef option:selected').val();
        var etat_desactive = (modifier_coef==='') ? true : false ;
        $('#bouton_valider_groupe').prop('disabled',etat_desactive);
      }
    );

    $("#select_action_groupe_modifier_cart").change
    (
      function()
      {
        var modifier_cart = $('#select_action_groupe_modifier_cart option:selected').val();
        var etat_desactive = (modifier_cart==='') ? true : false ;
        $('#bouton_valider_groupe').prop('disabled',etat_desactive);
      }
    );

    $("#select_action_groupe_modifier_socle_val").change
    (
      function()
      {
        var modifier_socle = $('#select_action_groupe_modifier_socle_val option:selected').val();
        var etat_desactive = (modifier_socle==='') ? true : false ;
        $('#bouton_valider_groupe').prop('disabled',etat_desactive);
      }
    );

    $("#select_action_groupe_deplacer_id_final").change
    (
      function()
      {
        var deplacer_id_final = $('#select_action_groupe_deplacer_id_final option:selected').val();
        var etat_desactive = (deplacer_id_final) ? false : true ;
        $('#bouton_valider_groupe').prop('disabled',etat_desactive);
      }
    );

    $("#bouton_valider_groupe").click
    (
      function()
      {
        var groupe_nom_initial = $('#select_action_groupe_deplacer_id_initial option:selected').text();
        var groupe_nom_final   = $('#select_action_groupe_deplacer_id_final option:selected').text();
        $('#ajax_msg_groupe').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=action_complementaire'+'&'+$('#zone_choix_referentiel').serialize()+'&groupe_nom_initial='+encodeURIComponent(groupe_nom_initial)+'&groupe_nom_final='+encodeURIComponent(groupe_nom_final),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg_groupe').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg_groupe').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                $('#ajax_msg_groupe').attr('class','valide').html(responseJSON['value']);
                var action_groupe = $('#select_action_groupe_choix option:selected').val();
                if( (action_groupe=='deplacer_domaine') || (action_groupe=='deplacer_theme') )
                {
                  // maj 1er select éléments de référentiels
                  lister_options_select( action_groupe.substring(9) , 'select_action_groupe_deplacer_id_initial' , 0 );
                  // maj 2e select éléments de référentiels
                  var deplacer_id_initial = $('#select_action_groupe_deplacer_id_initial option:selected').val();
                  var tab_ids = deplacer_id_initial.split('_');
                  var matiere_id_a_eviter = tab_ids[0];
                  var granulosite         = (action_groupe=='deplacer_domaine') ? 'referentiel'    : 'domaine' ;
                  lister_options_select( granulosite , 'select_action_groupe_deplacer_id_final' , matiere_id_a_eviter );
                }
              }
            }
          }
        );
      }
    );

  }
);
