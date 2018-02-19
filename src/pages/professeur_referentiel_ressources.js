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

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Permettre l'utilisation de caractères spéciaux
// ////////////////////////////////////////////////////////////////////////////////////////////////////

var tab_entite_nom = new Array('&sup2;','&sup3;','&times;','&divide;','&minus;','&pi;','&rarr;','&radic;','&infin;','&asymp;','&ne;','&le;','&ge;');
var tab_entite_val = new Array('²'     ,'³'     ,'×'      ,'÷'       ,'–'      ,'π'   ,'→'     ,'√'      ,'∞'      ,'≈'      ,'≠'   ,'≤'   ,'≥'   );
var imax = tab_entite_nom.length;
function entity_convert(string)
{
  for(i=0;i<imax;i++)
  {
    var reg = new RegExp(tab_entite_nom[i],"g");
    string = string.replace(reg,tab_entite_val[i]);
  }
  return string;
}

// jQuery !
$(document).ready
(
  function()
  {

    // initialisation (variables globales)
    var item_id     = 0;
    var item_nom    = '';
    var upload_lien = '';
    var matiere_id  = 0;
    var matiere_ref = '';
    var bouton_interface_travail = etablissement_identifie ? '<q class="ress_page_elaborer" title="Créer / Modifier une page de ressources pour travailler (partagées sur le serveur communautaire)."></q>' : '<q class="partager_non" title="Pour pouvoir créer une page de ressources sur le serveur communautaire, un administrateur doit préalablement identifier l\'établissement dans la base Sésamath."></q>' ;
    var tab_lien = new Array();
    var images = new Array();
    images[1]  = '';
    images[1] += '<q class="modifier" title="Modifier ce sous-titre"></q>';
    images[1] += '<q class="dupliquer" title="Dupliquer ce sous-titre"></q>';
    images[1] += '<q class="supprimer" title="Supprimer ce sous-titre"></q>';
    images[2]  = '';
    images[2] += '<q class="modifier" title="Modifier ce lien"></q>';
    images[2] += '<q class="dupliquer" title="Dupliquer ce lien"></q>';
    images[2] += '<q class="supprimer" title="Supprimer ce lien"></q>';
    images[3]  = '';
    images[3] += '<q class="ajouter" title="Ajouter ce lien"></q>';
    images[4]  = '';
    images[4] += '<q class="valider" title="Sélectionner cette ressource"></q>';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Charger le form zone_elaboration_referentiel en ajax
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_choix_referentiel q.modifier').click
    (
      function()
      {
        var id      = $(this).parent().attr('id');
        tab_id      = id.split('_');
        matiere_id  = tab_id[1];
        niveau_id   = tab_id[2];
        matiere_ref = $(this).parent().parent().attr('class').substring(3);
        afficher_masquer_images_action('hide');
        new_label = '<label for="'+id+'" class="loader">En cours&hellip;</label>';
        $(this).after(new_label);
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=Voir_referentiel'+'&matiere_id='+matiere_id+'&niveau_id='+niveau_id,
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
                $('#zone_elaboration_referentiel').html('<p><span class="tab"></span>Tout déployer / contracter :<q class="deployer_m1"></q><q class="deployer_m2"></q><q class="deployer_n1"></q><q class="deployer_n2"></q><q class="deployer_n3"></q><br /><span class="tab"></span><button id="fermer_zone_elaboration_referentiel" type="button" class="retourner">Retour à la liste des referentiels</button></p>'+responseJSON['value']);
                // Récupérer le contenu des title des ressources avant que le tooltip ne les enlève
                // Ajouter les icônes pour modifier les items
                $('#zone_elaboration_referentiel li.li_n3').each
                (
                  function()
                  {
                    id2 = $(this).attr('id').substring(3);
                    titre = $(this).children('img').attr('title');
                    tab_lien[id2] = (titre=='Absence de ressource.') ? '' : titre ;
                    $(this).append('<br /><input name="f_lien" size="100" maxlength="256" type="text" value="'+tab_lien[id2]+'" /><q class="voir" title="Tester ce lien."></q>'+bouton_interface_travail+'<q class="valider" title="Valider la modification de ce lien."></q><label>&nbsp;</label>');
                  }
                );
              }
              $('label[for='+id+']').remove();
              afficher_masquer_images_action('show');
            }
          }
        );
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
// Clic sur l'image pour voir la page correspondant au lien
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_elaboration_referentiel').on
    (
      'click',
      'q.voir',
      function()
      {
        item_id   = $(this).parent().attr('id').substring(3);
        item_lien = $('#n3_'+item_id).children('input').val();
        if(!item_lien)
        {
          $('#n3_'+item_id).children('label').attr('class','erreur').html("Adresse absente !");
          return false;
        }
        if(!testURL(item_lien))
        {
          $('#n3_'+item_id).children('label').attr('class','erreur').html("Adresse incorrecte !");
          return false;
        }
        window.open(item_lien);
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Réagir au changement dans un formulaire de lien associé à un item
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_elaboration_referentiel').on
    (
      'change',
      'input',
      function()
      {
        $(this).parent().children('label').attr('class','alerte').html("Penser à valider !");
        return false;
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur l'image pour valider la modification du lien associé à un item
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_elaboration_referentiel').on
    (
      'click',
      'q.valider',
      function()
      {
        item_id   = $(this).parent().attr('id').substring(3);
        item_lien = $('#n3_'+item_id).children('input').val();
        if(item_lien && !testURL(item_lien))
        {
          $('#n3_'+item_id).children('label').attr('class','erreur').html("Adresse incorrecte !");
          return false;
        }
        // Envoi des infos en ajax pour le traitement de la demande
        $('#n3_'+item_id).children('label').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=Enregistrer_lien'+'&item_id='+item_id+'&item_lien='+encodeURIComponent(item_lien),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#n3_'+item_id).children('label').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==false)
              {
                $('#n3_'+item_id).children('label').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                lien_image  = (item_lien=='') ? 'non' : 'oui' ;
                lien_title  = (item_lien=='') ? 'Absence de ressource.' : escapeHtml(item_lien) ;
                retour_msg  = (item_lien=='') ? 'Lien retiré.' : 'Lien enregistré.' ;
                $('#n3_'+item_id).children('img').attr('src','./_img/etat/link_'+lien_image+'.png').attr('title',lien_title);
                tab_lien[item_id] = (item_lien=='') ? '' : lien_title ;
                $('#n3_'+item_id).children('label').attr('class','valide').html(retour_msg);
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur l'image afin d'élaborer ou d'éditer sur le serveur communautaire une page de liens pour travailler
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_elaboration_referentiel').on
    (
      'click',
      'q.ress_page_elaborer',
      function()
      {
        item_id   = $(this).parent().attr('id').substring(3);
        item_lien = $('#n3_'+item_id).children('input').val();
        item_nom  = $(this).parent().html(); // text ne convient pas car on récupère le contenu du label avec...
        pos_debut = item_nom.indexOf('-')+2;
        pos_fin   = item_nom.indexOf('<br');
        item_nom  = item_nom.substring(pos_debut,pos_fin);
        // reporter le nom de l'item
        $('#zone_ressources span.f_nom').html(item_nom);
        // appel ajax
        $.fancybox( '<label class="loader">'+'En cours&hellip;'+'</label>' , {'centerOnScroll':true} );
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=Charger_ressources'+'&item_id='+item_id+'&item_lien='+encodeURIComponent(item_lien),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $.fancybox( '<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+'</label>' , {'centerOnScroll':true} );
              return false;
            },
            success : function(responseJSON)
            {
              if(responseJSON['statut']==false)
              {
                $.fancybox( '<label class="alerte">'+responseJSON['value']+'</label>' , {'centerOnScroll':true} );
                return false;
              }
              else
              {
                initialiser_compteur();
                // mode page_create | page_update
                var mode = (responseJSON['value'].substring(0,14)=='<li class="i">') ? 'page_create' : 'page_update' ;
                $('#page_mode').val(mode);
                // ajouter les boutons
                var reg = new RegExp('</span>',"g"); // Si on ne prend pas une expression régulière alors replace() ne remplace que la 1e occurence
                responseJSON['value'] = responseJSON['value'].replace(reg,'</span>'+images[1]);
                var reg = new RegExp('</a>',"g"); // Si on ne prend pas une expression régulière alors replace() ne remplace que la 1e occurence
                responseJSON['value'] = responseJSON['value'].replace(reg,'</a>'+images[2]);
                // montrer le cadre
                $('#sortable_v').html(responseJSON['value']);
                $('#zone_resultat_recherche_liens').html('');
                $('#zone_ressources q').show();
                $('#ajax_ressources_msg').removeAttr('class').html("");
                $.fancybox( { 'href':'#zone_ressources' , onStart:function(){$('#zone_ressources').css("display","block");} , onClosed:function(){$('#zone_ressources').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
                $('#sortable_v').sortable( { cursor:'ns-resize' } );
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour Annuler la page de liens pour travailler
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#choisir_ressources_annuler').click
    (
      function()
      {
        $('label[for=paragraphe_nom]').removeAttr('class').html('');
        $('label[for=lien_url]').removeAttr('class').html('');
        $('label[for=lien_nom]').removeAttr('class').html('');
        $.fancybox.close();
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour supprimer un élément d'une page de liens pour travailler
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#sortable_v').on
    (
      'click',
      'q.supprimer',
      function()
      {
        var nb_li = $(this).parent().parent().children().length;
        $(this).parent().remove();
        if(nb_li==1)
        {
          $('#sortable_v').append('<li class="i">Encore aucun élément actuellement ! Utilisez les outils ci-dessous pour en ajouter&hellip;</li>');
        }
        initialiser_compteur();
        return false;
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur un bouton pour modifier un élément d'une page de liens pour travailler
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#sortable_v').on
    (
      'click',
      'q.modifier',
      function()
      {
        var element = $(this).prev();
        // soit c'est un sous-titre de paragraphe
        if(element.is('span'))
        {
          var paragraphe_nom = element.html();
          $(this).parent().html('<label class="tab">Sous-titre :</label><input name="paragraphe_nom" value="'+paragraphe_nom+'" size="75" maxlength="256" /><input name="paragraphe_nom_old" value="'+paragraphe_nom+'" type="hidden" /><q class="valider" title="Valider les modifications"></q><q class="annuler" title="Annuler les modifications"></q><label></label>');
        }
        // soit c'est un lien
        else if(element.is('a'))
        {
          var lien_url = element.attr('href');
          var lien_nom = element.html();
          $(this).parent().html('<label class="tab">Adresse :</label><input name="lien_url" value="'+lien_url+'" size="75" maxlength="256" /><input name="lien_url_old" value="'+lien_url+'" type="hidden" /><br /><label class="tab">Intitulé :</label><input name="lien_nom" value="'+lien_nom+'" size="75" maxlength="256" /><input name="lien_nom_old" value="'+lien_nom+'" type="hidden" /><q class="valider" title="Valider les modifications"></q><q class="annuler" title="Annuler les modifications"></q><label></label>');
        }
        return false;
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur un bouton pour dupliquer un élément d'une page de liens pour travailler
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#sortable_v').on
    (
      'click',
      'q.dupliquer',
      function()
      {
        var element = $(this).prev().prev();
        // soit c'est un sous-titre de paragraphe
        if(element.is('span'))
        {
          var paragraphe_nom = element.html();
          $('#paragraphe_nom').val(paragraphe_nom).focus();
        }
        // soit c'est un lien
        else if(element.is('a'))
        {
          var lien_url = element.attr('href');
          var lien_nom = element.html();
          $('#lien_url').val(lien_url);
          $('#lien_nom').val(lien_nom).focus();
        }
        return false;
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur un bouton pour annuler la modification d'un élément d'une page de liens pour travailler
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#sortable_v').on
    (
      'click',
      'q.annuler',
      function()
      {
        var nb_input = $(this).parent().children('input').length;
        // soit c'est un sous-titre de paragraphe
        if(nb_input==2)
        {
          var paragraphe_nom = escapeHtml( $(this).parent().children('input[name=paragraphe_nom_old]').val() );
          $(this).parent().html('<span class="b">'+paragraphe_nom+'</span>'+images[1]);
        }
        // soit c'est un lien
        else if(nb_input==4)
        {
          var lien_url = escapeHtml( $(this).parent().children('input[name=lien_url_old]').val() );
          var lien_nom = escapeHtml( $(this).parent().children('input[name=lien_nom_old]').val() );
          $(this).parent().html('<a href="'+lien_url+'" title="'+lien_url+'" target="_blank" rel="noopener noreferrer">'+lien_nom+'</a>'+images[2]);
        }
        return false;
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur un bouton pour valider la modification d'un élément d'une page de liens pour travailler
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#sortable_v').on
    (
      'click',
      'q.valider',
      function()
      {
        var nb_input = $(this).parent().children('input').length;
        // soit c'est un sous-titre de paragraphe
        if(nb_input==2)
        {
          var paragraphe_nom = escapeHtml( entity_convert( $(this).parent().children('input[name=paragraphe_nom]').val() ) );
          if(paragraphe_nom == '')
          {
            $(this).next().next('label').addClass('erreur').html("Nom manquant !");
            $(this).parent().children('input[name=paragraphe_nom]').focus();
            return false;
          }
          else
          {
            $(this).parent().html('<span class="b">'+paragraphe_nom+'</span>'+images[1]+'</q>');
          }
        }
        // soit c'est un lien
        else if(nb_input==4)
        {
          var lien_url = escapeHtml( entity_convert( $(this).parent().children('input[name=lien_url]').val() ) );
          var lien_nom = escapeHtml( entity_convert( $(this).parent().children('input[name=lien_nom]').val() ) );
          if(lien_url == '')
          {
            $(this).next().next('label').addClass('erreur').html("Adresse manquante !");
            $(this).parent().children('input[name=lien_url]').focus();
            return false;
          }
          else if(!testURL(lien_url))
          {
            $(this).next().next('label').addClass('erreur').html("Adresse incorrecte !");
            $(this).parent().children('input[name=lien_url]').focus();
            return false;
          }
          $(this).next().next('label').removeAttr('class').html("");
          if(lien_nom == '')
          {
            $(this).next().next('label').addClass('erreur').html("Nom manquant !");
            $(this).parent().children('input[name=lien_nom]').focus();
            return false;
          }
          else
          {
            $(this).parent().html('<a href="'+lien_url+'" title="'+lien_url+'" target="_blank" rel="noopener noreferrer">'+lien_nom+'</a>'+images[2]+'</q>');
          }
        }
        initialiser_compteur();
        return false;
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour ajouter un sous-titre de paragraphe dans une page de liens pour travailler
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#paragraphe_ajouter').click
    (
      function()
      {
        var paragraphe_nom = escapeHtml( entity_convert( $('#paragraphe_nom').val() ) );
        if(paragraphe_nom == '')
        {
          $('label[for=paragraphe_nom]').addClass('erreur').html("Nom manquant !");
          $('#paragraphe_nom').focus();
          return false;
        }
        else
        {
          initialiser_compteur();
          $('label[for=paragraphe_nom]').removeAttr('class').html('');
          $('#sortable_v').append('<li><span class="b">'+paragraphe_nom+'</span>'+images[1]+'</li>');
          $('#sortable_v li.i').remove();
          $('#paragraphe_nom').val('');
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour ajouter une ressource dans une page de liens pour travailler
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#lien_ajouter').click
    (
      function()
      {
        // vérif lien_url
        var lien_url = escapeHtml( $('#lien_url').val() );
        if(lien_url == '')
        {
          $('label[for=lien_url]').addClass('erreur').html("Adresse manquante !");
          $('#lien_url').focus();
          return false;
        }
        else if(!testURL(lien_url))
        {
          $('label[for=lien_url]').addClass('erreur').html("Adresse incorrecte !");
          $('#lien_url').focus();
          return false;
        }
        $('label[for=lien_url]').removeAttr('class').html("");
        // vérif lien_nom
        var lien_nom = escapeHtml( entity_convert( $('#lien_nom').val() ) );
        if(lien_nom == '')
        {
          $('label[for=lien_nom]').addClass('erreur').html("Nom manquant !");
          $('#lien_nom').focus();
          return false;
        }
        $('label[for=lien_nom]').removeAttr('class').html('');
        // ok
        initialiser_compteur();
        $('#sortable_v').append('<li><a href="'+lien_url+'" title="'+lien_url+'" target="_blank" rel="noopener noreferrer">'+lien_nom+'</a>'+images[2]+'</li>');
        $('#sortable_v li.i').remove();
        $('#lien_url').val('');
        $('#lien_nom').val('');
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour valider et enregistrer le contenu d'une page de liens pour travailler
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#choisir_ressources_valider').click
    (
      function()
      {
        if($('#sortable_v li.i').length)
        {
          $('#ajax_ressources_msg').attr('class','erreur').html("La liste de ressources est vide !");
          return false;
        }
        // Récupérer les éléments
        var tab_ressources = new Array();
        var modif_en_cours = false;
        var nb_ressources = 0;
        $('#sortable_v li').each
        (
          function()
          {
            // soit c'est un sous-titre de paragraphe
            if($(this).children('span').length)
            {
              var paragraphe_nom = $(this).children('span').html();
              tab_ressources.push(paragraphe_nom);
            }
            // soit c'est un lien
            else if($(this).children('a').length)
            {
              var lien_url = $(this).children('a').attr('href');
              var lien_nom = $(this).children('a').html();
              tab_ressources.push(lien_nom+']¤['+lien_url);
              nb_ressources++;
            }
            // soit une modification d'un élément est en cours
            else
            {
              modif_en_cours = true;
              return false;
            }
          }
        );
        if(modif_en_cours)
        {
          $('#ajax_ressources_msg').attr('class','erreur').html("Valider ou annuler d'abord toute modification en cours !");
          return false;
        }
        if(!nb_ressources)
        {
          $('#ajax_ressources_msg').attr('class','erreur').html("Aucun lien trouvé vers une ressource !");
          return false;
        }
        // appel ajax
        $('#ajax_ressources_msg').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=Enregistrer_ressources'+'&item_id='+item_id+'&item_nom='+encodeURIComponent(item_nom)+'&page_mode='+$('#page_mode').val()+'&ressources='+encodeURIComponent(tab_ressources.join('}¤{')),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_ressources_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              if(responseJSON['statut']==false)
              {
                $('#ajax_ressources_msg').attr('class','alerte').html(responseJSON['value']);
                return false;
              }
              else
              {
                $('label[for=paragraphe_nom]').removeAttr('class').html('');
                $('label[for=lien_url]').removeAttr('class').html('');
                $('label[for=lien_nom]').removeAttr('class').html('');
                $('#ajax_ressources_msg').removeAttr('class').html("");
                $.fancybox.close();
                initialiser_compteur();
                $('#n3_'+item_id).children('input').val(responseJSON['value']);
                $('#n3_'+item_id).children('q.valider').click();
              }
            }
          }
        );
        return false;
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour rechercher des liens existants à partir de mots clefs
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#liens_rechercher').click
    (
      function()
      {
        var findme = $('#chaine_recherche').val();
        if(findme=='')
        {
          $('#zone_resultat_recherche_liens').html('<label class="erreur">Saisir des mots clefs !</label>');
          $('#chaine_recherche').focus();
          return false;
        }
        // appel ajax
        $('#zone_resultat_recherche_liens').html('<label class="loader">En cours&hellip;</label>');
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=Rechercher_liens_ressources'+'&item_id='+item_id+'&findme='+encodeURIComponent(findme),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#zone_resultat_recherche_liens').html('<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+'</label>');
              return false;
            },
            success : function(responseJSON)
            {
              if(responseJSON['statut']==false)
              {
                $('#zone_resultat_recherche_liens').html('<label class="alerte">'+responseJSON['value']+'</label>');
                return false;
              }
              else
              {
                var reg = new RegExp('</a>',"g"); // Si on ne prend pas une expression régulière alors replace() ne remplace que la 1e occurence
                responseJSON['value'] = responseJSON['value'].replace(reg,'</a>'+images[3]);
                $('#zone_resultat_recherche_liens').html('<ul>'+responseJSON['value']+'</ul>');
                initialiser_compteur();
              }
            }
          }
        );
        return false;
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour ajouter un lien trouvé suite à une recherche
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_resultat_recherche_liens').on
    (
      'click',
      'q.ajouter',
      function()
      {
        var lien_url = $(this).prev().attr('href');
        var lien_nom = $(this).prev().html();
        $(this).parent().remove();
        initialiser_compteur();
        $('#sortable_v').append('<li><a href="'+lien_url+'" title="'+lien_url+'" target="_blank" rel="noopener noreferrer">'+lien_nom+'</a>'+images[2]+'</li>');
        $('#sortable_v li.i').remove();
      }
    );


    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du formulaire #zone_ressources_upload
    // Upload d'un fichier (avec jquery.form.js)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Indéfini si pas de droit d'accès à cette fonctionnalité.
    if( $('#zone_ressources_upload').length )
    {

      // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
      // À définir avant la déclaration de ajaxOptions_import sinon Firefox plante mystétieusement... juste parce que cette partie est dans une boucle if{} !
      function retour_form_erreur_ressource(jqXHR, textStatus, errorThrown)
      {
        $('#f_ressource').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
        $('#zone_ressources_upload button').prop('disabled',false);
        $('#ajax_msg_ressource').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
      }

      // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
      // À définir avant la déclaration de ajaxOptions_import sinon Firefox plante mystétieusement... juste parce que cette partie est dans une boucle if{} !
      function retour_form_valide_ressource(responseJSON)
      {
        $('#f_ressource').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
        $('#zone_ressources_upload button').prop('disabled',false);
        if(responseJSON['statut']==false)
        {
          $('#ajax_msg_ressource').attr('class','alerte').html(responseJSON['value']);
        }
        else
        {
          initialiser_compteur();
          var upload_lien = responseJSON['value'];
          var extension   = upload_lien.split('.').pop().toLowerCase();
          $('#ajax_ressources_upload').removeAttr('class').html('');
          $('#afficher_zone_ressources_form').click();
          $('label[for=lien_url]').attr('class','valide').html("Upload réussi !");
          $('label[for=lien_nom]').attr('class','alerte').html("Validez l'ajout&hellip;");
          $('#lien_url').val(upload_lien);
          $('#lien_nom').focus();
          if ( '.doc.docx.odg.odp.ods.odt.ppt.pptx.rtf.sxc.sxd.sxi.sxw.xls.xlsx.'.indexOf('.'+extension+'.') !== -1 )
          {
            $.prompt(
              "Votre fichier a bien été enregistré comme ressource.<br />Néanmoins, pour être consulté, il nécessite un ordinateur équipé d'une suite bureautique adaptée.<br />Pour une meilleure accessibilité, il serait préférable de le convertir au format PDF.",
              {
                title  : 'Information'
              }
            );
          }
        }
      }

      // Le formulaire qui va être analysé et traité en AJAX
      var formulaire_ressource = $('#zone_ressources_upload');

      // Options d'envoi du formulaire (avec jquery.form.js)
      var ajaxOptions_ressource =
      {
        url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
        type : 'POST',
        dataType : 'json',
        clearForm : false,
        resetForm : false,
        target : "#ajax_msg_ressource",
        error : retour_form_erreur_ressource,
        success : retour_form_valide_ressource
      };

      // Vérifications précédant l'envoi du formulaire, déclenchées au choix d'un fichier
      $('#f_ressource').change
      (
        function()
        {
          var file = this.files[0];
          if( typeof(file) == 'undefined' )
          {
            $('#ajax_msg_ressource').removeAttr('class').html('');
            return false;
          }
          else
          {
            var fichier_nom = file.name;
            var fichier_ext = fichier_nom.split('.').pop().toLowerCase();
            if( '.bat.com.exe.php.zip.'.indexOf('.'+fichier_ext+'.') !== -1 )
            {
              $('#ajax_msg_ressource').attr('class','erreur').html('Extension non autorisée.');
              return false;
            }
            else
            {
              $('#f_ressource_matiere').val(matiere_ref);
              $('#zone_ressources_upload button').prop('disabled',true);
              $('#ajax_msg_ressource').attr('class','loader').html("En cours&hellip;");
              formulaire_ressource.submit();
            }
          }
        }
      );

      // Envoi du formulaire (avec jquery.form.js)
      formulaire_ressource.submit
      (
        function()
        {
          $(this).ajaxSubmit(ajaxOptions_ressource);
          return false;
        }
      );

    }

    $('#acceptation_conditions').click
    (
      function()
      {
        if($(this).is(':checked'))
        {
          $('#bouton_choisir_ressource').prop('disabled',false);
        }
        else
        {
          $('#bouton_choisir_ressource').prop('disabled',true);
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour rechercher des ressources existantes uploadées par l'établissement
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#ressources_rechercher').click
    (
      function()
      {
        // appel ajax
        $('#zone_resultat_recherche_ressources').html('<label class="loader">En cours&hellip;</label>');
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=Rechercher_documents',
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#zone_resultat_recherche_ressources').html('<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+'</label>');
              return false;
            },
            success : function(responseJSON)
            {
              if(responseJSON['statut']==false)
              {
                $('#zone_resultat_recherche_ressources').html('<label class="alerte">'+responseJSON['value']+'</label>');
                return false;
              }
              else
              {
                var reg = new RegExp('</a>',"g"); // Si on ne prend pas une expression régulière alors replace() ne remplace que la 1e occurence
                responseJSON['value'] = responseJSON['value'].replace(reg,'</a>'+images[4]);
                $('#zone_resultat_recherche_ressources').html('<ul>'+responseJSON['value']+'</ul>');
                initialiser_compteur();
              }
            }
          }
        );
        return false;
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour ajouter une ressource trouvée suite à une recherche
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_resultat_recherche_ressources').on
    (
      'click',
      'q.valider',
      function()
      {
        var lien_url = $(this).prev().attr('href');
        $('#ajax_ressources_upload').removeAttr('class').html('');
        $('#afficher_zone_ressources_form').click();
        $('label[for=lien_url]').removeAttr('class').html("");
        $('label[for=lien_nom]').attr('class','alerte').html("Validez l'ajout&hellip;");
        $('#lien_url').val(lien_url);
        $('#lien_nom').focus();
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Passer de zone_ressources_form à zone_ressources_upload et vice-versa ; report d'un lien vers une ressource.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#afficher_zone_ressources_upload').click
    (
      function()
      {
        $('#ajax_ressources_upload').removeAttr('class').html('');
        $('#zone_ressources_form').hide();
        $('#zone_ressources_upload').show();
      }
    );

    $('#afficher_zone_ressources_form').click
    (
      function()
      {
        $('#zone_ressources_upload').hide();
        $('#zone_ressources_form').show();
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
          $(this).nextAll('q.valider , q.ajouter').click();
        }
        else if(e.which==27)  // touche escape
        {
          $(this).nextAll('q.annuler').click();
        }
        return false;
      }
    );

  }
);
