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

// jQuery !
$(document).ready
(
  function()
  {

    // Fonction pour vérifier le format hexadécimal
    function verif_hexa_format(value)
    {
      return (/^\#[0-9a-f]{3,6}$/i.test(value)) && (value.length!=5) && (value.length!=6) ;
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Initialisation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var mode_note_code = false;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Gestion des déplacements de blocs
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#sortable_h_note , #sortable_h_acquis').sortable( { cursor:'ew-resize' , items:'li:not(.colorpicker)' } );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur un checkbox pour activer / désactiver un code couleur ou un état d'acquisition
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('input[type=checkbox]').change
    (
      function()
      {
        if($(this).is(':checked'))
        {
          $(this).parent().parent().next().show(0);
        }
        else
        {
          $(this).parent().parent().next().hide(0);
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur un crayon pour modifier un symbole coloré
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_notes').on
    (
      'click',
      'q.modifier',
      function()
      {
        mode_note_code = $(this).prev('input').attr('id').substring(11); // note_image_
        $.fancybox( { 'href':'#form_symbole' , onStart:function(){$('#form_symbole').css("display","block");} , onClosed:function(){$('#form_symbole').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur un lien pour choisir d'un symbole coloré
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('.note_liste').on
    (
      'click',
      'a',
      function()
      {
        var new_note_code = $(this).attr('id').substring(2); // s_ | p_
        var new_note_src  = $(this).children('img').attr('src');
        var input_obj = $('#note_image_'+mode_note_code);
        input_obj.val(new_note_code);
        input_obj.prev('img').attr('src',new_note_src);
        $('#ajax_msg_notes').attr('class','alerte').html("Pensez à valider vos modifications !");
        $.fancybox.close();
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le lien pour annuler le choix d'un symbole coloré
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_annuler_note').click
    (
      function()
      {
        $.fancybox.close();
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Activation du colorpicker pour les 3 champs input.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var f = $.farbtastic('#colorpicker');
    $('input.stretch').focus
    (
      function()
      {
        $('#colorpicker').show();
        f.linkTo(this);
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Reporter dans un input colorpicker une valeur préféfinie lors du clic sur un bouton (couleur de fond).
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('button.colorer').click
    (
      function()
      {
        $( '#acquis_'+$(this).attr('name') ).val( $(this).val() ).focus();
        $('#ajax_msg_acquis').attr('class','alerte').html("Pensez à valider vos modifications !");
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement du premier formulaire (codes de notation)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_valider_notes').click
    (
      function()
      {
        // Vérifications
        var note_id = 0;
        var nb_actifs = 0;
        var nb_verifs = 0;
        var val_min = -1 ;
        var nb_sup_100 = 0 ;
        var tab_actif   = new Array();
        var tab_ordre   = new Array();
        var tab_image   = new Array();
        var tab_sigle   = new Array();
        var tab_legende = new Array();
        var tab_clavier = new Array();
        $('#sortable_h_note').children('li').each
        (
          function()
          {
            note_id = $(this).attr('id').substring(1);
            tab_ordre.push(note_id);
            if($('#note_actif_'+note_id).is(':checked'))
            {
              nb_actifs = tab_actif.push(note_id);
              // Valeur
              var saisie = $('#note_valeur_'+note_id).val();
              var valeur = parseInt(saisie,10);
              if( isNaN(saisie) || ( parseFloat(saisie) != valeur ) )
              {
                $('#ajax_msg_notes').attr('class','erreur').html("Valeur #"+note_id+" : nombre entier requis.").show();
                $('#note_valeur_'+note_id).focus();
                return false;
              }
              if( valeur < 0 )
              {
                $('#ajax_msg_notes').attr('class','erreur').html("Valeur #"+note_id+" : nombre positif requis.").show();
                $('#note_valeur_'+note_id).focus();
                return false;
              }
              if( valeur <= val_min )
              {
                $('#ajax_msg_notes').attr('class','erreur').html("Valeur #"+note_id+" : nombres croissants requis.").show();
                $('#note_valeur_'+note_id).focus();
                return false;
              }
              if( valeur > 200 )
              {
                $('#ajax_msg_notes').attr('class','erreur').html("Valeur #"+note_id+" : 200 maximum pour le meilleur code.").show();
                $('#note_valeur_'+note_id).focus();
                return false;
              }
              if( valeur > 100 )
              {
                nb_sup_100++;
                if( nb_sup_100 >= 2 )
                {
                  $('#ajax_msg_notes').attr('class','erreur').html("Valeur #"+note_id+" : une seule prut dépasser 100.").show();
                  $('#note_valeur_'+note_id).focus();
                  return false;
                }
              }
              val_min = valeur;
              // Image
              var image = $('#note_image_'+note_id).val();
              if( (image=='X') || !image )
              {
                $('#ajax_msg_notes').attr('class','erreur').html("Symbole #"+note_id+" : choix manquant.").show();
                $('#note_image_'+note_id).focus();
                return false;
              }
              var image_upper = image.toUpperCase();
              if( typeof(tab_image[image_upper]) !== 'undefined' )
              {
                $('#ajax_msg_notes').attr('class','erreur').html("Symbole #"+note_id+" : identique au symbole #"+tab_image[image_upper]+".").show();
                $('#note_image_'+note_id).focus();
                return false;
              }
              tab_image[image_upper] = note_id;
              // Sigle
              var sigle = $('#note_sigle_'+note_id).val();
              if( !sigle.trim() )
              {
                $('#ajax_msg_notes').attr('class','erreur').html("Sigle #"+note_id+" : saisie manquante.").show();
                $('#note_sigle_'+note_id).focus();
                return false;
              }
              if( sigle.length>3 )
              {
                $('#ajax_msg_notes').attr('class','erreur').html("Sigle #"+note_id+" : 3 caractères maximum.").show();
                $('#note_sigle_'+note_id).focus();
                return false;
              }
              var sigle_upper = sigle.toUpperCase();
              if( typeof(tab_sigle[sigle_upper]) !== 'undefined' )
              {
                $('#ajax_msg_notes').attr('class','erreur').html("Sigle #"+note_id+" : identique au sigle #"+tab_sigle[sigle_upper]+".").show();
                $('#note_sigle_'+note_id).focus();
                return false;
              }
              tab_sigle[sigle_upper] = note_id;
              // Légende
              var legende = $('#note_legende_'+note_id).val();
              if( !legende.trim() )
              {
                $('#ajax_msg_notes').attr('class','erreur').html("Légende #"+note_id+" : saisie manquante.").show();
                $('#note_legende_'+note_id).focus();
                return false;
              }
              if( legende.length>40 )
              {
                $('#ajax_msg_notes').attr('class','erreur').html("Légende #"+note_id+" : 40 caractères maximum.").show();
                $('#note_legende_'+note_id).focus();
                return false;
              }
              var legende_upper = legende.toUpperCase();
              if( typeof(tab_legende[legende_upper]) !== 'undefined' )
              {
                $('#ajax_msg_notes').attr('class','erreur').html("Légende #"+note_id+" : identique à la légende #"+tab_legende[legende_upper]+".").show();
                $('#note_legende_'+note_id).focus();
                return false;
              }
              tab_legende[legende_upper] = note_id;
              // Touche
              var saisie = $('#note_clavier_'+note_id).val();
              var clavier = parseInt(saisie,10);
              if( isNaN(saisie) || ( parseFloat(saisie) != clavier ) )
              {
                $('#ajax_msg_notes').attr('class','erreur').html("Touche #"+note_id+" : nombre entier requis.").show();
                $('#note_clavier_'+note_id).focus();
                return false;
              }
              if( ( clavier < 0 ) || ( clavier > 10 ) )
              {
                $('#ajax_msg_notes').attr('class','erreur').html("Touche #"+note_id+" : nombre entre 1 et 9 requis.").show();
                $('#note_clavier_'+note_id).focus();
                return false;
              }
              if( typeof(tab_clavier[clavier]) !== 'undefined' )
              {
                $('#ajax_msg_notes').attr('class','erreur').html("Touche #"+note_id+" : identique à la touche #"+tab_clavier[clavier]+".").show();
                $('#note_clavier_'+note_id).focus();
                return false;
              }
              tab_clavier[clavier] = note_id;
              nb_verifs++;
            }
          }
        );
        // Vérification qu'il n'y a pas eu d'erreur trouvée
        if( nb_verifs < nb_actifs )
        {
          return false;
        }
        // Vérification au moins 2 codes activés
        if( nb_actifs < 2 )
        {
          $('#ajax_msg_notes').attr('class','erreur').html("Il faut au moins 2 codes actifs.").show();
          return false;
        }
        // GO !
        $('#notes_ordre').val(tab_ordre.join());
        $('#notes_actif').val(tab_actif.join());
        $("#bouton_valider_notes").prop('disabled',true);
        $('#ajax_msg_notes').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=save_notes'+'&'+$('#form_notes').serialize(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $("#bouton_valider_notes").prop('disabled',false);
              $('#ajax_msg_notes').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $("#bouton_valider_notes").prop('disabled',false);
              if(responseJSON['statut']==true)
              {
                $('#ajax_msg_notes').attr('class','valide').html("Choix mémorisés !");
              }
              else
              {
                $('#ajax_msg_notes').attr('class','alerte').html(responseJSON['value']);
              }
              return false;
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement du second formulaire (états d'acquisitions)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_valider_acquis').click
    (
      function()
      {
        // Vérifications
        var acquis_id = 0;
        var nb_actifs = 0;
        var nb_verifs = 0;
        var seuil_min = -1 ;
        var etat_min = -1 ;
        var tab_actif   = new Array();
        var tab_ordre   = new Array();
        var tab_color   = new Array();
        var tab_sigle   = new Array();
        var tab_legende = new Array();
        $('#sortable_h_acquis').children('li').each
        (
          function()
          {
            if($(this).hasClass('colorpicker'))
            {
              return false;
            }
            acquis_id = $(this).attr('id').substring(1);
            tab_ordre.push(acquis_id);
            if($('#acquis_actif_'+acquis_id).is(':checked'))
            {
              nb_actifs = tab_actif.push(acquis_id);
              // Seuil minimum
              var saisie = $('#acquis_seuil_'+acquis_id+'_min').val();
              var valeur = parseInt(saisie,10);
              if( isNaN(saisie) || ( parseFloat(saisie) != valeur ) )
              {
                $('#ajax_msg_acquis').attr('class','erreur').html("Seuil min #"+acquis_id+" : nombre entier requis.").show();
                $('#acquis_seuil_'+acquis_id+'_min').focus();
                return false;
              }
              if( ( seuil_min==-1 ) && ( valeur != 0 ) )
              {
                $('#ajax_msg_acquis').attr('class','erreur').html("Seuil min #"+acquis_id+" : valeur 0 requise pour le premier seuil.").show();
                $('#acquis_seuil_'+acquis_id+'_min').focus();
                return false;
              }
              if( valeur <= seuil_min )
              {
                $('#ajax_msg_acquis').attr('class','erreur').html("Seuil min #"+acquis_id+" : valeurs croissantes requises.").show();
                $('#acquis_seuil_'+acquis_id+'_min').focus();
                return false;
              }
              if( valeur != seuil_min+1 )
              {
                $('#ajax_msg_acquis').attr('class','erreur').html("Seuil min #"+acquis_id+" : intervalles consécutifs requis.").show();
                $('#acquis_seuil_'+acquis_id+'_min').focus();
                return false;
              }
              if( valeur > 100 )
              {
                $('#ajax_msg_acquis').attr('class','erreur').html("Seuil min #"+acquis_id+" : valeur inférieure à 100 requise.").show();
                $('#acquis_seuil_'+acquis_id+'_min').focus();
                return false;
              }
              seuil_min = valeur;
              // Seuil maximum
              var saisie = $('#acquis_seuil_'+acquis_id+'_max').val();
              var valeur = parseInt(saisie,10);
              if( isNaN(saisie) || ( parseFloat(saisie) != valeur ) )
              {
                $('#ajax_msg_acquis').attr('class','erreur').html("Seuil max #"+acquis_id+" : nombre entier requis.").show();
                $('#acquis_seuil_'+acquis_id+'_max').focus();
                return false;
              }
              if( valeur <= seuil_min )
              {
                $('#ajax_msg_acquis').attr('class','erreur').html("Seuil max #"+acquis_id+" : valeurs croissantes requises.").show();
                $('#acquis_seuil_'+acquis_id+'_max').focus();
                return false;
              }
              if( valeur > 100 )
              {
                $('#ajax_msg_acquis').attr('class','erreur').html("Seuil max #"+acquis_id+" : valeur inférieure à 100 requise.").show();
                $('#acquis_seuil_'+acquis_id+'_max').focus();
                return false;
              }
              seuil_min = valeur;
              // Couleur
              var color = $('#acquis_color_'+acquis_id).val();
              if( !color )
              {
                $('#ajax_msg_acquis').attr('class','erreur').html("Couleur #"+acquis_id+" : choix manquant.").show();
                $('#acquis_color_'+acquis_id).focus();
                return false;
              }
              if( !verif_hexa_format(color) )
              {
                $('#ajax_msg_acquis').attr('class','erreur').html("Couleur #"+acquis_id+" : valeur invalide.").show();
                $('#acquis_color_'+acquis_id).focus();
                return false;
              }
              if( typeof(tab_color[color]) !== 'undefined' )
              {
                $('#ajax_msg_acquis').attr('class','erreur').html("Couleur #"+acquis_id+" : identique à l'acquisition #"+tab_color[color]+".").show();
                $('#acquis_color_'+acquis_id).focus();
                return false;
              }
              tab_color[color] = acquis_id;
              // Sigle
              var sigle = $('#acquis_sigle_'+acquis_id).val();
              if( !sigle.trim() )
              {
                $('#ajax_msg_acquis').attr('class','erreur').html("Sigle #"+acquis_id+" : saisie manquante.").show();
                $('#acquis_sigle_'+acquis_id).focus();
                return false;
              }
              if( sigle.length>3 )
              {
                $('#ajax_msg_acquis').attr('class','erreur').html("Sigle #"+acquis_id+" : 3 caractères maximum.").show();
                $('#acquis_sigle_'+acquis_id).focus();
                return false;
              }
              var sigle_upper = sigle.toUpperCase();
              if( typeof(tab_sigle[sigle_upper]) !== 'undefined' )
              {
                $('#ajax_msg_acquis').attr('class','erreur').html("Sigle #"+acquis_id+" : identique au sigle #"+tab_sigle[sigle_upper]+".").show();
                $('#acquis_sigle_'+acquis_id).focus();
                return false;
              }
              tab_sigle[sigle_upper] = acquis_id;
              // Légende
              var legende = $('#acquis_legende_'+acquis_id).val();
              if( !legende.trim() )
              {
                $('#ajax_msg_acquis').attr('class','erreur').html("Légende #"+acquis_id+" : saisie manquante.").show();
                $('#acquis_legende_'+acquis_id).focus();
                return false;
              }
              if( legende.length>40 )
              {
                $('#ajax_msg_acquis').attr('class','erreur').html("Légende #"+acquis_id+" : 40 caractères maximum.").show();
                $('#acquis_legende_'+acquis_id).focus();
                return false;
              }
              var legende_upper = legende.toUpperCase();
              if( typeof(tab_legende[legende_upper]) !== 'undefined' )
              {
                $('#ajax_msg_acquis').attr('class','erreur').html("Légende #"+acquis_id+" : identique à la légende #"+tab_legende[legende_upper]+".").show();
                $('#acquis_legende_'+acquis_id).focus();
                return false;
              }
              tab_legende[legende_upper] = acquis_id;
              // État
              var saisie = $('#acquis_valeur_'+acquis_id).val();
              var valeur = parseInt(saisie,10);
              if( isNaN(saisie) || ( parseFloat(saisie) != valeur ) )
              {
                $('#ajax_msg_acquis').attr('class','erreur').html("État #"+acquis_id+" : nombre entier requis.").show();
                $('#acquis_valeur_'+acquis_id).focus();
                return false;
              }
              if( ( valeur < 0 ) || ( valeur > 100 ) )
              {
                $('#ajax_msg_acquis').attr('class','erreur').html("État #"+acquis_id+" : nombre entre 0 et 100 requis.").show();
                $('#acquis_valeur_'+acquis_id).focus();
                return false;
              }
              /*
              if( ( etat_min==-1 ) && ( valeur != 0 ) )
              {
                $('#ajax_msg_acquis').attr('class','erreur').html("État #"+acquis_id+" : valeur 0 requise pour le premier état.").show();
                $('#acquis_valeur_'+acquis_id).focus();
                return false;
              }
              */
              if( valeur < etat_min )
              {
                $('#ajax_msg_acquis').attr('class','erreur').html("État #"+acquis_id+" : valeurs croissantes requises.").show();
                $('#acquis_valeur_'+acquis_id).focus();
                return false;
              }
              etat_min = valeur;
              nb_verifs++;
            }
          }
        );
        // Vérification qu'il n'y a pas eu d'erreur trouvée
        if( nb_verifs < nb_actifs )
        {
          return false;
        }
        if( seuil_min != 100 )
        {
          var last_acquis_id_actif = tab_actif.pop();
          $('#ajax_msg_acquis').attr('class','erreur').html("Seuil max #"+last_acquis_id_actif+" : valeur 100 requise pour le dernier seuil.").show();
          $('#acquis_seuil_'+last_acquis_id_actif+'_max').focus();
          return false;
        }
        if( etat_min != 100 )
        {
          var last_acquis_id_actif = tab_actif.pop();
          $('#ajax_msg_acquis').attr('class','erreur').html("État #"+last_acquis_id_actif+" : valeur 100 requise pour le dernier état.").show();
          $('#acquis_valeur_'+last_acquis_id_actif).focus();
          return false;
        }
        // Vérification au moins 2 états activés
        if( nb_actifs < 2 )
        {
          $('#ajax_msg_acquis').attr('class','erreur').html("Il faut au moins 2 états actifs.").show();
          return false;
        }
        // GO !
        $('#acquis_ordre').val(tab_ordre.join());
        $('#acquis_actif').val(tab_actif.join());
        $("#bouton_valider_acquis").prop('disabled',true);
        $('#ajax_msg_acquis').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=save_acquis'+'&'+$('#form_acquis').serialize(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $("#bouton_valider_acquis").prop('disabled',false);
              $('#ajax_msg_acquis').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $("#bouton_valider_acquis").prop('disabled',false);
              if(responseJSON['statut']==true)
              {
                $('#ajax_msg_acquis').attr('class','valide').html("Choix mémorisés !");
                $('#colorpicker').hide();
                // Actualisation boutons avec valeur enregistrée établissement
                for( num=1 ; num<7 ; num++ )
                {
                   var color = $('#acquis_color_'+num).val();
                   $('#report_color_'+num).val(color);
                }
              }
              else
              {
                $('#ajax_msg_acquis').attr('class','alerte').html(responseJSON['value']);
              }
              return false;
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du formulaire #form_symbole
    // Upload d'un fichier (avec jquery.form.js)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire_symbole = $('#form_symbole');

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions_symbole =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : 'json',
      clearForm : false,
      resetForm : false,
      target : "#ajax_msg_symbole",
      error : retour_form_erreur_symbole,
      success : retour_form_valide_symbole
    };

    // Vérifications précédant l'envoi du formulaire, déclenchées au choix d'un fichier
    $('#f_symbole').change
    (
      function()
      {
        var file = this.files[0];
        if( typeof(file) == 'undefined' )
        {
          $('#ajax_msg_symbole').removeAttr('class').html('');
          return false;
        }
        else
        {
          var fichier_nom = file.name;
          var fichier_ext = fichier_nom.split('.').pop().toLowerCase();
          if( '.bmp.gif.jpg.jpeg.png.'.indexOf('.'+fichier_ext+'.') == -1 )
          {
            $('#ajax_msg_symbole').attr('class','erreur').html('Le fichier "'+fichier_nom+'" n\'a pas une extension autorisée (bmp gif jpg jpeg png).');
            return false;
          }
          else
          {
            $("#bouton_choisir_symbole").prop('disabled',true);
            $('#ajax_msg_symbole').attr('class','loader').html("En cours&hellip;");
            formulaire_symbole.submit();
          }
        }
      }
    );

    // Envoi du formulaire (avec jquery.form.js)
    formulaire_symbole.submit
    (
      function()
      {
        $(this).ajaxSubmit(ajaxOptions_symbole);
        return false;
      }
    );

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur_symbole(jqXHR, textStatus, errorThrown)
    {
      $('#f_symbole').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
      $("#bouton_choisir_symbole").prop('disabled',false);
      $('#ajax_msg_symbole').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide_symbole(responseJSON)
    {
      $('#f_symbole').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
      $("#bouton_choisir_symbole").prop('disabled',false);
      if(responseJSON['statut']==false)
      {
        $('#ajax_msg_symbole').attr('class','alerte').html(responseJSON['value']);
      }
      else
      {
        initialiser_compteur();
        $('#ajax_msg_symbole').attr('class','valide').html('Image ajoutée');
        $('#notes_perso').append(responseJSON['value']);
      }
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Appel en ajax pour supprimer une image
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#notes_perso').on
    (
      'click',
      'q.supprimer',
      function()
      {
        var image_id = $(this).prev('a').attr('id').substr(9); // p_upload_
        $('#ajax_upload').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=delete_symbole'+'&f_image_id='+image_id,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_upload').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              if(responseJSON['statut']==false)
              {
                $('#ajax_upload').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                $('#ajax_upload').removeAttr('class').html('');
                $('#p_upload_'+image_id).parent('span').remove();
                // Actualisation si c'était un symbole choisi
                for( num=1 ; num<7 ; num++ )
                {
                  var input_obj = $('#note_image_'+num);
                  if( input_obj.val()=='upload_'+image_id )
                  {
                    input_obj.val('X');
                    input_obj.prev('img').attr('src','./_img/note/choix/h/X.gif');
                  }
                }
              }
            }
          }
        );
      }
    );

  }
);
